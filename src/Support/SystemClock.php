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

namespace Milpa\Support;

use Milpa\Interfaces\Clock;

/** The wall clock: reads the real time. Non-deterministic by design — inject a {@see FixedClock} for replay. */
final class SystemClock implements Clock
{
    /** The real current instant, ISO-8601 — non-deterministic. */
    public function now(): string
    {
        return date('c');
    }
}
