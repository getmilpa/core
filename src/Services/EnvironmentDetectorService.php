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

namespace Milpa\Services;

/**
 * Detects the runtime environment the application is executing under
 * (command-line vs. web server).
 */
class EnvironmentDetectorService
{
    /**
     * Returns 'cli' when running under the PHP command-line SAPI,
     * otherwise 'web'.
     */
    public static function detect(): string
    {
        $phpSapiNameCallback = fn () => php_sapi_name();
        return ($phpSapiNameCallback)() === 'cli' ? 'cli' : 'web';
    }
}
