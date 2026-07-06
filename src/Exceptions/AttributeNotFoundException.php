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

/**
 * Thrown when reflection-based lookup expects a PHP attribute (e.g. #[Route],
 * #[Action], #[PluginMetadata]) on a class or method but none is present.
 */
class AttributeNotFoundException extends \Exception implements MilpaExceptionInterface
{
}
