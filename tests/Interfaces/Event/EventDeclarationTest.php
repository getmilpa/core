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

namespace Milpa\Tests\Interfaces\Event;

use Milpa\Interfaces\Event\DeclaredEvents;
use Milpa\Interfaces\Event\EventDeclaration;
use PHPUnit\Framework\TestCase;

/**
 * A declaration describes one event as data, refuses a name that is not an event name, and the contract a
 * dispatcher may implement is what a catalogue reads (greenhouse decisions/0228).
 */
final class EventDeclarationTest extends TestCase
{
    public function testItDescribesOneEventAsData(): void
    {
        $d = new EventDeclaration('component.rendering', 'Milpa\\Live\\Events\\LiveEventEmitter', 'before a component renders', 'slot', 'Milpa\\Events\\InterceptionSlot', mutable: false, interceptable: true);

        self::assertSame([
            'name' => 'component.rendering',
            'dispatchedBy' => 'Milpa\\Live\\Events\\LiveEventEmitter',
            'when' => 'before a component renders',
            'subject' => ['key' => 'slot', 'type' => 'Milpa\\Events\\InterceptionSlot', 'mutable' => false, 'interceptable' => true],
        ], $d->toArray());

        $plain = new EventDeclaration('kernel.booted', 'Milpa\\Runtime\\Kernel', 'after every plugin booted');
        self::assertSame('event', $plain->subjectKey, 'the family\'s default payload key');
        self::assertNull($plain->subjectType);
        self::assertFalse($plain->mutable);
        self::assertFalse($plain->interceptable);
    }

    public function testItRefusesWhatIsNotAnEventName(): void
    {
        foreach (['', 'Kernel.Booted', ' kernel.booted', '1st.thing', 'has space'] as $bad) {
            try {
                new EventDeclaration($bad, 'X', 'y');
                self::fail('«' . $bad . '» must be refused');
            } catch (\InvalidArgumentException $e) {
                self::assertStringContainsString('event name', $e->getMessage());
            }
        }
        try {
            new EventDeclaration('ok.name', 'X', 'y', '');
            self::fail('an empty subject key must be refused');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('payload key', $e->getMessage());
        }
    }

    public function testTheContractIsWhatACatalogueReads(): void
    {
        $dispatcher = new class () implements DeclaredEvents {
            /** @var list<EventDeclaration> */
            private array $declared = [];

            /** @var list<string> */
            private array $dispatched = [];

            public function declare(EventDeclaration ...$events): void
            {
                foreach ($events as $event) {
                    foreach ($this->declared as $known) {
                        if ($known->name === $event->name) {
                            continue 2;
                        }
                    }
                    $this->declared[] = $event;
                }
            }

            public function declared(): array
            {
                return $this->declared;
            }

            public function dispatched(): array
            {
                return $this->dispatched;
            }

            public function fire(string $name): void
            {
                if (!\in_array($name, $this->dispatched, true)) {
                    $this->dispatched[] = $name;
                }
            }
        };
        $dispatcher->declare(new EventDeclaration('a.one', 'A', 'first'), new EventDeclaration('a.one', 'B', 'a second opinion'), new EventDeclaration('a.two', 'A', 'second'));
        $dispatcher->fire('a.two');
        $dispatcher->fire('b.undeclared');
        $dispatcher->fire('a.two');

        self::assertSame(['a.one', 'a.two'], array_map(static fn (EventDeclaration $d): string => $d->name, $dispatcher->declared()));
        self::assertSame('A', $dispatcher->declared()[0]->dispatchedBy, 'the first declaration of a name is kept');
        self::assertSame(['a.two', 'b.undeclared'], $dispatcher->dispatched(), 'what was dispatched, first occurrence first — declared or not');
    }
}
