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

namespace Milpa\ValueObjects\Tooling;

/**
 * Typed replacement for the untyped `array $options` previously accepted by
 * {@see \Milpa\Interfaces\Tooling\ToolRegistryInterface::register()}.
 *
 * The concrete tool-runtime {@see \Milpa\ToolRuntime\ToolDefinition} only
 * understands `scopes|mutating|requiresConfirmation|timeout|clamps|version|outputSchema`.
 * Passing an unknown key (e.g. `category`) in the old raw-array API silently vanished —
 * {@see ToolDefinition} has no such property, so it was dropped with no error.
 * {@see self::fromArray()} closes that gap by rejecting unknown keys outright.
 */
final readonly class ToolOptions
{
    /**
     * @param array<string>                   $scopes       Required auth scopes for this tool
     * @param array<string, array<int|float>> $clamps       Per-argument numeric clamps ({min, max})
     * @param array<string, mixed>|null       $outputSchema JSON-schema-like output definition
     */
    public function __construct(
        public array $scopes = [],
        public bool $mutating = false,
        public bool $requiresConfirmation = false,
        public ?int $timeout = null,
        public array $clamps = [],
        public ?string $version = null,
        public ?array $outputSchema = null,
    ) {
    }

    /**
     * Build from a raw options array (the shape the old `array $options` parameter accepted).
     *
     * @param array<string, mixed> $options
     *
     * @throws \InvalidArgumentException if $options contains a key this VO does not model
     */
    public static function fromArray(array $options): self
    {
        $known = ['scopes', 'mutating', 'requiresConfirmation', 'timeout', 'clamps', 'version', 'outputSchema'];

        $unknown = array_diff(array_keys($options), $known);
        if ($unknown !== []) {
            throw new \InvalidArgumentException(
                'Unknown ToolOptions key(s): ' . implode(', ', $unknown)
                . '. Known keys: ' . implode(', ', $known) . '.'
            );
        }

        /** @var array<string> $scopes */
        $scopes = $options['scopes'] ?? [];
        /** @var array<string, array<int|float>> $clamps */
        $clamps = $options['clamps'] ?? [];
        /** @var array<string, mixed>|null $outputSchema */
        $outputSchema = $options['outputSchema'] ?? null;

        return new self(
            scopes: $scopes,
            mutating: (bool) ($options['mutating'] ?? false),
            requiresConfirmation: (bool) ($options['requiresConfirmation'] ?? false),
            timeout: $options['timeout'] ?? null,
            clamps: $clamps,
            version: $options['version'] ?? null,
            outputSchema: $outputSchema,
        );
    }

    /**
     * Expose the options as a plain array for the registry's internal use.
     *
     * @return array{scopes: array<string>, mutating: bool, requiresConfirmation: bool, timeout: ?int, clamps: array<string, array<int|float>>, version: ?string, outputSchema: ?array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'scopes' => $this->scopes,
            'mutating' => $this->mutating,
            'requiresConfirmation' => $this->requiresConfirmation,
            'timeout' => $this->timeout,
            'clamps' => $this->clamps,
            'version' => $this->version,
            'outputSchema' => $this->outputSchema,
        ];
    }
}
