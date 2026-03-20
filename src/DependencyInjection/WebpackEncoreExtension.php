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
namespace Symfony\Webpack_Encore_Bundle\Dependency_Injection;

use Symfony\Component\Config\File_Locator;
use Symfony\Component\Dependency_Injection\Alias;
use Symfony\Component\Dependency_Injection\Compiler\Service_Locator_Tag_Pass;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Dependency_Injection\Definition;
use Symfony\Component\Dependency_Injection\Loader\Php_File_Loader;
use Symfony\Component\Dependency_Injection\Reference;
use Symfony\Component\Http_Kernel\Dependency_Injection\Extension;
use Symfony\Component\Web_Link\Event_Listener\Add_Link_Header_Listener;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Interface;
use Symfony\Webpack_Encore_Bundle\Event_Listener\Reset_Assets_Event_Listener;
final class Webpack_Encore_Extension extends Extension
{
    private const ENTRYPOINTS_FILE_NAME = 'entrypoints.json';
    public function load(array $configs, Container_Builder $container): void
    {
        $loader = new Php_File_Loader($container, new File_Locator(\dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.php');
        $configuration = $this->get_configuration($configs, $container);
        $config = $this->process_configuration($configuration, $configs);
        $factories = [];
        $cache_keys = [];
        if (false !== $config['output_path']) {
            $factories['_default'] = $this->entrypoint_factory($container, '_default', $config['output_path'], $config['cache'], $config['strict_mode']);
            $cache_keys['_default'] = $config['output_path'] . '/' . self::ENTRYPOINTS_FILE_NAME;
            $container->get_definition('webpack_encore.entrypoint_lookup_collection')->set_argument(1, '_default');
        }
        foreach ($config['builds'] as $name => $path) {
            $factories[$name] = $this->entrypoint_factory($container, $name, $path, $config['cache'], $config['strict_mode']);
            $cache_keys[rawurlencode((string) $name)] = $path . '/' . self::ENTRYPOINTS_FILE_NAME;
        }
        $container->get_definition('webpack_encore.exception_listener')->replace_argument(1, array_keys($factories));
        $container->get_definition('webpack_encore.entrypoint_lookup.cache_warmer')->replace_argument(0, $cache_keys);
        $container->get_definition('webpack_encore.entrypoint_lookup_collection')->replace_argument(0, Service_Locator_Tag_Pass::register($container, $factories));
        $container->get_definition(Reset_Assets_Event_Listener::class)->set_argument(1, array_keys($factories));
        if (false !== $config['output_path']) {
            $container->set_alias(Entrypoint_Lookup_Interface::class, new Alias($this->get_entrypoint_service_id('_default')));
        }
        $default_attributes = [];
        if (false !== $config['crossorigin']) {
            $default_attributes['crossorigin'] = $config['crossorigin'];
        }
        $container->get_definition('webpack_encore.tag_renderer')->replace_argument(2, $default_attributes)->replace_argument(3, $config['script_attributes'])->replace_argument(4, $config['link_attributes']);
        if ($config['preload']) {
            if (!class_exists(Add_Link_Header_Listener::class)) {
                throw new \LogicException('To use the "preload" option, the WebLink component must be installed. Try running "composer require symfony/web-link".');
            }
        } else {
            $container->remove_definition('webpack_encore.preload_assets_event_listener');
        }
    }
    private function entrypoint_factory(Container_Builder $container, string $name, string $path, bool $cache_enabled, bool $strict_mode): Reference
    {
        $id = $this->get_entrypoint_service_id($name);
        $arguments = [$path . '/' . self::ENTRYPOINTS_FILE_NAME, $cache_enabled ? new Reference('webpack_encore.cache') : null, $name, $strict_mode, new Reference('http_client', Container_Builder::NULL_ON_INVALID_REFERENCE)];
        $definition = new Definition(Entrypoint_Lookup::class, $arguments);
        $definition->add_tag('kernel.reset', ['method' => 'reset']);
        $container->set_definition($id, $definition);
        return new Reference($id);
    }
    private function get_entrypoint_service_id(string $name): string
    {
        return \sprintf('webpack_encore.entrypoint_lookup[%s]', $name);
    }
}