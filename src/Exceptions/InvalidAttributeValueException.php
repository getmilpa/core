<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

/**
 * Thrown when a PHP attribute (e.g. #[Route], #[Action], #[PluginMetadata])
 * is present but one of its argument values fails validation.
 */
class InvalidAttributeValueException extends \Exception implements MilpaExceptionInterface
{
}
