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

namespace Milpa\Interfaces\Tooling;


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
