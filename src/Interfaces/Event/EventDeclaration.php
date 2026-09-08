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
 * What an emitter says about one event it dispatches — so the house can answer «what events exist?»
 * from the emitters themselves, never from a scan of source (greenhouse decisions/0228).
 *
 * The name is the string handed to `dispatch()`; `dispatchedBy` the class that dispatches it; `when` one
 * sentence on the moment it fires; `subjectKey`/`subjectType` what the payload carries and under which key;
 * `mutable` whether a subscriber may change the subject in place; `interceptable` whether the payload carries
 * an `InterceptionSlot` a subscriber may stop or short-circuit. A declaration describes; it never dispatches.
 */
final readonly class EventDeclaration
{
    /**
     * @param class-string $dispatchedBy
     */
    public function __construct(
        public string $name,
        public string $dispatchedBy,
        public string $when,
        public string $subjectKey = 'event',
        public ?string $subjectType = null,
        public bool $mutable = false,
        public bool $interceptable = false,
    ) {
        if ($name === '' || preg_match('/^[a-z][a-z0-9_.:-]*$/', $name) !== 1) {
            throw new \InvalidArgumentException(sprintf('an event name is lowercase, dotted and starts with a letter; «%s» is not', $name));
        }
        if ($subjectKey === '') {
            throw new \InvalidArgumentException(sprintf('the declaration of «%s» names no payload key for its subject', $name));
        }
    }

    /**
     * The declaration as data, the shape a catalogue prints.
     *
     * @return array{name: string, dispatchedBy: string, when: string, subject: array{key: string, type: ?string, mutable: bool, interceptable: bool}}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'dispatchedBy' => $this->dispatchedBy,
            'when' => $this->when,
            'subject' => [
                'key' => $this->subjectKey,
                'type' => $this->subjectType,
                'mutable' => $this->mutable,
                'interceptable' => $this->interceptable,
            ],
        ];
    }
}
