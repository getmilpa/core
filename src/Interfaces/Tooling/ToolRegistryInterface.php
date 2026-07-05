<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Tooling;

use Milpa\app\ValueObjects\Tooling\ToolOptions;

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
     * @param ?ToolOptions          $options     scopes|mutating|requiresConfirmation|timeout|clamps|version|outputSchema
     */
    public function register(
        string $name,
        string $description,
        array $inputSchema,
        callable $callback,
        ?ToolOptions $options = null
    ): void;
}
