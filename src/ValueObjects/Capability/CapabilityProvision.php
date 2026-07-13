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

namespace Milpa\ValueObjects\Capability;

use Milpa\ValueObjects\SemanticVersion;

/**
 * A `provides` capability record.
 *
 * Declares that a plugin offers a concrete implementation (`service`) of a
 * stable contract (`interface`) at a given `contractVersion`.
 *
 * Also accepts the legacy bare-FQCN string form via {@see fromInterface()} so
 * legacy manifests (`contracts.provides = ["Foo\\BarInterface"]`) keep working
 * until the capability records are fully adopted.
 *
 * The primary constructor validates exactly like {@see fromArray()} does: `id`
 * and `interface` must be non-empty and `contractVersion` must be valid semver.
 * There is no "trusted, pre-validated" construction path — hand-building a
 * record (e.g. `new CapabilityProvision(...)` from reflected `#[PluginMetadata]`
 * data) is validated identically to parsing one from a manifest.
 *
 * `exclusive` defaults to TRUE, per capability-spec §3.1, verbatim: "`id` MUST
 * be globally unique in the installed graph unless `exclusive=false` allows
 * multi-provider lists" — the same default `milpa-plugin.schema.json` declares.
 * Providers that legitimately share an id must opt out with an explicit
 * `exclusive: false`. The legacy bare-FQCN wrapper {@see fromInterface()} is
 * the one deliberate exception: it pins `exclusive: false` because legacy
 * declarations predate the field and carry documented multi-provider semantics.
 */
final class CapabilityProvision
{
    /**
     * @throws \InvalidArgumentException If `id`/`interface` are empty or `contractVersion` is not valid semver.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $interface,
        public readonly string $contractVersion,
        public readonly ?string $service = null,
        public readonly int $priority = 0,
        public readonly bool $exclusive = true,
    ) {
        if (trim($this->id) === '') {
            throw new \InvalidArgumentException('Capability `provides` record requires a non-empty "id".');
        }

        if (trim($this->interface) === '') {
            throw new \InvalidArgumentException(
                "Capability `provides` record \"{$this->id}\" requires a non-empty \"interface\"."
            );
        }

        // Throws \InvalidArgumentException when not valid semver.
        SemanticVersion::parse($this->contractVersion);
    }

    /**
     * Build a provision record from a decoded `provides` manifest entry. Coerces raw
     * (possibly untyped) array values to their expected shape; validation of the
     * result (`id`/`interface` non-empty, `contractVersion` valid semver) happens in
     * the constructor, not here.
     *
     * @param array<string, mixed> $record
     *
     * @throws \InvalidArgumentException If `id`/`interface` are empty or `contractVersion` is not valid semver.
     */
    public static function fromArray(array $record): self
    {
        $id = trim((string) ($record['id'] ?? ''));
        $interface = trim((string) ($record['interface'] ?? ''));
        $contractVersion = trim((string) ($record['contractVersion'] ?? ''));

        $service = isset($record['service']) && (string) $record['service'] !== ''
            ? (string) $record['service']
            : null;

        return new self(
            id: $id,
            interface: $interface,
            contractVersion: $contractVersion,
            service: $service,
            // capability-spec §3.1: "id MUST be globally unique in the installed graph unless
            // `exclusive=false` allows multi-provider lists" — absent means exclusive.
            priority: (int) ($record['priority'] ?? 0),
            exclusive: (bool) ($record['exclusive'] ?? true),
        );
    }

    /**
     * Wrap a legacy bare-FQCN declaration as an unversioned record.
     *
     * Pins `exclusive: false` explicitly: a legacy declaration predates the `exclusive`
     * field, so it cannot opt out of §3.1's exclusive-by-default rule — and the legacy
     * graph's documented semantics allow duplicated providers (last provider wins as the
     * ordering edge source). Applying the structured-record default retroactively would
     * turn every duplicated legacy provider into a blocking conflict.
     */
    public static function fromInterface(string $interface): self
    {
        $interface = trim($interface);
        if ($interface === '') {
            throw new \InvalidArgumentException('Capability `provides` interface FQCN must be non-empty.');
        }

        return new self(
            id: $interface,
            interface: $interface,
            contractVersion: '0.0.0',
            exclusive: false,
        );
    }

    /**
     * Parse a `provides` manifest entry in either the legacy bare-FQCN string form or the
     * structured-record array form, dispatching to {@see fromInterface()} or {@see fromArray()}.
     *
     * @param string|array<string, mixed> $record
     */
    public static function parse(string|array $record): self
    {
        return is_string($record)
            ? self::fromInterface($record)
            : self::fromArray($record);
    }
}
