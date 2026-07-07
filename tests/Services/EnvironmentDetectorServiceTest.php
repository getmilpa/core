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

namespace Milpa\Tests\Services;

use PHPUnit\Framework\TestCase;
use Milpa\Services\EnvironmentDetectorService;

class EnvironmentDetectorServiceTest extends TestCase
{
    public function testDetectReturnsCliForCommandLineInterface(): void
    {
        // The static detect() method uses php_sapi_name() internally
        // In CLI tests, this will always return 'cli'
        $this->assertEquals('cli', EnvironmentDetectorService::detect());
    }

    public function testDetectReturnsExpectedFormat(): void
    {
        // The detect method returns either 'cli' or 'web'
        $result = EnvironmentDetectorService::detect();
        $this->assertContains($result, ['cli', 'web']);
    }
}
