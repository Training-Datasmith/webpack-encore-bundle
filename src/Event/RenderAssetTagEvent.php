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
namespace Symfony\Webpack_Encore_Bundle\Event;

/**
 * Dispatched each time a script or link tag is rendered.
 */
final class Render_Asset_Tag_Event
{
    public const TYPE_SCRIPT = 'script';
    public const TYPE_LINK = 'link';
    public function __construct(private readonly string $type, private readonly string $url, private array $attributes)
    {
    }
    public function is_script_tag(): bool
    {
        return self::TYPE_SCRIPT === $this->type;
    }
    public function is_link_tag(): bool
    {
        return self::TYPE_LINK === $this->type;
    }
    public function get_url(): string
    {
        return $this->url;
    }
    public function get_attributes(): array
    {
        return $this->attributes;
    }
    /**
     * @param string      $name  The attribute name
     * @param string|bool $value Value can be "true" to have an attribute without a value (e.g. "defer")
     */
    public function set_attribute(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }
    public function remove_attribute(string $name): void
    {
        unset($this->attributes[$name]);
    }
}