<?php

declare(strict_types=1);

namespace Milpa\app\DTO;

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
