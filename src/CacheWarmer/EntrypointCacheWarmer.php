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
namespace Symfony\Webpack_Encore_Bundle\Cache_Warmer;

use Symfony\Bundle\Framework_Bundle\Cache_Warmer\Abstract_Php_File_Cache_Warmer;
use Symfony\Component\Cache\Adapter\Array_Adapter;
use Symfony\Contracts\Http_Client\Http_Client_Interface;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup;
use Symfony\Webpack_Encore_Bundle\Exception\Entrypoint_Not_Found_Exception;
class Entrypoint_Cache_Warmer extends Abstract_Php_File_Cache_Warmer
{
    public function __construct(private readonly array $cache_keys, private readonly ?Http_Client_Interface $http_client, string $php_array_file)
    {
        parent::__construct($php_array_file);
    }
    protected function do_warm_up(string $cache_dir, Array_Adapter $array_adapter, ?string $build_dir = null): bool
    {
        foreach ($this->cache_keys as $cache_key => $path) {
            // If the file does not exist then just skip past this entry point.
            if (!str_starts_with((string) $path, 'http') && !file_exists($path)) {
                continue;
            }
            $entry_point_lookup = new Entrypoint_Lookup($path, $array_adapter, $cache_key, httpClient: $this->http_client);
            try {
                $entry_point_lookup->get_java_script_files('dummy');
            } catch (Entrypoint_Not_Found_Exception) {
                // ignore exception
            }
        }
        return true;
    }
}