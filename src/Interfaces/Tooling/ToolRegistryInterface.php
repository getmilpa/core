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

namespace Milpa\Interfaces\Tooling;

use Milpa\ValueObjects\Tooling\ToolOptions;

/**
 * Contract for the tool registry exposed to tool providers.
 *
 * Plugins implementing {@see ToolProviderInterface} receive a registry typed
 * against this contract, so the framework's extension point does not depend on
 * the concrete tool-runtime implementation (which lives in the tool-runtime
 * plugin, not in core).
 */
interface ToolRegistryInterface
{
    /**
     * Register a tool.
     *
     * @param array<string, mixed> $inputSchema JSON-schema-like input definition
     * @param callable             $callback    Tool handler
     * @param ?ToolOptions         $options     scopes|mutating|requiresConfirmation|timeout|clamps|version|outputSchema
     */
    public function register(
        string $name,
        string $description,
        array $inputSchema,
        callable $callback,
        ?ToolOptions $options = null
    ): void;
}
