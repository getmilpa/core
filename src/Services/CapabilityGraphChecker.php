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
 * ranges — range-checking is the architecture resolver's job. A
 * `#[PluginMetadata]` entry may be a bare interface FQCN (legacy) or a
 * structured capability record (canonical — T087): a bare string is one
 * identity, verbatim; a record provides BOTH its capability `id` and its
 * `interface`, and a record requirement is satisfied by its `id`, its
 * `interface`, or any of its `oneOf` alternatives (a requirement the
 * resolver would satisfy via `oneOf` must not fail pre-boot here). A
 * record entry with no readable identity contributes nothing — teaching
 * the malformed-record failure is the ingestion layer's job
 * ({@see \Milpa\ValueObjects\Capability\CapabilityProvision::fromArray()}),
 * not this check's.
 */
final class CapabilityGraphChecker
{
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
                foreach ($this->identitiesOf($entry) as $identity) {
                    $provided[$identity] = true;
                }
            }
        }

        foreach ($metadata as $meta) {
            foreach ($meta->requires as $entry) {
                $alternatives = $this->alternativesOf($entry);
                if ($alternatives === []) {
                    continue;
                }
                if (!$this->anyProvided($provided, $alternatives)) {
                    throw PluginDependencyException::unmet($meta->name, $alternatives[0]);
                }
            }
        }
    }

    /**
     * The identity strings one capability entry contributes: the string itself
     * (verbatim) for a bare FQCN, or the record's `id` and `interface` for a
     * structured capability record. An entry that is neither yields nothing.
     *
     * @return list<string>
     */
    private function identitiesOf(mixed $entry): array
    {
        if (is_string($entry)) {
            return [$entry];
        }

        if (!is_array($entry)) {
            return [];
        }

        $identities = [];
        foreach (['id', 'interface'] as $key) {
            $value = $entry[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $identities[] = trim($value);
            }
        }

        return $identities;
    }

    /**
     * Every identity that can satisfy one `requires` entry: its own identities
     * plus any `oneOf` alternatives a structured record declares. The FIRST
     * entry (a record's `id`, or the bare FQCN itself) names the requirement
     * in failure messages.
     *
     * @return list<string>
     */
    private function alternativesOf(mixed $entry): array
    {
        $alternatives = $this->identitiesOf($entry);

        if (is_array($entry)) {
            $oneOf = $entry['oneOf'] ?? [];
            foreach (is_array($oneOf) ? $oneOf : [] as $candidate) {
                if (is_string($candidate) && trim($candidate) !== '') {
                    $alternatives[] = trim($candidate);
                }
            }
        }

        return $alternatives;
    }

    /**
     * Whether any of the requirement's alternatives is in the provided set.
     *
     * @param array<string, true> $provided
     * @param list<string>        $alternatives
     */
    private function anyProvided(array $provided, array $alternatives): bool
    {
        foreach ($alternatives as $alternative) {
            if (isset($provided[$alternative])) {
                return true;
            }
        }

        return false;
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
