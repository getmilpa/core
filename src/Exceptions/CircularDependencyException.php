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

/**
 * Thrown when auto-wiring detects a circular dependency chain — e.g. service
 * A requires service B which (directly or transitively) requires service A
 * again.
 */
final class CircularDependencyException extends ContainerResolutionException
{
    /**
     * Builds the exception for a detected circular dependency chain.
     *
     * @param list<string> $chain Ordered class/service names forming the cycle,
     *                            e.g. ['A', 'B', 'A']
     */
    public static function forChain(array $chain): self
    {
        return new self(sprintf(
            'Circular dependency detected while resolving: %s.',
            implode(' -> ', $chain)
        ));
    }
}
