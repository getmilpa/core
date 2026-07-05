<?php

declare(strict_types=1);

namespace Milpa\app\Exceptions;

/**
 * Thrown when reflection-based lookup expects a PHP attribute (e.g. #[Route],
 * #[Action], #[PluginMetadata]) on a class or method but none is present.
 */
class AttributeNotFoundException extends \Exception implements MilpaExceptionInterface
{
}
