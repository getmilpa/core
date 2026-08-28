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

namespace Milpa\Interfaces;

/**
 * Time as an INPUT, not a hidden call. A component or runtime that stamps time reads it from an injected
 * {@see Clock} rather than calling `date()`/`time()` inside its logic, so the same inputs and the same clock
 * produce the same output — the determinism a replay needs (greenhouse decisions/0102/0103; the gap
 * ResearchLabs surfaced when its ActorRuntime used a wall clock). Implementations: a wall clock and a fixed,
 * reproducible one live in {@see \Milpa\Support\SystemClock} and {@see \Milpa\Support\FixedClock}.
 */
interface Clock
{
    /** The current instant as an ISO-8601 string (the shape a document/state stamps). */
    public function now(): string;

    /**
     * The same instant as a {@see \DateTimeImmutable} — the shape a domain runtime compares and formats
     * (the ResearchLabs ActorRuntime wanted this; a string was too lossy, so it forked its own port). A
     * consistent clock derives {@see now()} and {@see instant()} from the same source (greenhouse decisions/0131).
     */
    public function instant(): \DateTimeImmutable;
}
