<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

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
