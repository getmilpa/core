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

namespace Milpa\Attributes;

use Attribute;
use Milpa\Enums\DispatcherType;
use Milpa\Enums\ListenerPriority;

/**
 * Marks a class or method as an event subscriber.
 *
 * Can be applied to:
 * - Classes: The class becomes a listener, method specified by $method parameter
 * - Methods: The method becomes the event handler
 *
 * @example Class-level subscription:
 * #[Subscribe(event: ActionExecutedEvent::class, method: 'onActionExecuted')]
 * class RenderLayoutListener { ... }
 * @example Method-level subscription:
 * class MyListener {
 *     #[Subscribe(event: 'pre.operation', priority: ListenerPriority::HIGH)]
 *     public function onPreOperation(PreOperationEvent $event): void { ... }
 * }
 * @example Explicit dispatcher selection:
 * #[Subscribe(event: 'ai.tool.invoked', dispatcher: DispatcherType::MILPA)]
 *
 * @package Milpa\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Subscribe
{
    public readonly int $priorityValue;

    /**
     * @param string               $event      Event name or class to subscribe to
     * @param string|null          $method     Handler method name (required for class-level)
     * @param int|ListenerPriority $priority   Execution priority (higher = earlier)
     * @param DispatcherType|null  $dispatcher Target event bus (DispatcherType::SYMFONY or DispatcherType::MILPA)
     */
    public function __construct(
        public readonly string $event,
        public readonly ?string $method = null,
        public readonly int|ListenerPriority $priority = ListenerPriority::NORMAL,
        public readonly ?DispatcherType $dispatcher = null
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
