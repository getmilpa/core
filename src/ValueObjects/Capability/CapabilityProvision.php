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
 * `exclusive` is TRI-STATE, and NULL means nobody decided.
 *
 * It used to default TRUE for a rich record and FALSE for a bare FQCN, so the
 * same capability admitted one or several providers depending on HOW IT WAS
 * WRITTEN — and the documented migration path runs bare → rich, so migrating a
 * multi-provider capability as the docs recommend would have blocked boot.
 * {@see \Milpa\Services\CapabilityMatcher} killed the same shape for
 * `contractVersion`; this is the same lie one field over.
 *
 * ADR-0037 measured that nobody decides the cardinality of a capability today
 * and refused to legislate who should. So this field stops inventing an answer:
 * an explicit `true`/`false` is a decision, absence is the absence of one, and
 * what the resolver does with an undecided cardinality is the resolver's to
 * report — never this record's to guess.
 */
final class CapabilityProvision
{
    /**
     * @param string|null $contractVersion NULL means nobody declared one. Only {@see fromInterface()}
     *                                     produces it: a rich record must still declare its version.
     *
     * @throws \InvalidArgumentException If `id`/`interface` are empty or `contractVersion` is not valid semver.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $interface,
        public readonly ?string $contractVersion,
        public readonly ?string $service = null,
        public readonly int $priority = 0,
        public readonly ?bool $exclusive = null,
    ) {
        if (trim($this->id) === '') {
            throw new \InvalidArgumentException('Capability `provides` record requires a non-empty "id".');
        }

        if (trim($this->interface) === '') {
            throw new \InvalidArgumentException(
                "Capability `provides` record \"{$this->id}\" requires a non-empty \"interface\"."
            );
        }

        // NULL means UNKNOWN — nobody declared a contract version. Anything else must be real
        // semver, so an empty string still fails exactly as it did before.
        if ($this->contractVersion !== null) {
            SemanticVersion::parse($this->contractVersion);
        }
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
        // Un `null` EXPLÍCITO significa desconocida y viaja intacto — es lo que un registro legacy
        // serializa y vuelve a leer. La AUSENCIA de la llave sigue siendo un error: un registro rico
        // tiene que declarar su versión, y aflojar eso convertiría una validación en silencio.
        $contractVersion = \array_key_exists('contractVersion', $record) && $record['contractVersion'] === null
            ? null
            : trim((string) ($record['contractVersion'] ?? ''));

        $service = isset($record['service']) && (string) $record['service'] !== ''
            ? (string) $record['service']
            : null;

        return new self(
            id: $id,
            interface: $interface,
            contractVersion: $contractVersion,
            service: $service,
            priority: (int) ($record['priority'] ?? 0),
            // Sólo una declaración EXPLÍCITA decide. La ausencia viaja como `null`.
            exclusive: \array_key_exists('exclusive', $record) && $record['exclusive'] !== null
                ? (bool) $record['exclusive']
                : null,
        );
    }

    /**
     * Wrap a legacy bare-FQCN declaration as a record whose contract version is UNKNOWN.
     *
     * Leaves `exclusive` NULL: a legacy declaration predates the field, so it says nothing
     * about how many providers the capability admits. It used to pin `false` — the mirror of
     * the rich record's `true` — and between the two, cardinality was decided by SPELLING.
     * Neither pin was a policy; §3.1's exclusive-by-default was retired in P17.3 for the same
     * reason (ADR-0037).
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
            // NOT '0.0.0'. A legacy declaration says nothing about the contract version, and
            // `0.0.0` is a real version: against `^1.0` it reads as "too old", which is a
            // different statement from "nobody said". Measured in `settlement-q-p17.md` —
            // the engine rejected legacy providers for a reason it invented.
            contractVersion: null,
            // Tampoco `false`: una declaración legacy es anterior al campo, así que no dice nada
            // sobre la multiplicidad. Fijar `false` aquí era decidir por quien no decidió.
            exclusive: null,
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
