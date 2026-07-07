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

namespace Milpa\DTO;

/**
 * Result of a plugin installation or update operation.
 */
final readonly class PluginInstallResult
{
    public function __construct(
        public bool $success,
        public string $pluginName,
        public string $version,
        public string $source,
        /** @var array<string> Composer packages that were installed */
        public array $composerPackagesInstalled = [],
        public int $migrationsExecuted = 0,
        public ?string $error = null
    ) {
    }
}
