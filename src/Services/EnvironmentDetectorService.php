<?php

declare(strict_types=1);

namespace Milpa\app\Services;

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
