<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Event;

/**
 * Event Subscriber Interface
 *
 * Plugins implement this to declare which events they subscribe to.
 */
interface EventSubscriberInterface
{
    /**
     * Returns array of events this plugin subscribes to.
     *
     * Format:
     * [
     *     'event.name' => [
     *         'method' => 'handlerMethodName',
     *         'priority' => 0  // Optional, default 0
     *     ],
     *     'other.event' => ['method' => 'onOtherEvent']
     * ]
     *
     * Supports wildcards: 'user.*' matches every one-segment event under `user.`
     * (see MilpaEventDispatcherInterface::subscribe() for the full grammar).
     *
     * @return array<string, array{method: string, priority?: int}>
     */
    public static function getSubscribedEvents(): array;
}
