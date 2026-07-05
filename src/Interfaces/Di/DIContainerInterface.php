<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Di;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Dependency Injection Container Interface.
 *
 * Provides service registration, retrieval, and auto-wiring capabilities.
 *
 * Extends the PSR-11 `ContainerInterface` so implementations are usable
 * anywhere a standard PSR-11 container is expected.
 */
interface DIContainerInterface extends ContainerInterface
{
    /**
     * Returns the underlying service container.
     */
    public function getContainer(): ContainerInterface;

    /**
     * Registers a service under the given identifier.
     *
     * Accepts either a class name (registered for later auto-wiring) or an
     * already-built instance (set directly on the container). An explicit
     * registration made here takes precedence over on-demand {@see resolve()}
     * autowiring for the same identifier. This is the method a
     * {@see \Milpa\app\Attributes\RegisterService} scanner calls after
     * instantiating an annotated class.
     */
    public function registerService(string $id, string|object $classOrInstance): void;

    /**
     * Compiles the underlying container, freezing its service definitions.
     */
    public function compileContainer(): void;

    /**
     * Get a service from the container.
     *
     * If the service is not registered but the class exists,
     * it will be auto-resolved and registered as a singleton.
     *
     * @param string $id Service identifier (usually FQCN)
     *
     * @return mixed Service instance
     *
     * @throws NotFoundExceptionInterface No entry was found for this identifier.
     * @throws \Psr\Container\ContainerExceptionInterface Auto-resolution of the entry failed.
     */
    public function get(string $id): mixed;

    /**
     * Checks whether an identifier is resolvable: either already registered,
     * or an existing class that can be auto-wired.
     */
    public function has(string $id): bool;

    /**
     * Resolve a class with auto-wiring.
     *
     * Instantiates the class resolving constructor dependencies from the container.
     * If $singleton is true, registers the instance for future use.
     *
     * @param string $className Fully qualified class name
     * @param bool   $singleton Register as singleton (default: true)
     *
     * @return mixed Class instance
     *
     * @throws ContainerExceptionInterface Error while resolving the entry.
     */
    public function resolve(string $className, bool $singleton = true): mixed;

    /**
     * Get a service or return null if not available.
     *
     * Unlike get(), this won't throw or auto-resolve.
     *
     * @param string $id Service identifier
     *
     * @return mixed|null Service instance or null
     */
    public function tryGet(string $id): mixed;
}
