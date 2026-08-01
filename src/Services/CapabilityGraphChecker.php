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

namespace Milpa\Services;

use Milpa\Attributes\PluginMetadata;
use Milpa\Exceptions\AttributeNotFoundException;
use Milpa\Exceptions\Plugin\PluginDependencyException;

/**
 * The provides/requires capability graph check: fails BEFORE boot, with a
 * readable message, when a plugin's declared `requires` has no matching
 * `provides` among the given plugins. This is the "A provee / B requiere"
 * edge of the `plugin → capability → tool → verification → event → result`
 * loop — every host application that boots plugins needs it, so it lives
 * here instead of being hand-rolled per consumer (it originated as exactly
 * that: an application-level `CapabilityGraph` reflecting `#[PluginMetadata]`
 * by hand).
 *
 * Only `requires` is enforced. A missing `suggests` MUST NOT fail the graph
 * — that is the whole point of {@see \Milpa\ValueObjects\Capability\CapabilitySuggestion}
 * being an optional dependency with a `fallback`, and this checker honors it
 * by simply never looking at `suggests`.
 *
 * This check only reasons about IDENTITY (does *some* plugin provide the
 * required thing at all), never about `contractVersion`/`constraint`
 * ranges — range-checking is the architecture resolver's job. That layering
 * is deliberate and survives; what did NOT survive is this class having its
 * own idea of what "the same capability" means. Identity now comes from
 * {@see CapabilityMatcher}, the single criterion the resolver, the manifest
 * validator and the inspector all consume (`settlement-q-p17.md` measured
 * four comparators disagreeing).
 *
 * A `#[PluginMetadata]` entry may be a bare interface FQCN (legacy) or a
 * structured capability record (canonical — T087), and the matcher treats
 * `php:Acme\Thing` and `Acme\Thing` as one identity so both forms coexist. A
 * record offers BOTH its `id` and its `interface`, and a record requirement is
 * satisfied by either or by any of its `oneOf` alternatives (a requirement the
 * resolver would satisfy via `oneOf` must not fail pre-boot here). A record
 * entry with no readable identity contributes nothing — teaching the
 * malformed-record failure is the ingestion layer's job
 * ({@see \Milpa\ValueObjects\Capability\CapabilityProvision::fromArray()}),
 * not this check's.
 */
final class CapabilityGraphChecker
{
    /**
     * @param CapabilityMatcher $matcher The single identity criterion. Injectable so a
     *                                   consumer can share one instance; the default is
     *                                   the same law, not a different one.
     */
    public function __construct(private readonly CapabilityMatcher $matcher = new CapabilityMatcher())
    {
    }

    /**
     * Checks that every `requires` entry across `$plugins` is matched by a
     * `provides` entry somewhere in `$plugins` (including, if declared, the
     * requiring plugin's own `provides` — a plugin may satisfy its own
     * requirement).
     *
     * @param list<object> $plugins Plugin instances carrying `#[PluginMetadata]`,
     *                              or `PluginMetadata` records passed directly
     *                              (e.g. already extracted from a manifest —
     *                              no reflection needed in that case).
     *
     * @throws AttributeNotFoundException If a plugin instance carries no `#[PluginMetadata]` attribute.
     * @throws PluginDependencyException  If a `requires` entry has no matching `provides` among `$plugins`.
     */
    public function check(array $plugins): void
    {
        $metadata = array_map($this->metadataOf(...), $plugins);

        $provided = [];
        foreach ($metadata as $meta) {
            foreach ($meta->provides as $entry) {
                foreach ($this->matcher->identitiesOffered($entry) as $identity) {
                    $provided[$identity] = true;
                }
            }
        }

        foreach ($metadata as $meta) {
            foreach ($meta->requires as $entry) {
                $alternatives = $this->matcher->identitiesAccepted($entry);
                if ($alternatives === []) {
                    continue;
                }
                if (array_intersect($alternatives, array_keys($provided)) === []) {
                    throw PluginDependencyException::unmet($meta->name, $this->nameOf($entry, $alternatives[0]));
                }
            }
        }
    }

    /**
     * How the unmet requirement is named in the failure message: as it was
     * written, not as it was canonicalized. A developer who declared
     * `\Acme\Thing` should read `\Acme\Thing` back, not a normalized form they
     * never typed.
     *
     * @param string|array<string, mixed> $entry
     */
    private function nameOf(string|array $entry, string $fallback): string
    {
        if (is_string($entry)) {
            return $entry;
        }

        foreach (['id', 'interface'] as $key) {
            $value = $entry[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return $fallback;
    }

    /**
     * Resolves the `#[PluginMetadata]` for one entry: returned as-is if
     * `$plugin` already IS a `PluginMetadata` record, otherwise read via
     * reflection off the instance's class attribute.
     *
     * @throws AttributeNotFoundException If `$plugin` is not a `PluginMetadata` and carries no `#[PluginMetadata]` attribute.
     */
    private function metadataOf(object $plugin): PluginMetadata
    {
        if ($plugin instanceof PluginMetadata) {
            return $plugin;
        }

        $attributes = (new \ReflectionClass($plugin))->getAttributes(PluginMetadata::class);
        if ($attributes === []) {
            throw new AttributeNotFoundException(
                $plugin::class . ' has no #[PluginMetadata] attribute'
                . ' (pass a PluginMetadata instance directly if the metadata is not attached to a class).'
            );
        }

        return $attributes[0]->newInstance();
    }
}
