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

namespace Milpa\Tests\Exceptions;

use Milpa\Exceptions\AttributeNotFoundException;
use PHPUnit\Framework\TestCase;
use Throwable;

final class AttributeNotFoundExceptionTest extends TestCase
{
    public function testIsThrowableAndExtendsException(): void
    {
        $exception = new AttributeNotFoundException();

        $this->assertInstanceOf(Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testCarriesMessageCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('root cause');
        $exception = new AttributeNotFoundException('Route attribute missing', 42, $previous);

        $this->assertSame('Route attribute missing', $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanActuallyBeThrownAndCaught(): void
    {
        try {
            throw new AttributeNotFoundException('#[Route] not found on method');
        } catch (AttributeNotFoundException $caught) {
            $this->assertSame('#[Route] not found on method', $caught->getMessage());
            return;
        }

        $this->fail('Expected AttributeNotFoundException to be thrown and caught.');
    }
}
