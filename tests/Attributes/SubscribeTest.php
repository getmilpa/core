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

namespace Milpa\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use Milpa\Attributes\Subscribe;
use Milpa\Enums\DispatcherType;
use Milpa\Enums\ListenerPriority;

/**
 * Core-only coverage for the `#[Subscribe]` attribute, which marks a class or
 * method as an event subscriber (class-level or method-level, repeatable).
 */
final class SubscribeTest extends TestCase
{
    public function testDefaults(): void
    {
        $subscribe = new Subscribe(event: 'user.created');

        $this->assertSame('user.created', $subscribe->event);
        $this->assertNull($subscribe->method);
        $this->assertSame(ListenerPriority::NORMAL, $subscribe->priority);
        $this->assertNull($subscribe->dispatcher);
        $this->assertSame(ListenerPriority::NORMAL->value, $subscribe->priorityValue);
    }

    public function testConstructorSetsMethod(): void
    {
        $subscribe = new Subscribe(
            event: 'order.placed',
            method: 'onOrderPlaced'
        );

        $this->assertSame('onOrderPlaced', $subscribe->method);
    }

    public function testEnumPriorityIsResolvedToItsNumericValue(): void
    {
        $subscribe = new Subscribe(
            event: 'critical.event',
            priority: ListenerPriority::HIGH
        );

        $this->assertSame(ListenerPriority::HIGH, $subscribe->priority);
        $this->assertSame(100, $subscribe->priorityValue);
        $this->assertSame(100, $subscribe->getPriority());
    }

    public function testIntPriorityIsUsedVerbatim(): void
    {
        $subscribe = new Subscribe(
            event: 'custom.event',
            priority: 500
        );

        $this->assertSame(500, $subscribe->priority);
        $this->assertSame(500, $subscribe->priorityValue);
        $this->assertSame(500, $subscribe->getPriority());
    }

    public function testConstructorSetsDispatcher(): void
    {
        $symfony = new Subscribe(event: 'event', dispatcher: DispatcherType::SYMFONY);
        $milpa = new Subscribe(event: 'event', dispatcher: DispatcherType::MILPA);

        $this->assertSame(DispatcherType::SYMFONY, $symfony->dispatcher);
        $this->assertSame(DispatcherType::MILPA, $milpa->dispatcher);
    }

    public function testGetPriorityMatchesPriorityValueForAllEnumLevels(): void
    {
        $cases = [
            [ListenerPriority::CRITICAL, 500],
            [ListenerPriority::HIGHEST, 200],
            [ListenerPriority::HIGH, 100],
            [ListenerPriority::NORMAL, 0],
            [ListenerPriority::LOW, -100],
            [ListenerPriority::LOWEST, -200],
        ];

        foreach ($cases as [$enum, $expected]) {
            $subscribe = new Subscribe(event: 'e', priority: $enum);
            $this->assertSame($expected, $subscribe->getPriority(), "Priority {$enum->name} should be {$expected}");
        }
    }

    public function testNegativeIntPriority(): void
    {
        $subscribe = new Subscribe(event: 'e', priority: -50);

        $this->assertSame(-50, $subscribe->priorityValue);
        $this->assertSame(-50, $subscribe->getPriority());
    }

    public function testIsRepeatableAndTargetsClassAndMethod(): void
    {
        $reflection = new \ReflectionClass(Subscribe::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        $this->assertTrue((bool) ($attribute->flags & \Attribute::IS_REPEATABLE));
        $this->assertTrue((bool) ($attribute->flags & \Attribute::TARGET_CLASS));
        $this->assertTrue((bool) ($attribute->flags & \Attribute::TARGET_METHOD));
    }

    public function testAttributeIsReadableViaReflectionOnAnnotatedMethod(): void
    {
        $reflection = new \ReflectionMethod(FakeListenerAnnotatedWithSubscribe::class, 'onThing');
        $attributes = $reflection->getAttributes(Subscribe::class);

        $this->assertCount(1, $attributes);

        /** @var Subscribe $instance */
        $instance = $attributes[0]->newInstance();
        $this->assertSame('thing.happened', $instance->event);
        $this->assertSame(200, $instance->getPriority());
    }
}

final class FakeListenerAnnotatedWithSubscribe
{
    #[Subscribe(event: 'thing.happened', priority: ListenerPriority::HIGHEST)]
    public function onThing(): void
    {
    }
}
