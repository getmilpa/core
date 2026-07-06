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

namespace Milpa\ValueObjects\Capability;

/**
 * A `requires` capability record.
 *
 * Declares a HARD dependency on a contract: at least one installed provider
 * must satisfy `interface` within the semver `constraint` range.
 *
 * `oneOf` optionally lists provider capability ids any of which satisfies the
 * requirement. Legacy bare-FQCN declarations are accepted via {@see fromInterface()}.
 */
final class CapabilityRequirement
{
    /**
     * @param list<string> $oneOf
     */
    public function __construct(
        public readonly string $id,
        public readonly string $interface,
        public readonly string $constraint = '*',
        public readonly array $oneOf = [],
    ) {
    }

    /**
     * Build a requirement record from a decoded `requires` manifest entry, validating `id` and
     * `interface`, defaulting `constraint` to `*`, and normalizing `oneOf` to a list of non-empty strings.
     *
     * @param array<string, mixed> $record
     * @throws \InvalidArgumentException If `id` or `interface` is empty.
     */
    public static function fromArray(array $record): self
    {
        $id = trim((string) ($record['id'] ?? ''));
        if ($id === '') {
            throw new \InvalidArgumentException('Capability `requires` record requires a non-empty "id".');
        }

        $interface = trim((string) ($record['interface'] ?? ''));
        if ($interface === '') {
            throw new \InvalidArgumentException(
                "Capability `requires` record \"{$id}\" requires a non-empty \"interface\"."
            );
        }

        $constraint = trim((string) ($record['constraint'] ?? ''));
        if ($constraint === '') {
            $constraint = '*';
        }

        $oneOf = [];
        foreach ((array) ($record['oneOf'] ?? []) as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate !== '') {
                $oneOf[] = $candidate;
            }
        }

        return new self(id: $id, interface: $interface, constraint: $constraint, oneOf: $oneOf);
    }

    /**
     * Wrap a legacy bare-FQCN declaration as an any-version requirement.
     */
    public static function fromInterface(string $interface): self
    {
        $interface = trim($interface);
        if ($interface === '') {
            throw new \InvalidArgumentException('Capability `requires` interface FQCN must be non-empty.');
        }

        return new self(id: $interface, interface: $interface);
    }

    /**
     * Parse a `requires` manifest entry in either the legacy bare-FQCN string form or the
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
