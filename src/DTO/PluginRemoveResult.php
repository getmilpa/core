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
 * Result of a plugin removal operation.
 *
 * Note: `$migrationsReverted` is a reserved field — migration rollback on
 * removal is not yet wired, so it is currently always 0. Do not treat a 0 as
 * "no migrations existed"; treat it as "rollback not performed".
 */
final readonly class PluginRemoveResult
{
    public function __construct(
        public bool $success,
        public string $pluginName,
        public bool $dataKept = false,
        public int $migrationsReverted = 0,
        public ?string $error = null
    ) {
    }

    /**
     * Build a successful removal result.
     */
    public static function success(string $pluginName, bool $dataKept = false, int $migrationsReverted = 0): self
    {
        return new self(
            success: true,
            pluginName: $pluginName,
            dataKept: $dataKept,
            migrationsReverted: $migrationsReverted,
        );
    }

    /**
     * Build a failed removal result carrying the reason.
     */
    public static function failure(string $pluginName, string $error): self
    {
        return new self(
            success: false,
            pluginName: $pluginName,
            error: $error,
        );
    }
}
