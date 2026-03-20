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

use Symfony\Contracts\Service\Reset_Interface;
use Symfony\Webpack_Encore_Bundle\Exception\Entrypoint_Not_Found_Exception;
interface Entrypoint_Lookup_Interface extends Reset_Interface
{
    /**
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     */
    public function get_java_script_files(string $entry_name): array;
    /**
     * @throws EntrypointNotFoundException if an entry name is passed that does not exist in entrypoints.json
     */
    public function get_css_files(string $entry_name): array;
    /**
     * Resets the state of this service.
     */
    public function reset(): void;
}