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

use Symfony\Component\Config\Definition\Builder\Array_Node_Definition;
use Symfony\Component\Config\Definition\Builder\Tree_Builder;
use Symfony\Component\Config\Definition\Configuration_Interface;
use Symfony\Component\Config\Definition\Exception\Invalid_Definition_Exception;
final class Configuration implements Configuration_Interface
{
    public function get_config_tree_builder(): Tree_Builder
    {
        $tree_builder = new Tree_Builder('webpack_encore');
        /** @var ArrayNodeDefinition $rootNode */
        $root_node = $tree_builder->get_root_node();
        $root_node->validate()->if_true(fn(array $v): bool => false === $v['output_path'] && empty($v['builds']))->then_invalid('Default build can only be disabled if multiple entry points are defined.')->end()->children()->scalar_node('output_path')->is_required()->info('The path where Encore is building the assets - i.e. Encore.setOutputPath()')->end()->enum_node('crossorigin')->default_false()->values([false, 'anonymous', 'use-credentials'])->info('crossorigin value when Encore.enableIntegrityHashes() is used, can be false (default), anonymous or use-credentials')->end()->boolean_node('preload')->info('preload all rendered script and link tags automatically via the http2 Link header.')->default_false()->end()->boolean_node('cache')->info('Enable caching of the entry point file(s)')->default_false()->end()->boolean_node('strict_mode')->info('Throw an exception if the entrypoints.json file is missing or an entry is missing from the data')->default_true()->end()->array_node('builds')->use_attribute_as_key('name')->normalize_keys(false)->scalar_prototype()->validate()->always(function (array $values): array {
            if (isset($values['_default'])) {
                throw new Invalid_Definition_Exception("Key '_default' can't be used as build name.");
            }
            return $values;
        })->end()->end()->end()->array_node('script_attributes')->info('Key/value pair of attributes to render on all script tags')->example('{ defer: true, referrerpolicy: "origin" }')->use_attribute_as_key('name')->normalize_keys(false)->scalar_prototype()->end()->end()->array_node('link_attributes')->info('Key/value pair of attributes to render on all CSS link tags')->example('{ referrerpolicy: "origin" }')->use_attribute_as_key('name')->normalize_keys(false)->scalar_prototype()->end()->end()->end();
        return $tree_builder;
    }
}