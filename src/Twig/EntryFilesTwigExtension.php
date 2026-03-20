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
namespace Symfony\Webpack_Encore_Bundle\Twig;

use Psr\Container\Container_Interface;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Interface;
use Symfony\Webpack_Encore_Bundle\Asset\Tag_Renderer;
use Twig\Extension\Abstract_Extension;
use Twig\Twig_Function;
final class Entry_Files_Twig_Extension extends Abstract_Extension
{
    public function __construct(private readonly Container_Interface $container)
    {
    }
    public function get_functions(): array
    {
        return [new Twig_Function('encore_entry_js_files', $this->get_webpack_js_files(...)), new Twig_Function('encore_entry_css_files', $this->get_webpack_css_files(...)), new Twig_Function('encore_entry_script_tags', $this->render_webpack_script_tags(...), ['is_safe' => ['html']]), new Twig_Function('encore_entry_link_tags', $this->render_webpack_link_tags(...), ['is_safe' => ['html']]), new Twig_Function('encore_entry_exists', $this->entry_exists(...))];
    }
    public function get_webpack_js_files(string $entry_name, string $entrypoint_name = '_default'): array
    {
        return $this->get_entrypoint_lookup($entrypoint_name)->get_java_script_files($entry_name);
    }
    public function get_webpack_css_files(string $entry_name, string $entrypoint_name = '_default'): array
    {
        return $this->get_entrypoint_lookup($entrypoint_name)->get_css_files($entry_name);
    }
    public function render_webpack_script_tags(string $entry_name, ?string $package_name = null, string $entrypoint_name = '_default', array $attributes = []): string
    {
        return $this->get_tag_renderer()->render_webpack_script_tags($entry_name, $package_name, $entrypoint_name, $attributes);
    }
    public function render_webpack_link_tags(string $entry_name, ?string $package_name = null, string $entrypoint_name = '_default', array $attributes = []): string
    {
        return $this->get_tag_renderer()->render_webpack_link_tags($entry_name, $package_name, $entrypoint_name, $attributes);
    }
    public function entry_exists(string $entry_name, string $entrypoint_name = '_default'): bool
    {
        $entrypoint_lookup = $this->get_entrypoint_lookup($entrypoint_name);
        if (!$entrypoint_lookup instanceof Entrypoint_Lookup) {
            throw new \LogicException(\sprintf('Cannot use entryExists() unless the entrypoint lookup is an instance of "%s"', Entrypoint_Lookup::class));
        }
        return $entrypoint_lookup->entry_exists($entry_name);
    }
    private function get_entrypoint_lookup(string $entrypoint_name): Entrypoint_Lookup_Interface
    {
        return $this->container->get('webpack_encore.entrypoint_lookup_collection')->get_entrypoint_lookup($entrypoint_name);
    }
    private function get_tag_renderer(): Tag_Renderer
    {
        return $this->container->get('webpack_encore.tag_renderer');
    }
}