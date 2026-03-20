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
namespace Symfony\Webpack_Encore_Bundle\Event_Listener;

use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Collection;
class Exception_Listener
{
    public function __construct(private readonly Entrypoint_Lookup_Collection $entrypoint_lookup_collection, private readonly array $build_names)
    {
    }
    public function on_kernel_exception(): void
    {
        foreach ($this->build_names as $build_name) {
            $this->entrypoint_lookup_collection->get_entrypoint_lookup($build_name)->reset();
        }
    }
}