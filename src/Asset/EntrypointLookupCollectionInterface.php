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

use Symfony\Webpack_Encore_Bundle\Exception\Undefined_Build_Exception;
interface Entrypoint_Lookup_Collection_Interface
{
    /**
     * Retrieve the EntrypointLookupInterface for the given build.
     *
     * @throws UndefinedBuildException if the build does not exist
     */
    public function get_entrypoint_lookup(?string $build_name = null): Entrypoint_Lookup_Interface;
}