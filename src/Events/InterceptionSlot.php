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

namespace Milpa\Events;

/**
 * The family's single canonical interception primitive (KEYSTONE, core 0.5).
 *
 * Events dispatched through {@see \Milpa\Interfaces\Event\MilpaEventDispatcherInterface}
 * stay ALWAYS readonly — pure notification data, never the vector for veto or
 * short-circuit. This class is the one and only escape hatch: a single mutable
 * object, dispatched ALONGSIDE the readonly event (never in place of it), that a
 * handler mutates to veto or short-circuit and that the emitter reads back after
 * `dispatch()` returns to decide what to do next. Wire it into the payload as
 * `['event' => $readonlyEvent, 'slot' => $slot]` — see the emitter<->slot contract
 * documented on {@see \Milpa\Interfaces\Event\MilpaEventDispatcherInterface::dispatch()}.
 *
 * Atomicity (hallazgo (b) of the adversarial review that hardened this design):
 * {@see shortCircuit()} is the ONLY way this class ever sets a result. It sets the
 * result AND marks the slot stopped in the same call, so {@see hasResult()} and
 * {@see isStopped()} can NEVER disagree — there is no intermediate state where a
 * result exists but the slot isn't stopped, or the slot is stopped but a result was
 * only half-set. There is deliberately no bare `setResult()`. {@see stop()} alone is
 * pure veto: it stops propagation without ever touching the result.
 */
final class InterceptionSlot
{
    private bool $stopped = false;

    private bool $hasResult = false;

    private mixed $result = null;

    /**
     * Veto: stop propagation without supplying a replacement result.
     *
     * Use for pure veto semantics — e.g. `plugin.booting`: a feature-flag plugin
     * skips another plugin's boot without producing a "result" for it. After this
     * call {@see isStopped()} is true and {@see hasResult()} stays false.
     */
    public function stop(): void
    {
        $this->stopped = true;
    }

    /**
     * True once {@see stop()} or {@see shortCircuit()} has been called on this slot.
     */
    public function isStopped(): bool
    {
        return $this->stopped;
    }

    /**
     * Short-circuit: atomically supply a replacement result AND stop propagation.
     *
     * The only way to set a result on this slot — by design there is no bare
     * `setResult()` that could leave {@see hasResult()} true while {@see isStopped()}
     * is false, or vice versa. Use for cache/veto-with-replacement semantics: a
     * handler supplies `$result` in place of the work the emitter would otherwise do
     * (e.g. a cache plugin short-circuits `tool.executing` with a cached `ToolResult`).
     *
     * @param mixed $result the replacement result the emitter reads back via {@see getResult()}
     */
    public function shortCircuit(mixed $result): void
    {
        $this->result = $result;
        $this->hasResult = true;
        $this->stopped = true;
    }

    /**
     * True once {@see shortCircuit()} has supplied a replacement result on this slot.
     */
    public function hasResult(): bool
    {
        return $this->hasResult;
    }

    /**
     * The replacement result supplied via {@see shortCircuit()}, or null if none was set.
     *
     * Callers MUST check {@see hasResult()} first — a null return is ambiguous between
     * "no result was set" and "the handler short-circuited with a literal null result".
     */
    public function getResult(): mixed
    {
        return $this->result;
    }
}
