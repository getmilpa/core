<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Milpa\Enums\ListenerPriority;

class EnumsTest extends TestCase
{
    public function testListenerPriorityValues(): void
    {
        $this->assertEquals(500, ListenerPriority::CRITICAL->value);
        $this->assertEquals(200, ListenerPriority::HIGHEST->value);
        $this->assertEquals(100, ListenerPriority::HIGH->value);
        $this->assertEquals(0, ListenerPriority::NORMAL->value);
        $this->assertEquals(-100, ListenerPriority::LOW->value);
        $this->assertEquals(-200, ListenerPriority::LOWEST->value);
    }

    public function testListenerPriorityOrdering(): void
    {
        // CRITICAL > HIGHEST > HIGH > NORMAL > LOW > LOWEST
        $this->assertGreaterThan(ListenerPriority::HIGHEST->value, ListenerPriority::CRITICAL->value);
        $this->assertGreaterThan(ListenerPriority::HIGH->value, ListenerPriority::HIGHEST->value);
        $this->assertGreaterThan(ListenerPriority::NORMAL->value, ListenerPriority::HIGH->value);
        $this->assertGreaterThan(ListenerPriority::LOW->value, ListenerPriority::NORMAL->value);
        $this->assertGreaterThan(ListenerPriority::LOWEST->value, ListenerPriority::LOW->value);
    }

    public function testListenerPriorityCases(): void
    {
        $cases = ListenerPriority::cases();

        $this->assertCount(6, $cases);
    }

    public function testListenerPriorityCanBeUsedInComparison(): void
    {
        $priority1 = ListenerPriority::HIGH;
        $priority2 = ListenerPriority::LOW;

        $this->assertTrue($priority1->value > $priority2->value);
    }

    public function testListenerPriorityFromValue(): void
    {
        $priority = ListenerPriority::from(100);

        $this->assertEquals(ListenerPriority::HIGH, $priority);
    }
}
