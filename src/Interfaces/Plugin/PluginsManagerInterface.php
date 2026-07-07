<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Interfaces\Plugin;

/**
 * Discovers, registers, and boots the plugins that make up the running
 * application.
 */
interface PluginsManagerInterface
{
    /**
     * Registers a directory to be scanned for plugins.
     *
     * No-op if the path was already registered.
     */
    public function addPluginPath(string $path): void;

    /**
     * Discovers plugins under the registered paths, instantiates the
     * enabled ones through the container, and boots them.
     */
    public function loadPlugins(): void;

    /**
     * Get prompt sections from all plugins implementing ToolProviderInterface.
     *
     * @return array<string>
     */
    public function getToolProviderPromptSections(): array;

    /**
     * Returns all booted plugin instances, keyed by plugin name.
     *
     * @return array<string, PluginInterface>
     */
    public function getPlugins(): array;

    /**
     * Returns a single booted plugin instance by name, or null if no
     * such plugin has been booted.
     */
    public function getPlugin(string $name): ?PluginInterface;

    /**
     * Whether the given plugin name is currently enabled.
     */
    public function isEnabled(string $name): bool;
}
