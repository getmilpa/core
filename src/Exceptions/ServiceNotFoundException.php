<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Exceptions;

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
