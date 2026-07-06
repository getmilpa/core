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

namespace Milpa\Exceptions\Plugin;

use Milpa\Exceptions\MilpaExceptionInterface;

/**
 * Thrown when a plugin declares a dependency (another plugin, a Composer
 * package, etc.) that is not available in the current environment.
 */
final class PluginDependencyException extends \RuntimeException implements MilpaExceptionInterface
{
    /**
     * Builds the exception for a plugin dependency that could not be satisfied.
     *
     * @param string $plugin     Name of the plugin declaring the dependency
     * @param string $dependency Name of the unmet dependency
     */
    public static function unmet(string $plugin, string $dependency): self
    {
        return new self(sprintf(
            'Plugin "%s" requires "%s", which is not available.',
            $plugin,
            $dependency
        ));
    }
}
