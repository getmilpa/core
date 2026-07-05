<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Plugin;

use Milpa\app\DTO\DependencyResolution;
use Milpa\app\DTO\PluginInstallResult;
use Milpa\app\DTO\PluginRemoveResult;

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
     * @throws \Milpa\app\Exceptions\Plugin\PluginInstallException If the plugin fails to install.
     * @throws \Milpa\app\Exceptions\Plugin\PluginDependencyException If a required dependency is unmet.
     */
    public function require(string $source): PluginInstallResult;

    /**
     * Update an installed plugin to the latest compatible version.
     *
     * @throws \Milpa\app\Exceptions\Plugin\PluginInstallException If the plugin fails to update.
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
