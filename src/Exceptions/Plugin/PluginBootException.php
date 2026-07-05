<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions\Plugin;

use Milpa\app\Exceptions\MilpaExceptionInterface;

/**
 * Thrown when a plugin's boot() method fails during application bootstrap
 * (e.g. a missing service, a misconfigured dependency, or an unrecoverable
 * setup error raised by the plugin itself).
 */
final class PluginBootException extends \RuntimeException implements MilpaExceptionInterface
{
    /**
     * Builds the exception for a plugin that failed to boot.
     *
     * @param string $name   Name of the plugin that failed to boot
     * @param string $reason Human-readable reason for the failure
     */
    public static function forPlugin(string $name, string $reason): self
    {
        return new self(sprintf('Plugin "%s" failed to boot: %s', $name, $reason));
    }
}
