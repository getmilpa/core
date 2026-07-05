<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions\Plugin;

use Milpa\app\Exceptions\MilpaExceptionInterface;

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
