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

/** A reproducible clock: always returns the instant it was constructed with. The determinism a replay needs. */
final class FixedClock implements Clock
{
    public function __construct(private readonly string $instant)
    {
    }

    /** The fixed instant this clock was constructed with. */
    public function now(): string
    {
        return $this->instant;
    }
}
