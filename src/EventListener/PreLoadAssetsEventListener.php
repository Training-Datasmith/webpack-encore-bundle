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
use Symfony\Component\Http_Kernel\Event\Response_Event;
use Symfony\Component\Web_Link\Generic_Link_Provider;
use Symfony\Component\Web_Link\Link;
use Symfony\Webpack_Encore_Bundle\Asset\Tag_Renderer;
/**
 * @author Ryan Weaver <ryan@symfonycasts.com>
 */
class Pre_Load_Assets_Event_Listener implements Event_Subscriber_Interface
{
    public function __construct(private readonly Tag_Renderer $tag_renderer)
    {
    }
    public function on_kernel_response(Response_Event $event): void
    {
        if (!$event->is_main_request()) {
            return;
        }
        $request = $event->get_request();
        if (null === $link_provider = $request->attributes->get('_links')) {
            $request->attributes->set('_links', new Generic_Link_Provider());
        }
        /** @var GenericLinkProvider $linkProvider */
        $link_provider = $request->attributes->get('_links');
        $default_attributes = $this->tag_renderer->get_default_attributes();
        foreach ($this->tag_renderer->get_rendered_scripts(true) as $attributes) {
            $src = $attributes['src'];
            unset($attributes['src']);
            $attributes = [...$default_attributes, ...$attributes];
            $link = $this->create_link('preload', $src)->with_attribute('as', 'script');
            foreach ($attributes as $k => $v) {
                $link = $link->with_attribute($k, $v);
            }
            $link_provider = $link_provider->with_link($link);
        }
        foreach ($this->tag_renderer->get_rendered_styles(true) as $attributes) {
            $href = $attributes['href'];
            unset($attributes['href']);
            $attributes = [...$default_attributes, ...$attributes];
            $link = $this->create_link('preload', $href)->with_attribute('as', 'style');
            foreach ($attributes as $k => $v) {
                $link = $link->with_attribute($k, $v);
            }
            $link_provider = $link_provider->with_link($link);
        }
        $request->attributes->set('_links', $link_provider);
    }
    public static function get_subscribed_events(): array
    {
        return [
            // must run before AddLinkHeaderListener
            'kernel.response' => ['onKernelResponse', 50],
        ];
    }
    private function create_link(string $rel, string $href): Link
    {
        return new Link($rel, $href);
    }
}