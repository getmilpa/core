<?php

declare(strict_types=1);

namespace Milpa\Tests\Exceptions;

use Milpa\app\Exceptions\InvalidAttributeValueException;
use PHPUnit\Framework\TestCase;
use Throwable;

final class InvalidAttributeValueExceptionTest extends TestCase
{
    public function testIsThrowableAndExtendsException(): void
    {
        $exception = new InvalidAttributeValueException();

        $this->assertInstanceOf(Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testCarriesMessageCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('validation failed');
        $exception = new InvalidAttributeValueException('Invalid method for #[Route]', 7, $previous);

        $this->assertSame('Invalid method for #[Route]', $exception->getMessage());
        $this->assertSame(7, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanActuallyBeThrownAndCaught(): void
    {
        try {
            throw new InvalidAttributeValueException('priority must be an integer');
        } catch (InvalidAttributeValueException $caught) {
            $this->assertSame('priority must be an integer', $caught->getMessage());
            return;
        }

        $this->fail('Expected InvalidAttributeValueException to be thrown and caught.');
    }
}
