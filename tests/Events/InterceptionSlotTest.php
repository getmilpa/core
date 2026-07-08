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

namespace Milpa\Tests\Events;

use PHPUnit\Framework\TestCase;
use Milpa\Events\InterceptionSlot;

/**
 * {@see InterceptionSlot} — the family's single canonical interception primitive
 * (KEYSTONE, core 0.5). Pins the atomicity guarantee that motivated the design
 * (hallazgo (b) of the adversarial review): `shortCircuit()` is the only way to
 * set a result, so `hasResult()` and `isStopped()` can never disagree.
 */
final class InterceptionSlotTest extends TestCase
{
    public function testFreshSlotIsNotStoppedAndHasNoResult(): void
    {
        $slot = new InterceptionSlot();

        $this->assertFalse($slot->isStopped());
        $this->assertFalse($slot->hasResult());
        $this->assertNull($slot->getResult());
    }

    public function testStopAloneStopsButLeavesHasResultFalse(): void
    {
        $slot = new InterceptionSlot();

        $slot->stop();

        $this->assertTrue($slot->isStopped());
        $this->assertFalse($slot->hasResult(), 'stop() is pure veto — it must never set a result');
        $this->assertNull($slot->getResult());
    }

    public function testShortCircuitAtomicallySetsResultAndStops(): void
    {
        $slot = new InterceptionSlot();

        $slot->shortCircuit('cached-value');

        $this->assertTrue($slot->isStopped(), 'shortCircuit() must also stop the slot');
        $this->assertTrue($slot->hasResult());
        $this->assertSame('cached-value', $slot->getResult());
    }

    public function testShortCircuitAcceptsNullAsALiteralResult(): void
    {
        $slot = new InterceptionSlot();

        $slot->shortCircuit(null);

        $this->assertTrue($slot->isStopped());
        $this->assertTrue($slot->hasResult(), 'a literal null result must still be distinguishable from "no result"');
        $this->assertNull($slot->getResult());
    }

    public function testShortCircuitAcceptsFalsyResults(): void
    {
        $slot = new InterceptionSlot();

        $slot->shortCircuit(0);

        $this->assertTrue($slot->hasResult());
        $this->assertSame(0, $slot->getResult());
    }

    public function testStopAfterShortCircuitIsANoOpThatKeepsTheResult(): void
    {
        $slot = new InterceptionSlot();

        $slot->shortCircuit('cached-value');
        $slot->stop();

        $this->assertTrue($slot->isStopped());
        $this->assertTrue($slot->hasResult());
        $this->assertSame('cached-value', $slot->getResult());
    }

    public function testShortCircuitCanOverwriteAPreviousShortCircuit(): void
    {
        $slot = new InterceptionSlot();

        $slot->shortCircuit('first');
        $slot->shortCircuit('second');

        $this->assertTrue($slot->hasResult());
        $this->assertSame('second', $slot->getResult());
    }

    public function testHasResultAndIsStoppedNeverDisagree(): void
    {
        // There is no bare setResult() — hasResult() true implies isStopped() true,
        // for every code path that reaches a result. Exercise both mutators and
        // assert the invariant holds after each.
        $slot = new InterceptionSlot();
        $this->assertSame($slot->hasResult(), false);
        $this->assertSame($slot->isStopped(), false);

        $slot->stop();
        $this->assertFalse($slot->hasResult() && !$slot->isStopped());

        $slot2 = new InterceptionSlot();
        $slot2->shortCircuit('x');
        $this->assertFalse($slot2->hasResult() && !$slot2->isStopped());
        $this->assertFalse(!$slot2->hasResult() && $slot2->isStopped() && $slot2->getResult() !== null);
    }
}
