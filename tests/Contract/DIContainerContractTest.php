<?php

declare(strict_types=1);

namespace Milpa\Tests\Contract;

use Milpa\app\Exceptions\CircularDependencyException;
use Milpa\app\Exceptions\ContainerResolutionException;
use Milpa\app\Exceptions\ServiceNotFoundException;
use Milpa\app\Interfaces\Di\DIContainerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Contract test suite for {@see DIContainerInterface}.
 *
 * This pins, at the PACKAGE level, the guarantees the interface's docblocks
 * promise but no test previously exercised: PSR-11 conformance, the typed
 * exception hierarchy, and the executable get/has/registerService contract.
 *
 * It intentionally does NOT re-test the concrete `DIContainer` implementation
 * (autowiring, singleton caching, cycle detection, etc. — see the monorepo's
 * `tests/Unit/DIContainerTest.php` and `tests/Unit/Providers/DIContainerTest.php`
 * for that ~70-test suite). Instead it demonstrates the contract is
 * satisfiable via a minimal in-test fake, so the documented behavior ships
 * as an executable spec alongside the interface itself.
 */
final class DIContainerContractTest extends TestCase
{
    public function testDIContainerInterfaceExtendsPsr11ContainerInterface(): void
    {
        $reflection = new \ReflectionClass(DIContainerInterface::class);

        $this->assertTrue(
            $reflection->implementsInterface(ContainerInterface::class),
            'DIContainerInterface must extend Psr\Container\ContainerInterface so any implementation is usable anywhere a PSR-11 container is expected.'
        );
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     * @param class-string             $psr11Interface
     */
    #[DataProvider('psr11ExceptionTypeProvider')]
    public function testDiExceptionsSatisfyTheirDocumentedPsr11Interface(string $exceptionClass, string $psr11Interface): void
    {
        $this->assertTrue(
            is_a($exceptionClass, $psr11Interface, true),
            sprintf('%s must be a %s per the documented DI exception contract.', $exceptionClass, $psr11Interface)
        );
    }

    /**
     * @return array<string, array{0: class-string<\Throwable>, 1: class-string}>
     */
    public static function psr11ExceptionTypeProvider(): array
    {
        return [
            'ServiceNotFoundException is a PSR-11 NotFoundExceptionInterface' => [
                ServiceNotFoundException::class,
                NotFoundExceptionInterface::class,
            ],
            'ContainerResolutionException is a PSR-11 ContainerExceptionInterface' => [
                ContainerResolutionException::class,
                ContainerExceptionInterface::class,
            ],
            'CircularDependencyException is a PSR-11 ContainerExceptionInterface (via its ContainerResolutionException parent)' => [
                CircularDependencyException::class,
                ContainerExceptionInterface::class,
            ],
        ];
    }

    public function testGetOnMissingIdThrowsServiceNotFoundAsAPsr11NotFoundException(): void
    {
        $container = $this->makeContainer();

        try {
            $container->get('nope.missing');
            $this->fail('Expected get() on a missing id to throw a Psr11 NotFoundExceptionInterface.');
        } catch (NotFoundExceptionInterface $exception) {
            $this->assertInstanceOf(ServiceNotFoundException::class, $exception);
        }
    }

    public function testRegisterServiceMakesHasTrueAndGetReturnTheSameInstance(): void
    {
        $container = $this->makeContainer();
        $service = new \stdClass();

        $container->registerService('x', $service);

        $this->assertTrue($container->has('x'));
        $this->assertSame($service, $container->get('x'));
    }

    #[DataProvider('unregisteredIdProvider')]
    public function testHasReturnsFalseForAnUnregisteredId(string $id): void
    {
        $container = $this->makeContainer();

        $this->assertFalse($container->has($id));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unregisteredIdProvider(): array
    {
        return [
            'plain id' => ['missing'],
            'fqcn-like id' => ['App\\Services\\Missing'],
            'empty string id' => [''],
        ];
    }

    /**
     * Builds a minimal, array-backed fake implementation of DIContainerInterface.
     *
     * Only get/has/registerService carry real behavior; the remaining
     * interface members get no-op/throwing stubs since this contract test
     * does not pin autowiring/resolve/tryGet/compilation semantics (those
     * belong to the concrete implementation's own exhaustive test suite).
     */
    private function makeContainer(): DIContainerInterface
    {
        return new class implements DIContainerInterface {
            /** @var array<string, string|object> */
            private array $services = [];

            public function getContainer(): ContainerInterface
            {
                return $this;
            }

            public function registerService(string $id, string|object $classOrInstance): void
            {
                $this->services[$id] = $classOrInstance;
            }

            public function compileContainer(): void
            {
                // No-op: this fake has no compilation step to freeze.
            }

            public function get(string $id): mixed
            {
                if (!array_key_exists($id, $this->services)) {
                    throw ServiceNotFoundException::forId($id);
                }

                return $this->services[$id];
            }

            public function has(string $id): bool
            {
                return array_key_exists($id, $this->services);
            }

            public function resolve(string $className, bool $singleton = true): mixed
            {
                throw new ContainerResolutionException('This fake does not support autowired resolution.');
            }

            public function tryGet(string $id): mixed
            {
                return $this->services[$id] ?? null;
            }
        };
    }
}
