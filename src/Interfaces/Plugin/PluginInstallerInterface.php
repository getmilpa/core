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

namespace Milpa\Interfaces\Plugin;

use Milpa\DTO\DependencyResolution;
use Milpa\DTO\PluginInstallResult;
use Milpa\DTO\PluginRemoveResult;

/**
 * Installs, updates, and removes plugins from remote sources (GitHub).
 */
interface PluginInstallerInterface
{
    /**
     * Install a plugin from a remote source (GitHub).
     *
     * @param string $source "owner/repo", "owner/repo:^2.0", or full GitHub URL
     *
     * @throws \Milpa\Exceptions\Plugin\PluginInstallException    If the plugin fails to install.
     * @throws \Milpa\Exceptions\Plugin\PluginDependencyException If a required dependency is unmet.
     */
    public function require(string $source): PluginInstallResult;

    /**
     * Update an installed plugin to the latest compatible version.
     *
     * @throws \Milpa\Exceptions\Plugin\PluginInstallException If the plugin fails to update.
     */
    public function update(string $pluginName, ?string $targetVersion = null): PluginInstallResult;

    /**
     * Resolve plugin and Composer dependencies for a remote source without installing it.
     *
     * Downloads the candidate release, reads its manifest, and checks it against
     * currently-installed plugins — the same resolution `require()` performs
     * internally before it commits any files or DB state.
     *
     * @param string $source "owner/repo", "owner/repo:^2.0", or full GitHub URL
     */
    public function resolve(string $source): DependencyResolution;

    /**
     * Remove a remotely-installed plugin.
     */
    public function remove(string $pluginName, bool $keepData = false): PluginRemoveResult;
}
