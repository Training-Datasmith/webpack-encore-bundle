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

use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Event_Dispatcher\Event_Dispatcher_Interface;
use Symfony\Contracts\Service\Reset_Interface;
use Symfony\Webpack_Encore_Bundle\Event\Render_Asset_Tag_Event;
/**
 * @final
 */
class Tag_Renderer implements Reset_Interface
{
    private $packages;
    // TODO WebpackEncoreBundle 3.0: remove this property
    private array $rendered_files = [];
    // TODO WebpackEncoreBundle 3.0: rename this property to $renderedFiles
    private array $rendered_files_with_attributes = [];
    public function __construct(private readonly Entrypoint_Lookup_Collection_Interface $entrypoint_lookup_collection, Packages $packages, private readonly array $default_attributes = [], private readonly array $default_script_attributes = [], private readonly array $default_link_attributes = [], private readonly ?Event_Dispatcher_Interface $event_dispatcher = null)
    {
        $this->packages = $packages;
        $this->reset();
    }
    public function render_webpack_script_tags(string $entry_name, ?string $package_name = null, ?string $entrypoint_name = null, array $extra_attributes = [], bool $include_attributes = false): string
    {
        $entrypoint_name = $entrypoint_name ?: '_default';
        $script_tags = [];
        $entry_point_lookup = $this->get_entrypoint_lookup($entrypoint_name);
        $integrity_hashes = $entry_point_lookup instanceof Integrity_Data_Provider_Interface ? $entry_point_lookup->get_integrity_data() : [];
        foreach ($entry_point_lookup->get_java_script_files($entry_name) as $filename) {
            $attributes = [];
            $attributes['src'] = $this->get_asset_path($filename, $package_name);
            $attributes = array_merge($attributes, $this->default_attributes, $this->default_script_attributes, $extra_attributes);
            if (isset($integrity_hashes[$filename])) {
                $attributes['integrity'] = $integrity_hashes[$filename];
            }
            $event = new Render_Asset_Tag_Event(Render_Asset_Tag_Event::TYPE_SCRIPT, $attributes['src'], $attributes);
            if (null !== $this->event_dispatcher) {
                $event = $this->event_dispatcher->dispatch($event);
            }
            $attributes = $event->get_attributes();
            $script_tags[] = \sprintf('<script %s></script>', $this->convert_array_to_attributes($attributes));
            $this->rendered_files['scripts'][] = $attributes['src'];
            $this->rendered_files_with_attributes['scripts'][] = $attributes;
        }
        return implode('', $script_tags);
    }
    public function render_webpack_link_tags(string $entry_name, ?string $package_name = null, ?string $entrypoint_name = null, array $extra_attributes = []): string
    {
        $entrypoint_name = $entrypoint_name ?: '_default';
        $script_tags = [];
        $entry_point_lookup = $this->get_entrypoint_lookup($entrypoint_name);
        $integrity_hashes = $entry_point_lookup instanceof Integrity_Data_Provider_Interface ? $entry_point_lookup->get_integrity_data() : [];
        foreach ($entry_point_lookup->get_css_files($entry_name) as $filename) {
            $attributes = [];
            $attributes['rel'] = 'stylesheet';
            $attributes['href'] = $this->get_asset_path($filename, $package_name);
            $attributes = array_merge($attributes, $this->default_attributes, $this->default_link_attributes, $extra_attributes);
            if (isset($integrity_hashes[$filename])) {
                $attributes['integrity'] = $integrity_hashes[$filename];
            }
            $event = new Render_Asset_Tag_Event(Render_Asset_Tag_Event::TYPE_LINK, $attributes['href'], $attributes);
            if (null !== $this->event_dispatcher) {
                $this->event_dispatcher->dispatch($event);
            }
            $attributes = $event->get_attributes();
            $script_tags[] = \sprintf('<link %s>', $this->convert_array_to_attributes($attributes));
            $this->rendered_files['styles'][] = $attributes['href'];
            $this->rendered_files_with_attributes['styles'][] = $attributes;
        }
        return implode('', $script_tags);
    }
    /**
     * @param bool $includeAttributes Whether to include the attributes or not.
     *                                In WebpackEncoreBundle 3.0, this parameter will be removed,
     *                                and the attributes will always be included.
     *                                TODO WebpackEncoreBundle 3.0
     *
     * @return ($includeAttributes is true ? list<array<string, mixed>> : list<string>)
     */
    public function get_rendered_scripts(bool $include_attributes = false): array
    {
        return $include_attributes ? $this->rendered_files_with_attributes['scripts'] : $this->rendered_files['scripts'];
    }
    /**
     * @param bool $includeAttributes Whether to include the attributes or not.
     *                                In WebpackEncoreBundle 3.0, this parameter will be removed,
     *                                and the attributes will always be included.
     *                                TODO WebpackEncoreBundle 3.0
     *
     * @return ($includeAttributes is true ? list<array<string, mixed>> : list<string>)
     */
    public function get_rendered_styles(bool $include_attributes = false): array
    {
        return $include_attributes ? $this->rendered_files_with_attributes['styles'] : $this->rendered_files['styles'];
    }
    public function get_default_attributes(): array
    {
        return $this->default_attributes;
    }
    public function reset(): void
    {
        $this->rendered_files = $this->rendered_files_with_attributes = ['scripts' => [], 'styles' => []];
    }
    private function get_asset_path(string $asset_path, ?string $package_name = null): string
    {
        if (null === $this->packages) {
            throw new \Exception('To render the script or link tags, run "composer require symfony/asset".');
        }
        return $this->packages->get_url($asset_path, $package_name);
    }
    private function get_entrypoint_lookup(string $build_name): Entrypoint_Lookup_Interface
    {
        return $this->entrypoint_lookup_collection->get_entrypoint_lookup($build_name);
    }
    private function convert_array_to_attributes(array $attributes_map): string
    {
        // remove attributes set specifically to false
        $attributes_map = array_filter($attributes_map, static fn($value) => false !== $value);
        return implode(' ', array_map(static function (string $key, int|string $value): string {
            // allows for things like defer: true to only render "defer"
            if (null === $value) {
                return $key;
            }
            return \sprintf('%s="%s"', $key, htmlentities($value));
        }, array_keys($attributes_map), $attributes_map));
    }
}