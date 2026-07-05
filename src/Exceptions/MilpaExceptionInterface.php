<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

/**
 * Marker interface implemented by every exception the Milpa framework throws.
 * Lets consumers catch all framework-originated errors without resorting to \Exception.
 */
interface MilpaExceptionInterface extends \Throwable
{
}
