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

/**
 * A single reversible schema/data migration.
 *
 * Implementations apply forward changes in {@see up()} and must be able to
 * undo them in {@see down()}.
 *
 * Discovery/ordering convention: migrations of this kind live as flat PHP
 * classes directly under the root `migrations/` directory (namespace
 * `Milpa\Migrations`), one class per file, with the class short-name equal
 * to the file's basename (e.g. `migrations/CreateUsersTable.php` →
 * `CreateUsersTable`). {@see \Milpa\Commands\MigrateCommand} discovers
 * them via `glob('migrations/*.php')` and applies `sort()` on the file list
 * — i.e. execution order is the ASCII-sort of the class short-name, not a
 * timestamp or semver embedded in the name. This is a *different* migration
 * kind than plugin-versioned migrations (`Version_X_Y_Z.php`, see
 * {@see \Milpa\Interfaces\PluginMigrationInterface}), which sort by
 * semver instead.
 */
interface MigrationInterface
{
    /**
     * Applies the migration.
     */
    public function up(): void; // Ejecuta la migración

    /**
     * Reverts the migration applied by {@see up()}.
     */
    public function down(): void; // Revierte la migración

    /**
     * Returns a human-readable description of what this migration does.
     */
    public function getDescription(): string; // Devuelve una descripción de la migración

    /**
     * Returns a stable, unique key identifying this migration for ordering
     * and dedup purposes across plugins.
     *
     * Per the discovery convention documented on this interface, this is the
     * migration class's short name (e.g. `CreateUsersTable`) — already
     * unique within the `migrations/` directory, since the runner keys
     * applied migrations by fully-qualified class name.
     */
    public function getId(): string;

    /**
     * Returns a version/ordering token for this migration.
     *
     * There is no timestamp or semver embedded in the naming convention for
     * this migration kind (unlike `Version_X_Y_Z.php` plugin migrations), so
     * this defaults to the same value as {@see getId()} — the class
     * short-name — which is exactly the token the runner's `sort()` over
     * `glob()` results already orders by.
     */
    public function getVersion(): string;
}
