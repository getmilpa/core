<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Exceptions;

use Milpa\Exceptions\AttributeNotFoundException;
use Milpa\Exceptions\AuthenticationException;
use Milpa\Exceptions\CircularDependencyException;
use Milpa\Exceptions\ContainerResolutionException;
use Milpa\Exceptions\InvalidAttributeValueException;
use Milpa\Exceptions\MilpaExceptionInterface;
use Milpa\Exceptions\ServiceNotFoundException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

final class MilpaExceptionMarkerTest extends TestCase
{
    /**
     * @return list<class-string>
     */
    public static function frameworkExceptionProvider(): array
    {
        return [
            [AttributeNotFoundException::class],
            [AuthenticationException::class],
            [InvalidAttributeValueException::class],
            [ContainerResolutionException::class],
            [ServiceNotFoundException::class],
            [CircularDependencyException::class],
        ];
    }

    #[DataProvider('frameworkExceptionProvider')]
    public function testEachFrameworkExceptionImplementsTheMarker(string $fqcn): void
    {
        $this->assertTrue(
            is_a($fqcn, MilpaExceptionInterface::class, true),
            sprintf('%s must implement MilpaExceptionInterface.', $fqcn)
        );
    }

    public function testServiceNotFoundExceptionIsCatchableAsPsr11NotFound(): void
    {
        try {
            throw ServiceNotFoundException::forId('x');
        } catch (NotFoundExceptionInterface $caught) {
            $this->assertInstanceOf(ServiceNotFoundException::class, $caught);
            return;
        }

        $this->fail('Expected ServiceNotFoundException to be catchable as NotFoundExceptionInterface.');
    }

    public function testServiceNotFoundExceptionIsCatchableAsMilpaExceptionInterface(): void
    {
        try {
            throw ServiceNotFoundException::forId('x');
        } catch (MilpaExceptionInterface $caught) {
            $this->assertInstanceOf(ServiceNotFoundException::class, $caught);
            return;
        }

        $this->fail('Expected ServiceNotFoundException to be catchable as MilpaExceptionInterface.');
    }
}
