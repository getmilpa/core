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

use Milpa\Exceptions\CircularDependencyException;
use Milpa\Exceptions\ContainerResolutionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * A cycle is only actionable if the message names the ring. Catching the type
 * tells a developer that something looped; the chain tells them where.
 */
#[CoversClass(CircularDependencyException::class)]
final class CircularDependencyExceptionTest extends TestCase
{
    public function testTheMessageDrawsTheWholeRingInOrder(): void
    {
        $exception = CircularDependencyException::forChain(['A', 'B', 'A']);

        self::assertSame('Circular dependency detected while resolving: A -> B -> A.', $exception->getMessage());
    }

    public function testItIsAContainerResolutionFailureSoBroadHandlersStillCatchIt(): void
    {
        self::assertInstanceOf(
            ContainerResolutionException::class,
            CircularDependencyException::forChain(['A', 'A']),
        );
    }
}
