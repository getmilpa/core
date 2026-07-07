<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
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
 * This check only reasons about interface identity (does *some* plugin
 * provide the required interface at all), not about `contractVersion`
 * ranges or `oneOf` alternatives — `#[PluginMetadata]::$provides` /
 * `$requires` are bare interface FQCN lists, they carry no version
 * information to range-check against. A host that manages versioned
 * capability records (`CapabilityProvision`/`CapabilityRequirement` built
 * from a manifest via `fromArray()`) needs a richer checker; this one
 * covers the common case the attribute itself can express.
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
            foreach ($meta->provides as $interface) {
                $provided[$interface] = true;
            }
        }

        foreach ($metadata as $meta) {
            foreach ($meta->requires as $interface) {
                if (!isset($provided[$interface])) {
                    throw PluginDependencyException::unmet($meta->name, $interface);
                }
            }
        }
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
