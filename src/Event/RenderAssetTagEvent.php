<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\Event;

/**
 * Dispatched each time a script or link tag is rendered.
 */
final class RenderAssetTagEvent
{
    public const TYPE_SCRIPT = 'script';
    public const TYPE_LINK = 'link';

    public function __construct(private readonly string $type, private readonly string $url, private array $attributes)
    {
    }

    public function isScriptTag(): bool
    {
        return self::TYPE_SCRIPT === $this->type;
    }

    public function isLinkTag(): bool
    {
        return self::TYPE_LINK === $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string      $name  The attribute name
     * @param string|bool $value Value can be "true" to have an attribute without a value (e.g. "defer")
     */
    public function setAttribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function removeAttribute(string $name): void
    {
        unset($this->attributes[$name]);
    }
}
