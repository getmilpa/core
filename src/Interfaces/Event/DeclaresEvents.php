<?php

declare(strict_types=1);

/**
 * (c) Rodrigo Vicente - TeamX Agency
 *
 * Licensed under the Apache License, Version 2.0.
 */

namespace Milpa\Interfaces\Event;

/**
 * A package's own list of the events it dispatches, readable without constructing its emitters.
 *
 * An emitter declares to the dispatcher when it is built ({@see DeclaredEvents}), so a process that never
 * builds it never hears about its events: measured on cattle, a CLI process knew seven of the family's
 * twenty-four (greenhouse decisions/0228). The holder answers the same question one step earlier — it is a
 * static list, so a host can read it from a class name found in a package manifest and declare it to the
 * dispatcher on behalf of an emitter this process will never construct.
 *
 * The names must be the SAME symbols the dispatch sites use, never a retyped copy.
 */
interface DeclaresEvents
{
    /**
     * Every event this package dispatches, declared once.
     *
     * @return list<EventDeclaration>
     */
    public static function declarations(): array;
}
