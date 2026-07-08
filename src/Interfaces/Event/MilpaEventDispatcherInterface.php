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

namespace Milpa\Interfaces\Event;

use Milpa\Events\InterceptionSlot;

/**
 * Event Dispatcher Interface
 *
 * Enables plugins to emit and subscribe to events in a loosely-coupled manner.
 */
interface MilpaEventDispatcherInterface
{
    /**
     * Dispatch an event to all registered subscribers (exact-match plus any
     * matching wildcard subscriptions), in descending priority order.
     *
     * Listener error isolation: if a handler throws, the dispatcher logs the
     * error and continues to the remaining handlers — one failing listener MUST
     * NOT abort the dispatch or prevent later listeners from running.
     * (An implementation that needs fail-fast semantics must document the deviation.)
     *
     * `$async` semantics: `true` requests deferred execution via a queue. An
     * implementation with a queue wired MUST honor the request (dispatch via
     * the queue, not inline). An implementation with no queue configured MAY
     * degrade to synchronous dispatch as a conformant fallback — that is not
     * a deviation needing a special flag — but MUST document that it does so,
     * the same way the error-isolation paragraph above documents fail-fast as
     * a deviation. An implementation MUST NOT silently drop the event because
     * no queue is wired.
     *
     * **The emitter<->slot interception contract (KEYSTONE, core 0.5).** Events
     * stay ALWAYS readonly; the one escape hatch for veto/short-circuit is
     * {@see InterceptionSlot}, a single mutable object dispatched ALONGSIDE the
     * readonly event, never in place of it. This is a binding MUST/MAY contract
     * for every conformant implementation:
     * - The emitter (the class calling `dispatch()`) that wants interception MUST
     *   pass a payload shaped `['event' => $readonlyEvent, 'slot' => new InterceptionSlot()]`
     *   and, after `dispatch()` returns, MUST read the slot back — `$slot->hasResult()`
     *   for a short-circuit result, `$slot->isStopped()` for a pure veto — to decide
     *   what to do next. An emitter that dispatches a slot and never reads it back has
     *   built a no-op interception point; the dispatcher does not act on the slot for you.
     * - The dispatcher MUST, after EACH handler's error-isolating try/catch — UNCONDITIONALLY,
     *   whether or not that handler threw — check whether `payload['slot']` is an
     *   {@see InterceptionSlot} and, if `$slot->isStopped()`, stop invoking further
     *   handlers for this dispatch. A handler that calls `$slot->stop()` (or
     *   `shortCircuit()`) and THEN throws still stops propagation — the stop check
     *   happens after the catch, not only on the clean-return path.
     * - A payload with no `'slot'` key (or a `'slot'` that is not an {@see InterceptionSlot})
     *   MUST behave byte-identically to a dispatcher with no interception support at
     *   all — this is strictly additive, zero-risk for every existing emitter.
     * - `$async = true` combined with an {@see InterceptionSlot} present in the payload
     *   MUST throw. Interception is inherently synchronous: a slot handed to a queue
     *   is never re-read by the emitter, so the short-circuit/veto would be silently
     *   swallowed — a correctness trap, not a style preference. Implementations MUST
     *   reject this combination rather than degrade it.
     *
     * @param string               $eventName Event name (e.g., 'user.registered', 'order.shipped')
     * @param array<string, mixed> $payload   Data to pass to handlers — MAY include `'event'` (the readonly
     *                                        event object, family convention since core 0.3) and/or `'slot'`
     *                                        (an {@see InterceptionSlot}, per the contract above)
     * @param bool                 $async     If true, requests deferred (queued) execution — see the `$async` semantics paragraph above for the no-queue fallback contract, and the emitter<->slot contract above for why `$async = true` with a slot MUST throw
     *
     * @return void
     */
    public function dispatch(string $eventName, array $payload = [], bool $async = false): void;

    /**
     * Subscribe a handler to an event name or a wildcard pattern.
     *
     * Wildcard grammar: event names are dot-separated segments; `*` matches
     * exactly ONE segment (it does not span a `.`). Matching is case-sensitive
     * and anchored (the whole name must match). Examples: `user.*` matches
     * `user.created`/`user.deleted` but NOT `user.profile.updated`; `*.created`
     * matches `user.created`/`order.created`; `*` alone matches only single-segment
     * names (e.g. `boot`), not dotted ones.
     *
     * @param string   $eventName Event name or wildcard pattern (e.g. 'user.created', 'user.*')
     * @param callable $handler   Handler function: fn(string $event, array $payload): void
     * @param int      $priority  Higher priority handlers execute first (default: 0)
     *
     * @return void
     */
    public function subscribe(string $eventName, callable $handler, int $priority = 0): void;

    /**
     * Get all subscribers for an event (including wildcard matches).
     *
     * @param string $eventName Event name
     *
     * @return array<int, callable> Array of handlers sorted by priority
     */
    public function getSubscribers(string $eventName): array;

    /**
     * Check if an event has any subscribers.
     *
     * @param string $eventName Event name
     *
     * @return bool
     */
    public function hasSubscribers(string $eventName): bool;
}
