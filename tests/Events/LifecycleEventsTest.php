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

namespace Milpa\Tests\Events;

use PHPUnit\Framework\TestCase;
use Milpa\Events\CapabilityResolvedEvent;
use Milpa\Events\InterceptionSlot;
use Milpa\Events\KernelBootedEvent;
use Milpa\Events\PluginBootedEvent;
use Milpa\Events\PluginBootingEvent;

/**
 * The four kernel-boot lifecycle event VOs ({@see CapabilityResolvedEvent},
 * {@see KernelBootedEvent}, {@see PluginBootedEvent}, {@see PluginBootingEvent}) — promoted
 * from `milpa/runtime` so the runtime kernel and, eventually, the legacy host share one
 * source instead of maintaining diverging copies. Each is a readonly VO (family convention,
 * core/events KEYSTONE): construction exposes the payload verbatim and properties reject
 * reassignment.
 */
final class LifecycleEventsTest extends TestCase
{
    public function testCapabilityResolvedEventExposesTheFinalizedLoadOrder(): void
    {
        $loadOrder = [
            ['name' => 'ProvidingPlugin', 'class' => 'App\\ProvidingPlugin'],
            ['name' => 'DependentPlugin', 'class' => 'App\\DependentPlugin'],
        ];

        $event = new CapabilityResolvedEvent($loadOrder);

        $this->assertSame($loadOrder, $event->loadOrder);
    }

    public function testCapabilityResolvedEventLoadOrderPropertyIsReadonly(): void
    {
        $event = new CapabilityResolvedEvent([]);

        $this->expectException(\Error::class);
        // @phpstan-ignore-next-line readonly.propertyAssign — the point of this test is the runtime error
        $event->loadOrder = [['name' => 'Mutated']];
    }

    public function testKernelBootedEventExposesTheBootedPluginNames(): void
    {
        $event = new KernelBootedEvent(['ProvidingPlugin', 'DependentPlugin']);

        $this->assertSame(['ProvidingPlugin', 'DependentPlugin'], $event->bootedPluginNames);
    }

    public function testKernelBootedEventAcceptsAnEmptyBootedList(): void
    {
        // e.g. every configured plugin was vetoed via 'plugin.booting' — 'kernel.booted' still
        // fires as the boot-is-complete signal, just with nothing booted.
        $event = new KernelBootedEvent([]);

        $this->assertSame([], $event->bootedPluginNames);
    }

    public function testPluginBootedEventExposesNameAndMetadata(): void
    {
        $metadata = ['name' => 'ProvidingPlugin', 'provides' => ['App\\SomeCapability'], 'requires' => []];

        $event = new PluginBootedEvent('ProvidingPlugin', $metadata);

        $this->assertSame('ProvidingPlugin', $event->pluginName);
        $this->assertSame($metadata, $event->metadata);
    }

    public function testPluginBootedEventMetadataDefaultsToEmptyArray(): void
    {
        $event = new PluginBootedEvent('ProvidingPlugin');

        $this->assertSame([], $event->metadata);
    }

    public function testPluginBootingEventExposesNameAndMetadata(): void
    {
        $metadata = ['name' => 'ProvidingPlugin', 'provides' => [], 'requires' => []];

        $event = new PluginBootingEvent('ProvidingPlugin', $metadata);

        $this->assertSame('ProvidingPlugin', $event->pluginName);
        $this->assertSame($metadata, $event->metadata);
    }

    public function testPluginBootingEventMetadataDefaultsToEmptyArray(): void
    {
        $event = new PluginBootingEvent('ProvidingPlugin');

        $this->assertSame([], $event->metadata);
    }

    /**
     * {@see PluginBootingEvent} is the family's interceptable lifecycle event: per its
     * docblock, veto lives exclusively in the {@see InterceptionSlot} dispatched ALONGSIDE
     * it (payload shaped `['event' => $event, 'slot' => $slot]`), never on the event object
     * itself — the event stays readonly and carries no mutable state of its own. This pins
     * that an emitter (e.g. `Kernel::bootPlugins()`) reading the slot back after a listener
     * vetoes still finds the event object untouched.
     */
    public function testPluginBootingEventCarriesNoMutableStateAndVetoLivesOnTheSlotAlongsideIt(): void
    {
        $event = new PluginBootingEvent('VetoedPlugin', ['name' => 'VetoedPlugin']);
        $slot = new InterceptionSlot();

        $payload = ['event' => $event, 'slot' => $slot];

        // A listener vetoes via the slot from the dispatch payload, not the event.
        $payload['slot']->stop();

        $this->assertTrue($slot->isStopped(), 'the emitter must read the veto back off the slot');
        $this->assertFalse($slot->hasResult(), 'plugin.booting is pure veto — it must never carry a replacement result');
        $this->assertSame('VetoedPlugin', $event->pluginName, 'the event itself must stay untouched by the veto');
        $this->assertSame($event, $payload['event'], 'the same readonly event instance travels through the payload');
    }
}
