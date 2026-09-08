<?php

declare(strict_types=1);

/**
 * (c) Rodrigo Vicente - TeamX Agency
 *
 * Licensed under the Apache License, Version 2.0.
 */

namespace Milpa\Tests\Interfaces\Event;

use Milpa\Interfaces\Event\DeclaresEvents;
use Milpa\Interfaces\Event\EventDeclaration;
use PHPUnit\Framework\TestCase;

final class DeclaresEventsTest extends TestCase
{
    /**
     * A holder answers without anyone constructing the emitter — that is the whole point of the contract.
     */
    public function testItIsReadableFromTheClassNameAloneWithNoInstance(): void
    {
        $class = ProbeEventsHolder::class;

        self::assertTrue(is_a($class, DeclaresEvents::class, true));

        $declarations = $class::declarations();

        self::assertCount(1, $declarations);
        self::assertInstanceOf(EventDeclaration::class, $declarations[0]);
        self::assertSame('probe.opened', $declarations[0]->name);
    }
}

final class ProbeEventsHolder implements DeclaresEvents
{
    public const string OPENED = 'probe.opened';

    /**
     * The probe's single event, built from the same constant its dispatch would use.
     *
     * @return list<EventDeclaration>
     */
    public static function declarations(): array
    {
        return [new EventDeclaration(
            name: self::OPENED,
            dispatchedBy: self::class,
            when: 'the probe opened',
        )];
    }
}
