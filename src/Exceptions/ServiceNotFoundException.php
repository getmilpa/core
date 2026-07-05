<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when a requested service identifier is not registered in the
 * container and cannot be auto-resolved (PSR-11 "not found" contract).
 */
final class ServiceNotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface, MilpaExceptionInterface
{
    /**
     * Builds the exception for a missing service identifier.
     *
     * @param string $id Service identifier that was requested
     */
    public static function forId(string $id): self
    {
        return new self(sprintf('Service "%s" is not registered in the container.', $id));
    }
}
