<?php

declare(strict_types=1);

namespace Milpa\app\ValueObjects\Capability;

use Milpa\app\ValueObjects\SemanticVersion;

/**
 * A `provides` capability record.
 *
 * Declares that a plugin offers a concrete implementation (`service`) of a
 * stable contract (`interface`) at a given `contractVersion`.
 *
 * Also accepts the legacy bare-FQCN string form via {@see fromInterface()} so
 * legacy manifests (`contracts.provides = ["Foo\\BarInterface"]`) keep working
 * until the capability records are fully adopted.
 */
final class CapabilityProvision
{
    public function __construct(
        public readonly string $id,
        public readonly string $interface,
        public readonly string $contractVersion,
        public readonly ?string $service = null,
        public readonly int $priority = 0,
        public readonly bool $exclusive = false,
    ) {
    }

    /**
     * Build a provision record from a decoded `provides` manifest entry, validating `id`,
     * `interface`, and `contractVersion` (must be valid semver).
     *
     * @param array<string, mixed> $record
     * @throws \InvalidArgumentException If `id`/`interface` are empty or `contractVersion` is not valid semver.
     */
    public static function fromArray(array $record): self
    {
        $id = trim((string) ($record['id'] ?? ''));
        if ($id === '') {
            throw new \InvalidArgumentException('Capability `provides` record requires a non-empty "id".');
        }

        $interface = trim((string) ($record['interface'] ?? ''));
        if ($interface === '') {
            throw new \InvalidArgumentException(
                "Capability `provides` record \"{$id}\" requires a non-empty \"interface\"."
            );
        }

        // Throws \InvalidArgumentException when not valid semver.
        $contractVersion = trim((string) ($record['contractVersion'] ?? ''));
        SemanticVersion::parse($contractVersion);

        $service = isset($record['service']) && (string) $record['service'] !== ''
            ? (string) $record['service']
            : null;

        return new self(
            id: $id,
            interface: $interface,
            contractVersion: $contractVersion,
            service: $service,
            priority: (int) ($record['priority'] ?? 0),
            exclusive: (bool) ($record['exclusive'] ?? false),
        );
    }

    /**
     * Wrap a legacy bare-FQCN declaration as an unversioned record.
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
