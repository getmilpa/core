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

namespace Milpa\Attributes;

use Attribute;
use Milpa\Enums\ListenerPriority;

/**
 * Marks a class as a declarative business rule.
 *
 * Generic attribute to annotate a business rule with its event, the entities it
 * applies to, and its execution priority. Note: the runtime that consumed this
 * attribute (RuleScanner) was removed as incomplete; the attribute is kept as an
 * inert primitive for future use.
 *
 * @example
 * #[BusinessRule(
 *     event: 'pre.operation',
 *     entities: [SomeEntity::class],
 *     priority: ListenerPriority::HIGH
 * )]
 * class SomeCondition { ... }
 *
 * @package Milpa\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class BusinessRule
{
    public readonly int $priorityValue;

    /**
     * @param string                    $event       Event to subscribe to (default: 'pre.operation')
     * @param array<int, class-string>  $entities    Entity classes this rule applies to
     * @param int|ListenerPriority      $priority    Execution priority
     * @param string|null               $description Human-readable description of the rule
     * @param bool                      $enabled     Whether the rule is enabled
     */
    public function __construct(
        public readonly string $event = 'pre.operation',
        public readonly array $entities = [],
        public readonly int|ListenerPriority $priority = ListenerPriority::HIGH,
        public readonly ?string $description = null,
        public readonly bool $enabled = true
    ) {
        $this->priorityValue = $priority instanceof ListenerPriority
            ? $priority->value
            : $priority;
    }

    /**
     * Get the numeric priority value.
     */
    public function getPriority(): int
    {
        return $this->priorityValue;
    }
}
