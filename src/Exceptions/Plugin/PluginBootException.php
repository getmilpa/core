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

namespace Milpa\Exceptions\Plugin;

use Milpa\Exceptions\MilpaExceptionInterface;

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
