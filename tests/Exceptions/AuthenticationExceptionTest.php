<?php

declare(strict_types=1);

namespace Milpa\Tests\Exceptions;

use Milpa\app\Exceptions\AuthenticationException;
use PHPUnit\Framework\TestCase;
use Throwable;

final class AuthenticationExceptionTest extends TestCase
{
    public function testIsThrowableAndExtendsException(): void
    {
        $exception = new AuthenticationException();

        $this->assertInstanceOf(Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testCarriesMessageCodeAndPrevious(): void
    {
        $previous = new \RuntimeException('token expired');
        $exception = new AuthenticationException('Invalid credentials', 401, $previous);

        $this->assertSame('Invalid credentials', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanActuallyBeThrownAndCaught(): void
    {
        try {
            throw new AuthenticationException('Missing bearer token');
        } catch (AuthenticationException $caught) {
            $this->assertSame('Missing bearer token', $caught->getMessage());
            return;
        }

        $this->fail('Expected AuthenticationException to be thrown and caught.');
    }
}
