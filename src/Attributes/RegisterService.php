<?php

declare(strict_types=1);

namespace Milpa\app\Attributes;

use Attribute;

/**
 * Marks a class as a service to be auto-registered in the DI container.
 *
 * A service scanner discovers classes carrying this attribute, instantiates
 * each one (honoring {@see $factory} and {@see $dependencies}), and registers
 * the resulting instance through
 * {@see \Milpa\app\Interfaces\Di\DIContainerInterface::registerService()}.
 * Because a concrete instance is registered, the service is effectively a
 * singleton (one shared instance); an explicit `registerService()` for the same
 * id takes precedence over `resolve()` autowiring.
 *
 * @example
 * #[RegisterService(
 *     id: ApiAuthMiddleware::class,
 *     dependencies: [EntityManagerInterface::class]
 * )]
 * class ApiAuthMiddleware { ... }
 *
 * @package Milpa\app\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RegisterService
{
    /**
     * @param string|null              $id           Service identifier (defaults to the class name)
     * @param array<int, class-string> $dependencies Dependency class names for constructor injection
     * @param string|null              $factory      Static factory method name if custom instantiation is needed
     */
    public function __construct(
        public readonly ?string $id = null,
        public readonly array $dependencies = [],
        public readonly ?string $factory = null
    ) {
    }
}
