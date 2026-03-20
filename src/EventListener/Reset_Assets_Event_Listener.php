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

use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Kernel\Event\Finish_Request_Event;
use Symfony\Component\Http_Kernel\Kernel_Events;
use Symfony\Webpack_Encore_Bundle\Asset\Entrypoint_Lookup_Collection;
class Reset_Assets_Event_Listener implements Event_Subscriber_Interface
{
    public function __construct(private readonly Entrypoint_Lookup_Collection $entrypoint_lookup_collection, private readonly array $build_names)
    {
    }
    public static function get_subscribed_events(): array
    {
        return [Kernel_Events::FINISH_REQUEST => 'resetAssets'];
    }
    public function reset_assets(Finish_Request_Event $event): void
    {
        if (!$event->is_main_request()) {
            return;
        }
        foreach ($this->build_names as $name) {
            $this->entrypoint_lookup_collection->get_entrypoint_lookup($name)->reset();
        }
    }
}