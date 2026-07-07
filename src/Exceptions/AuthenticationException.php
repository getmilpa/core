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

namespace Milpa\Exceptions;

/**
 * Thrown when a request or operation fails to authenticate — e.g. missing,
 * invalid, or expired credentials/tokens presented by the caller.
 */
class AuthenticationException extends \Exception implements MilpaExceptionInterface
{
}
