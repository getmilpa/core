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
use Milpa\Attributes\BusinessRule;
use Milpa\Enums\ListenerPriority;

class BusinessRuleTest extends TestCase
{
    public function testConstructorWithDefaults(): void
    {
        $rule = new BusinessRule();

        $this->assertEquals('pre.operation', $rule->event);
        $this->assertEmpty($rule->entities);
        $this->assertEquals(ListenerPriority::HIGH, $rule->priority);
        $this->assertNull($rule->description);
        $this->assertTrue($rule->enabled);
        $this->assertEquals(100, $rule->priorityValue);
    }

    public function testConstructorWithCustomEvent(): void
    {
        $rule = new BusinessRule(event: 'post.operation');

        $this->assertEquals('post.operation', $rule->event);
    }

    public function testConstructorWithEntities(): void
    {
        $rule = new BusinessRule(entities: ['App\\Entity\\User', 'App\\Entity\\Order']);

        $this->assertEquals(['App\\Entity\\User', 'App\\Entity\\Order'], $rule->entities);
    }

    public function testConstructorWithEnumPriority(): void
    {
        $rule = new BusinessRule(priority: ListenerPriority::CRITICAL);

        $this->assertEquals(ListenerPriority::CRITICAL, $rule->priority);
        $this->assertEquals(500, $rule->priorityValue);
    }

    public function testConstructorWithIntPriority(): void
    {
        $rule = new BusinessRule(priority: 42);

        $this->assertEquals(42, $rule->priority);
        $this->assertEquals(42, $rule->priorityValue);
    }

    public function testConstructorWithDescription(): void
    {
        $rule = new BusinessRule(description: 'Validates user age is above 18');

        $this->assertEquals('Validates user age is above 18', $rule->description);
    }

    public function testConstructorWithDisabled(): void
    {
        $rule = new BusinessRule(enabled: false);

        $this->assertFalse($rule->enabled);
    }

    public function testGetPriorityWithEnumValue(): void
    {
        $rule = new BusinessRule(priority: ListenerPriority::HIGHEST);

        $this->assertEquals(200, $rule->getPriority());
    }

    public function testGetPriorityWithIntValue(): void
    {
        $rule = new BusinessRule(priority: 75);

        $this->assertEquals(75, $rule->getPriority());
    }

    public function testAllPriorityLevels(): void
    {
        $priorities = [
            ['enum' => ListenerPriority::CRITICAL, 'expected' => 500],
            ['enum' => ListenerPriority::HIGHEST, 'expected' => 200],
            ['enum' => ListenerPriority::HIGH, 'expected' => 100],
            ['enum' => ListenerPriority::NORMAL, 'expected' => 0],
            ['enum' => ListenerPriority::LOW, 'expected' => -100],
            ['enum' => ListenerPriority::LOWEST, 'expected' => -200],
        ];

        foreach ($priorities as $item) {
            $rule = new BusinessRule(priority: $item['enum']);
            $this->assertEquals($item['expected'], $rule->getPriority(), "Priority {$item['enum']->name} should be {$item['expected']}");
        }
    }

    public function testFullCustomConfiguration(): void
    {
        $rule = new BusinessRule(
            event: 'custom.event',
            entities: ['Entity1', 'Entity2'],
            priority: ListenerPriority::CRITICAL,
            description: 'Custom rule description',
            enabled: true
        );

        $this->assertEquals('custom.event', $rule->event);
        $this->assertEquals(['Entity1', 'Entity2'], $rule->entities);
        $this->assertEquals(ListenerPriority::CRITICAL, $rule->priority);
        $this->assertEquals(500, $rule->priorityValue);
        $this->assertEquals('Custom rule description', $rule->description);
        $this->assertTrue($rule->enabled);
        $this->assertEquals(500, $rule->getPriority());
    }

    public function testAttributeIsTargetClass(): void
    {
        $reflection = new \ReflectionClass(BusinessRule::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();
        $this->assertEquals(\Attribute::TARGET_CLASS, $attribute->flags);
    }

    public function testNegativeIntPriority(): void
    {
        $rule = new BusinessRule(priority: -50);

        $this->assertEquals(-50, $rule->priorityValue);
        $this->assertEquals(-50, $rule->getPriority());
    }

    public function testZeroPriority(): void
    {
        $rule = new BusinessRule(priority: 0);

        $this->assertEquals(0, $rule->priorityValue);
        $this->assertEquals(0, $rule->getPriority());
    }
}
