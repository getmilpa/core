<?php

declare(strict_types=1);

namespace Milpa\Tests\Support;

use Milpa\app\Support\UuidGenerator;
use PHPUnit\Framework\TestCase;

/**
 * UuidGenerator is a trait meant to be composed into entities via
 * `self::generateUuid()`. We exercise it through a minimal host class
 * that exposes the protected method publicly for the test.
 */
final class UuidGeneratorTest extends TestCase
{
    private function generate(): string
    {
        $host = new class {
            use UuidGenerator;

            public function make(): string
            {
                return self::generateUuid();
            }
        };

        return $host->make();
    }

    public function testGeneratesWellFormedRfc4122V4Uuid(): void
    {
        $uuid = $this->generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
        $this->assertSame(36, strlen($uuid));
    }

    public function testGeneratesDistinctValuesAcrossSuccessiveCalls(): void
    {
        $first = $this->generate();
        $second = $this->generate();

        $this->assertNotSame($first, $second);
    }

    public function testGeneratesManyUniqueValues(): void
    {
        $seen = [];
        for ($i = 0; $i < 50; $i++) {
            $seen[$this->generate()] = true;
        }

        $this->assertCount(50, $seen);
    }
}
