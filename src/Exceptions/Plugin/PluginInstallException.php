<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions\Plugin;

use Milpa\app\Exceptions\MilpaExceptionInterface;

/**
 * Thrown when installing or updating a plugin from a remote source
 * (e.g. GitHub) fails — download failure, manifest error, or an
 * unresolvable release.
 */
final class PluginInstallException extends \RuntimeException implements MilpaExceptionInterface
{
    /**
     * Builds the exception for a plugin that failed to install from a source.
     *
     * @param string $source Remote source the plugin was installed from
     *                        (e.g. "owner/repo", "owner/repo:^2.0", or a GitHub URL)
     * @param string $reason Human-readable reason for the failure
     */
    public static function forSource(string $source, string $reason): self
    {
        return new self(sprintf('Failed to install plugin from "%s": %s', $source, $reason));
    }
}
