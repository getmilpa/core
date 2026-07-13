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
 * Thrown when the container fails to resolve or instantiate a service —
 * e.g. an unresolvable constructor parameter, an attempt to auto-resolve an
 * interface/abstract class, or a class that does not exist
 * (PSR-11 "container error" contract).
 */
class ContainerResolutionException extends \RuntimeException implements ContainerExceptionInterface, MilpaExceptionInterface
{
}
