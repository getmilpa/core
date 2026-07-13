<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
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
     * @param string                                   $type     The plugin's kind, by what surface it exposes. `$type` is a
     *                                                           plain `string`, not a backed enum — deliberately: the four
     *                                                           values below are the observed, sanctioned vocabulary (every
     *                                                           plugin across the Milpa ecosystem uses one of them, and the
     *                                                           scaffolding CLI only ever generates one of them), but making
     *                                                           it a native enum type would be a breaking change for every
     *                                                           existing `#[PluginMetadata(type: '...')]` call site the
     *                                                           moment a host application updates core, for a check this
     *                                                           docblock (plus a lint/CI rule, if a host wants one) already
     *                                                           covers.
     *                                                           - `'Web'` — exposes HTTP-facing surface (controllers/routes).
     *                                                           - `'CLI'` — exposes only CLI commands, no HTTP surface.
     *                                                           - `'Service'` — exposes only services/tools consumed by other
     *                                                           plugins or the runtime, no direct HTTP or CLI surface of its
     *                                                           own.
     *                                                           - `'Mixed'` — exposes more than one of the above (e.g. both
     *                                                           HTTP routes and CLI commands).
     * @param array<class-string|array<string, mixed>> $provides Capabilities this plugin provides. Each
     *                                                           entry is either a bare interface FQCN (legacy, synthesized as
     *                                                           an unversioned record) or a structured capability record
     *                                                           `{id, interface, contractVersion, service, priority?,
     *                                                           exclusive?}` (canonical — capability-spec §3.1, validated by
     *                                                           {@see \Milpa\ValueObjects\Capability\CapabilityProvision}).
     *                                                           Mixing both shapes in one list is valid — the incremental
     *                                                           migration path.
     * @param array<class-string|array<string, mixed>> $requires Required capabilities (hard dependency):
     *                                                           a bare interface FQCN or a record `{id, interface, constraint,
     *                                                           oneOf?}` ({@see \Milpa\ValueObjects\Capability\CapabilityRequirement}).
     * @param array<class-string|array<string, mixed>> $suggests Optional capabilities (soft dependency):
     *                                                           a bare interface FQCN or a record `{id, interface, constraint,
     *                                                           fallback?}` ({@see \Milpa\ValueObjects\Capability\CapabilitySuggestion}).
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
