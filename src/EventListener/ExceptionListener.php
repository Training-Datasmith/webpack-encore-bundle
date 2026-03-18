<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\EventListener;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollection;

class ExceptionListener
{
    public function __construct(private readonly EntrypointLookupCollection $entrypointLookupCollection, private readonly array $buildNames)
    {
    }

    public function onKernelException(): void
    {
        foreach ($this->buildNames as $buildName) {
            $this->entrypointLookupCollection->getEntrypointLookup($buildName)->reset();
        }
    }
}
