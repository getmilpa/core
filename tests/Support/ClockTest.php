<?php

declare(strict_types=1);

namespace Milpa\Tests\Support;

use Milpa\Interfaces\Clock;
use Milpa\Support\FixedClock;
use Milpa\Support\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * Time as an input (greenhouse decisions/0103): a FixedClock is reproducible, a SystemClock reads the wall.
 */
final class ClockTest extends TestCase
{
    public function testAFixedClockIsReproducible(): void
    {
        $clock = new FixedClock('2026-08-27T00:00:00+00:00');
        self::assertInstanceOf(Clock::class, $clock);
        self::assertSame('2026-08-27T00:00:00+00:00', $clock->now());
        self::assertSame($clock->now(), $clock->now(), 'the fixed clock returns the same instant every call');
    }

    public function testTheSystemClockReadsAnIsoInstant(): void
    {
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', (new SystemClock())->now());
    }
}
