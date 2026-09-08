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

namespace Milpa\Interfaces\Event;

/**
 * A dispatcher that knows which events were DECLARED to it and which names it has DISPATCHED.
 *
 * The one place every dispatch passes through is the dispatcher, so it is the one authority on «what events
 * exist» (greenhouse decisions/0228): emitters declare their events where they hold the dispatcher, the
 * dispatcher remembers every name it actually dispatched in this process, and a catalogue reads both — what
 * was declared, and what was dispatched without a declaration, as debt with a name. This is a separate
 * contract, not a widening of {@see MilpaEventDispatcherInterface}: a dispatcher that does not implement it
 * is asked nothing, and whoever asks says so instead of answering «no events».
 */
interface DeclaredEvents
{
    /**
     * Registers what an emitter dispatches. Declaring the same name twice keeps the first declaration.
     */
    public function declare(EventDeclaration ...$events): void;

    /**
     * Every declaration made to this dispatcher, in declaration order.
     *
     * @return list<EventDeclaration>
     */
    public function declared(): array;

    /**
     * Every event name this dispatcher has dispatched in this process, first occurrence first.
     *
     * @return list<string>
     */
    public function dispatched(): array;
}
