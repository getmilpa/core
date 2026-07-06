<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Attributes;

use Attribute;

/**
 * Declares plugin identity and dependency metadata (version, author, site, name, type,
 * provides/requires/suggests service lists). Applied to a plugin's main class.
 *
 * A plugin's identity is immutable: all properties are `readonly`, so a
 * reflection-obtained instance cannot be silently mutated after construction.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class PluginMetadata
{
    /**
     * @param array<class-string> $provides Interfaces/services this plugin provides
     * @param array<class-string> $requires Required interfaces/services (hard dependency)
     * @param array<class-string> $suggests Optional interfaces/services (soft dependency)
     */
    public function __construct(
        public readonly string $version,
        public readonly string $author,
        public readonly string $site,
        public readonly string $name,
        public readonly string $type,
        public readonly array $provides = [],
        public readonly array $requires = [],
        public readonly array $suggests = []
    ) {
    }
}
