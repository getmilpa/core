<?php

declare(strict_types=1);

namespace Milpa\Tests\Exceptions;

use Milpa\app\Exceptions\AttributeNotFoundException;
use Milpa\app\Exceptions\AuthenticationException;
use Milpa\app\Exceptions\CircularDependencyException;
use Milpa\app\Exceptions\ContainerResolutionException;
use Milpa\app\Exceptions\InvalidAttributeValueException;
use Milpa\app\Exceptions\MilpaExceptionInterface;
use Milpa\app\Exceptions\ServiceNotFoundException;
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
