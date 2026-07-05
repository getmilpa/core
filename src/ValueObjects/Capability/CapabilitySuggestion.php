<?php

declare(strict_types=1);

namespace Milpa\app\ValueObjects\Capability;

/**
 * A `suggests` capability record — an OPTIONAL dependency.
 *
 * A missing suggested capability MUST NOT fail boot; `fallback` names the
 * graceful-degradation path (e.g. "noop" / a null-object strategy).
 *
 * Legacy bare-FQCN declarations are accepted via {@see fromInterface()}.
 */
final class CapabilitySuggestion
{
    public function __construct(
        public readonly string $id,
        public readonly string $interface,
        public readonly string $constraint = '*',
        public readonly ?string $fallback = null,
    ) {
    }

    /**
     * Build a suggestion record from a decoded `suggests` manifest entry, validating `id` and
     * `interface`, defaulting `constraint` to `*`, and normalizing an empty `fallback` to null.
     *
     * @param array<string, mixed> $record
     * @throws \InvalidArgumentException If `id` or `interface` is empty.
     */
    public static function fromArray(array $record): self
    {
        $id = trim((string) ($record['id'] ?? ''));
        if ($id === '') {
            throw new \InvalidArgumentException('Capability `suggests` record requires a non-empty "id".');
        }

        $interface = trim((string) ($record['interface'] ?? ''));
        if ($interface === '') {
            throw new \InvalidArgumentException(
                "Capability `suggests` record \"{$id}\" requires a non-empty \"interface\"."
            );
        }

        $constraint = trim((string) ($record['constraint'] ?? ''));
        if ($constraint === '') {
            $constraint = '*';
        }

        $fallback = isset($record['fallback']) && (string) $record['fallback'] !== ''
            ? (string) $record['fallback']
            : null;

        return new self(id: $id, interface: $interface, constraint: $constraint, fallback: $fallback);
    }

    /**
     * Wrap a legacy bare-FQCN declaration as an any-version suggestion.
     */
    public static function fromInterface(string $interface): self
    {
        $interface = trim($interface);
        if ($interface === '') {
            throw new \InvalidArgumentException('Capability `suggests` interface FQCN must be non-empty.');
        }

        return new self(id: $interface, interface: $interface);
    }

    /**
     * Parse a `suggests` manifest entry in either the legacy bare-FQCN string form or the
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
