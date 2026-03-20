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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Symfony\Component\Cache\Adapter\Php_Array_Adapter;
use Symfony\Component\Dependency_Injection\Service_Locator;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Collection;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Collection_Interface;
use Symfony\Webpack_Encore_Bundle\Asset\Tag_Renderer;
use Symfony\Webpack_Encore_Bundle\Cache_Warmer\Entrypoint_Cache_Warmer;
use Symfony\Webpack_Encore_Bundle\Event_Listener\Exception_Listener;
use Symfony\Webpack_Encore_Bundle\Event_Listener\Pre_Load_Assets_Event_Listener;
use Symfony\Webpack_Encore_Bundle\Event_Listener\Reset_Assets_Event_Listener;
use Symfony\Webpack_Encore_Bundle\Twig\Entry_Files_Twig_Extension;
return static function (Container_Configurator $container_configurator): void {
    $container_configurator->services()->set('webpack_encore.entrypoint_lookup_collection', Entrypoint_Lookup_Collection::class)->args([abstract_arg('build list of entrypoints locator')])->alias(Entrypoint_Lookup_Collection_Interface::class, 'webpack_encore.entrypoint_lookup_collection')->set('webpack_encore.tag_renderer', Tag_Renderer::class)->tag('kernel.reset', ['method' => 'reset'])->args([
        service('webpack_encore.entrypoint_lookup_collection'),
        service('assets.packages'),
        [],
        // Default attributes
        [],
        // Default script attributes
        [],
        // Default link attributes
        service('event_dispatcher'),
    ])->set('webpack_encore.twig_entry_files_extension', Entry_Files_Twig_Extension::class)->tag('twig.extension')->args([inline_service(Service_Locator::class)->tag('container.service_locator')->args([['webpack_encore.entrypoint_lookup_collection' => service('webpack_encore.entrypoint_lookup_collection'), 'webpack_encore.tag_renderer' => service('webpack_encore.tag_renderer')]])])->set('webpack_encore.entrypoint_lookup.cache_warmer', Entrypoint_Cache_Warmer::class)->tag('kernel.cache_warmer')->args([abstract_arg(' build list of entrypoint paths'), service('http_client')->null_on_invalid(), '%kernel.build_dir%/webpack_encore.cache.php'])->set('webpack_encore.cache', Php_Array_Adapter::class)->factory([Php_Array_Adapter::class, 'create'])->args(['%kernel.build_dir%/webpack_encore.cache.php', service('cache.webpack_encore')])->set('cache.webpack_encore')->parent('cache.system')->tag('cache.pool')->set('webpack_encore.exception_listener', Exception_Listener::class)->tag('kernel.event_listener', ['event' => 'kernel.exception'])->args([service('webpack_encore.entrypoint_lookup_collection'), abstract_arg('build list of build names')])->set('webpack_encore.preload_assets_event_listener', Pre_Load_Assets_Event_Listener::class)->tag('kernel.event_subscriber')->args([service('webpack_encore.tag_renderer')])->set(Reset_Assets_Event_Listener::class)->tag('kernel.event_subscriber')->args([service('webpack_encore.entrypoint_lookup_collection')]);
};