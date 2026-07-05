<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

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
