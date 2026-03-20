<?php

declare (strict_types=1);
/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Symfony\Webpack_Encore_Bundle\Asset;

use Psr\Cache\Cache_Item_Pool_Interface;
use Symfony\Component\Http_Client\Http_Client;
use Symfony\Contracts\Http_Client\Http_Client_Interface;
use Symfony\Webpack_Encore_Bundle\Exception\Entrypoint_Not_Found_Exception;
/**
 * Returns the CSS or JavaScript files needed for a Webpack entry.
 *
 * This reads a JSON file with the format of Webpack Encore's entrypoints.json file.
 *
 * @final
 */
class Entrypoint_Lookup implements Entrypoint_Lookup_Interface, Integrity_Data_Provider_Interface
{
    private $entries_data;
    private array $returned_files = [];
    public function __construct(private readonly string $entrypoint_json_path, private readonly ?Cache_Item_Pool_Interface $cache = null, private readonly ?string $cache_key = null, private readonly bool $strict_mode = true, private readonly ?Http_Client_Interface $http_client = null)
    {
    }
    public function get_java_script_files(string $entry_name): array
    {
        return $this->get_entry_files($entry_name, 'js');
    }
    public function get_css_files(string $entry_name): array
    {
        return $this->get_entry_files($entry_name, 'css');
    }
    public function get_integrity_data(): array
    {
        $entries_data = $this->get_entries_data();
        if (!\array_key_exists('integrity', $entries_data)) {
            return [];
        }
        return $entries_data['integrity'];
    }
    /**
     * Resets the state of this service.
     */
    public function reset(): void
    {
        $this->returned_files = [];
    }
    private function get_entry_files(string $entry_name, string $key): array
    {
        $this->validate_entry_name($entry_name);
        $entries_data = $this->get_entries_data();
        $entry_data = $entries_data['entrypoints'][$entry_name] ?? [];
        if (!isset($entry_data[$key])) {
            // If we don't find the file type then just send back nothing.
            return [];
        }
        // make sure to not return the same file multiple times
        $entry_files = $entry_data[$key];
        $new_files = array_values(array_diff($entry_files, $this->returned_files));
        $this->returned_files = array_merge($this->returned_files, $new_files);
        return $new_files;
    }
    private function validate_entry_name(string $entry_name): void
    {
        $entries_data = $this->get_entries_data();
        if (!isset($entries_data['entrypoints'][$entry_name]) && $this->strict_mode) {
            $without_extension = substr($entry_name, 0, strrpos($entry_name, '.'));
            if (isset($entries_data['entrypoints'][$without_extension])) {
                throw new Entrypoint_Not_Found_Exception(\sprintf('Could not find the entry "%s". Try "%s" instead (without the extension).', $entry_name, $without_extension));
            }
            throw new Entrypoint_Not_Found_Exception(\sprintf('Could not find the entry "%s" in "%s". Found: %s.', $entry_name, $this->entrypoint_json_path, implode(', ', array_keys($entries_data['entrypoints']))));
        }
    }
    private function get_entries_data(): array
    {
        if (null !== $this->entries_data) {
            return $this->entries_data;
        }
        if ($this->cache) {
            $cached = $this->cache->get_item($this->cache_key);
            if ($cached->is_hit()) {
                return $this->entries_data = $cached->get();
            }
        }
        if (str_starts_with($this->entrypoint_json_path, 'http')) {
            if (null === $this->http_client && !class_exists(Http_Client::class)) {
                throw new \LogicException(\sprintf('You cannot fetch the entrypoints file from URL "%s" as the HttpClient component is not installed. Try running "composer require symfony/http-client".', $this->entrypoint_json_path));
            }
            $http_client = $this->http_client ?? Http_Client::create();
            $response = $http_client->request('GET', $this->entrypoint_json_path);
            if (200 !== $response->get_status_code()) {
                if (!$this->strict_mode) {
                    return [];
                }
                throw new \InvalidArgumentException(\sprintf('Could not find the entrypoints file from URL "%s": the HTTP request failed with status code %d.', $this->entrypoint_json_path, $response->get_status_code()));
            }
            $this->entries_data = $response->to_array();
        } elseif (!file_exists($this->entrypoint_json_path)) {
            if (!$this->strict_mode) {
                return [];
            }
            throw new \InvalidArgumentException(\sprintf('Could not find the entrypoints file from Webpack: the file "%s" does not exist.', $this->entrypoint_json_path));
        } else {
            $this->entries_data = json_decode(file_get_contents($this->entrypoint_json_path), true);
        }
        if (null === $this->entries_data) {
            throw new \InvalidArgumentException(\sprintf('There was a problem JSON decoding the "%s" file', $this->entrypoint_json_path));
        }
        if (!isset($this->entries_data['entrypoints'])) {
            throw new \InvalidArgumentException(\sprintf('Could not find an "entrypoints" key in the "%s" file', $this->entrypoint_json_path));
        }
        if (isset($cached)) {
            $this->cache->save($cached->set($this->entries_data));
        }
        return $this->entries_data;
    }
    public function entry_exists(string $entry_name): bool
    {
        $entries_data = $this->get_entries_data();
        return isset($entries_data['entrypoints'][$entry_name]);
    }
}