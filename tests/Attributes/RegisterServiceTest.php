<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use Milpa\Attributes\RegisterService;

/**
 * Core-only coverage for the `#[RegisterService]` attribute, which marks a
 * class for auto-registration in the DI container (ServiceScanner discovers
 * it during plugin boot).
 */
final class RegisterServiceTest extends TestCase
{
    public function testDefaults(): void
    {
        $attr = new RegisterService();

        $this->assertNull($attr->id);
        $this->assertSame([], $attr->dependencies);
        $this->assertNull($attr->factory);
    }

    public function testConstructorSetsExplicitId(): void
    {
        $attr = new RegisterService(id: 'my.custom.service');

        $this->assertSame('my.custom.service', $attr->id);
    }

    public function testConstructorSetsDependencies(): void
    {
        $attr = new RegisterService(
            dependencies: ['DatabaseInterface', 'LoggerInterface']
        );

        $this->assertSame(['DatabaseInterface', 'LoggerInterface'], $attr->dependencies);
    }

    public function testConstructorSetsFactory(): void
    {
        $attr = new RegisterService(factory: 'createInstance');

        $this->assertSame('createInstance', $attr->factory);
    }

    public function testFullCustomConfiguration(): void
    {
        $attr = new RegisterService(
            id: 'app.mailer',
            dependencies: ['TransportInterface'],
            factory: 'make'
        );

        $this->assertSame('app.mailer', $attr->id);
        $this->assertSame(['TransportInterface'], $attr->dependencies);
        $this->assertSame('make', $attr->factory);
    }

    public function testPropertiesAreReadonly(): void
    {
        $reflection = new \ReflectionClass(RegisterService::class);

        foreach (['id', 'dependencies', 'factory'] as $propertyName) {
            $this->assertTrue(
                $reflection->getProperty($propertyName)->isReadOnly(),
                "Expected RegisterService::\${$propertyName} to be readonly"
            );
        }
    }

    public function testAttributeIsTargetClass(): void
    {
        $reflection = new \ReflectionClass(RegisterService::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();
        $this->assertSame(\Attribute::TARGET_CLASS, $attribute->flags);
    }

    public function testAttributeIsReadableViaReflectionOnAnnotatedClass(): void
    {
        $reflection = new \ReflectionClass(FakeServiceAnnotatedWithRegisterService::class);
        $attributes = $reflection->getAttributes(RegisterService::class);

        $this->assertCount(1, $attributes);

        /** @var RegisterService $instance */
        $instance = $attributes[0]->newInstance();
        $this->assertSame('fixture.service', $instance->id);
        $this->assertSame(['DependencyOne'], $instance->dependencies);
    }
}

#[RegisterService(id: 'fixture.service', dependencies: ['DependencyOne'])]
final class FakeServiceAnnotatedWithRegisterService
{
}
