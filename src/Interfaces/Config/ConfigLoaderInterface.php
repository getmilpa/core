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

namespace Milpa\Interfaces\Config;

/**
 * Loads application configuration from an arbitrary source.
 *
 * Implementations are agnostic to the origin of the configuration — a file
 * path, an environment variable key, a remote URI, etc. — and resolve it
 * on demand via `load()` instead of being coupled to it at construction time.
 */
interface ConfigLoaderInterface
{
    /**
     * Loads and returns the configuration from the given source (a file
     * path, env key, remote URI, or any other source identifier the
     * implementation understands).
     *
     * @return array<string, mixed>
     */
    public function load(string $source): array;

}
