<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Tooling;


/**
 * Interface for plugins that provide AI tools.
 *
 * Plugins implementing this interface can:
 * - Register tools with the ToolRegistry for AI agent use
 * - Contribute sections to the AI system prompt
 */
interface ToolProviderInterface
{
    /**
     * Register tools with the ToolRegistry.
     * Called during plugin boot based on environment and plugin type.
     */
    public function registerTools(ToolRegistryInterface $registry): void;

    /**
     * Get prompt sections for the AI agent.
     * Returns an array of prompt lines describing the tools.
     *
     * @return array<string> Lines to add to the system prompt
     */
    public function getPromptSections(): array;
}
