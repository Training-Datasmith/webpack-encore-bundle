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

use Psr\Container\Container_Interface;
use Symfony\Webpack_Encore_Bundle\Exception\Undefined_Build_Exception;
/**
 * Aggregate the different entry points configured in the container.
 *
 * Retrieve the EntrypointLookup instance from the given key.
 *
 * @final
 */
class Entrypoint_Lookup_Collection implements Entrypoint_Lookup_Collection_Interface
{
    public function __construct(private readonly Container_Interface $build_entrypoints, private readonly ?string $default_build_name = null)
    {
    }
    public function get_entrypoint_lookup(?string $build_name = null): Entrypoint_Lookup_Interface
    {
        if (null === $build_name) {
            if (null === $this->default_build_name) {
                throw new Undefined_Build_Exception('There is no default build configured: please pass an argument to getEntrypointLookup().');
            }
            $build_name = $this->default_build_name;
        }
        if (!$this->build_entrypoints->has($build_name)) {
            throw new Undefined_Build_Exception(\sprintf('The build "%s" is not configured', $build_name));
        }
        return $this->build_entrypoints->get($build_name);
    }
}