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

namespace Milpa\Interfaces\Di;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Dependency Injection Container Interface.
 *
 * Provides service registration and retrieval, plus an explicit auto-wiring
 * seam ({@see resolve()}) that implementations opt into.
 *
 * Extends the PSR-11 `ContainerInterface` so implementations are usable
 * anywhere a standard PSR-11 container is expected.
 *
 * **Autowiring is a MAY, not a MUST.** {@see get()} and {@see has()} do not
 * themselves promise auto-resolution of unregistered classes — a minimal,
 * spec-conformant implementation may simply throw from `get()` and return
 * `false` from `has()` for any identifier that was never explicitly
 * registered via {@see registerService()}. An implementation MAY choose to
 * additionally auto-resolve unregistered but existing classes (typically by
 * delegating internally to {@see resolve()}) — that is a capability of the
 * implementation, not a guarantee of this interface. Callers that need
 * guaranteed autowiring on `get()`/`has()` MUST depend on that documented
 * capability of their chosen implementation, not on this interface alone.
 * {@see resolve()} remains the one method whose contract IS auto-wiring:
 * calling it always attempts constructor-dependency resolution.
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
     * {@see \Milpa\Attributes\RegisterService} scanner calls after
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
     * Guaranteed to resolve identifiers registered via {@see registerService()}.
     * For an identifier that was never registered but names an existing class,
     * an implementation MAY auto-resolve and register it as a singleton (see
     * the class docblock) — a minimal implementation MAY instead throw
     * {@see NotFoundExceptionInterface} for any unregistered identifier.
     * Consult the implementation's own documentation for which behavior it
     * guarantees.
     *
     * @param string $id Service identifier (usually FQCN)
     *
     * @return mixed Service instance
     *
     * @throws NotFoundExceptionInterface                 No entry was found for this identifier (and, if the implementation does not auto-resolve, no entry ever will be).
     * @throws \Psr\Container\ContainerExceptionInterface Auto-resolution of the entry failed.
     */
    public function get(string $id): mixed;

    /**
     * Checks whether an identifier is resolvable.
     *
     * Guaranteed `true` for identifiers registered via {@see registerService()}.
     * For an unregistered identifier, an implementation MAY additionally
     * return `true` when the identifier names a class it is willing to
     * auto-wire (see the class docblock) — a minimal implementation MAY
     * instead return `false` for anything not explicitly registered.
     */
    public function has(string $id): bool;

    /**
     * Resolve a class with auto-wiring.
     *
     * Instantiates the class resolving constructor dependencies from the container.
     * If $singleton is true, registers the instance for future use.
     *
     * Unlike {@see get()}/{@see has()}, autowiring is this method's actual
     * contract: every conformant implementation MUST attempt constructor
     * resolution here, regardless of what it guarantees for `get()`/`has()`.
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
