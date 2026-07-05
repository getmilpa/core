<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

/**
 * Thrown when a request or operation fails to authenticate — e.g. missing,
 * invalid, or expired credentials/tokens presented by the caller.
 */
class AuthenticationException extends \Exception implements MilpaExceptionInterface
{
}
