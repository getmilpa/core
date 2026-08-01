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

namespace Milpa\Exceptions;

use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown when a service id is registered a second time with a DIFFERENT service.
 *
 * Registering the same service again is a no-op and never reaches here — measured on a real boot,
 * where the only two duplicate registrations in 96 are the same instance registered twice.
 *
 * ## Why this fails instead of overwriting
 *
 * The container used to keep the last registration and say nothing. That is not a permissive
 * multiplicity policy, it is the absence of one: `get()` returned whoever arrived last, and the
 * provider that lost never existed as far as any report was concerned.
 *
 * Nothing in this framework decides how many providers a capability admits
 * ({@see \Milpa\Services\CapabilityMatcher}, ADR-0037): multiplicity could legitimately mean an
 * error, a collection, an explicit replacement, a composition, or a later selection. This exception
 * does NOT decide which — it refuses only the one option that is dishonest under every reading:
 *
 * > When cardinality is undefined, keeping both facts or failing is honest. Silently substituting
 * > one for the other is not, because it destroys the evidence a decision would need.
 *
 * So this is a fail-closed placeholder, not a verdict that duplicates are invalid. When the
 * framework grows a real cardinality policy, this is the seam that policy replaces.
 *
 * ## What it carries
 *
 * The four facts a reader needs and a silent overwrite threw away: the id, the service already
 * registered, the one arriving, and where each came from.
 */
class ServiceRedefinitionException extends \RuntimeException implements ContainerExceptionInterface, MilpaExceptionInterface
{
    /**
     * Builds the refusal, naming all four facts.
     *
     * @param string $id       The service id registered twice.
     * @param string $existing A description of the service already registered.
     * @param string $incoming A description of the service arriving.
     * @param string $from     Where the first registration came from, or `?` if unknown.
     * @param string $to       Where the second registration came from, or `?` if unknown.
     */
    public static function of(string $id, string $existing, string $incoming, string $from, string $to): self
    {
        return new self(sprintf(
            'The service "%s" is already registered with %s (from %s), and %s (from %s) would replace it. '
            . 'Nothing here decides whether a second provider is allowed, so the container refuses to '
            . 'choose in silence: register one, or give the second a distinct id.',
            $id,
            $existing,
            $from,
            $incoming,
            $to,
        ));
    }
}
