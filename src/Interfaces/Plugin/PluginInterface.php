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

use Milpa\Interfaces\Di\DIContainerInterface;

/**
 * The lifecycle contract every Milpa plugin's main class must implement.
 *
 * Plugins are constructed with the framework's {@see DIContainerInterface}
 * and are taken through install/enable/disable/uninstall by the plugins
 * manager, with {@see boot()} run on every enabled plugin during bootstrap.
 */
interface PluginInterface
{
    public function __construct(DIContainerInterface $container);

    /**
     * Boots the plugin: registers services, routes, event listeners, etc.
     * Called on every enabled plugin during application bootstrap.
     *
     * @throws \Milpa\Exceptions\Plugin\PluginBootException If the plugin fails to boot.
     */
    public function boot(): void;

    /**
     * Runs one-time setup for the plugin (e.g. migrations, initial config).
     * Called when the plugin is installed.
     */
    public function install(): void;

    /**
     * Reverses what {@see install()} did, cleaning up plugin-owned state.
     * Called when the plugin is uninstalled.
     */
    public function uninstall(): void;

    /**
     * Activates the plugin so it participates in {@see boot()} on future runs.
     */
    public function enable(): void;

    /**
     * Deactivates the plugin without uninstalling it.
     */
    public function disable(): void;

}
