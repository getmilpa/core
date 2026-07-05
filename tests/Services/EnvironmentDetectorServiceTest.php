<?php

declare(strict_types=1);

namespace Milpa\Tests\Services;

use PHPUnit\Framework\TestCase;
use Milpa\app\Services\EnvironmentDetectorService;

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
