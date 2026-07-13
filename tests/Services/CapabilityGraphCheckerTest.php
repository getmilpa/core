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

namespace Milpa\Tests\Services;

use Milpa\Attributes\PluginMetadata;
use Milpa\Exceptions\AttributeNotFoundException;
use Milpa\Exceptions\Plugin\PluginDependencyException;
use Milpa\Services\CapabilityGraphChecker;
use PHPUnit\Framework\TestCase;

/**
 * Pins the provides/requires capability graph check: fails pre-boot, with a
 * readable message, when a plugin's `requires` has no matching `provides`
 * among the given plugins. Generalizes the example-blog's hand-rolled
 * `CapabilityGraph` (the friction that motivated moving this into core):
 * accepts plugin instances (read via `#[PluginMetadata]` reflection) OR
 * `PluginMetadata` records directly.
 */
final class CapabilityGraphCheckerTest extends TestCase
{
    public function testPassesWhenEveryRequirementHasAMatchingProvider(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([
            new FakeProviderPlugin(),
            new FakeConsumerPlugin(),
        ]);

        $this->addToAssertionCount(1); // No exception thrown.
    }

    public function testThrowsAReadableExceptionWhenARequirementHasNoProvider(): void
    {
        $checker = new CapabilityGraphChecker();

        try {
            $checker->check([new FakeConsumerPlugin()]);
            $this->fail('Expected a PluginDependencyException.');
        } catch (PluginDependencyException $exception) {
            $this->assertStringContainsString('Consumer', $exception->getMessage());
            $this->assertStringContainsString('Fake\\WidgetServiceInterface', $exception->getMessage());
        }
    }

    public function testSuggestsAreNeverEnforced(): void
    {
        $checker = new CapabilityGraphChecker();

        // FakeSuggesterPlugin suggests an interface nobody provides — must not throw.
        $checker->check([new FakeSuggesterPlugin()]);

        $this->addToAssertionCount(1);
    }

    public function testAcceptsPluginMetadataInstancesDirectlyWithoutReflection(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'ProviderRecord',
                type: 'Service',
                provides: ['Fake\\WidgetServiceInterface'],
            ),
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'ConsumerRecord',
                type: 'Service',
                requires: ['Fake\\WidgetServiceInterface'],
            ),
        ]);

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAMixOfPluginInstancesAndPluginMetadataRecords(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([
            new FakeProviderPlugin(),
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'ConsumerRecord',
                type: 'Service',
                requires: ['Fake\\WidgetServiceInterface'],
            ),
        ]);

        $this->addToAssertionCount(1);
    }

    public function testARequirementCanBeSatisfiedByTheSamePluginThatDeclaresIt(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([new FakeSelfSatisfyingPlugin()]);

        $this->addToAssertionCount(1);
    }

    public function testEmptyPluginListPassesTrivially(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([]);

        $this->addToAssertionCount(1);
    }

    public function testThrowsAttributeNotFoundForAPluginInstanceMissingTheMetadataAttribute(): void
    {
        $checker = new CapabilityGraphChecker();

        $this->expectException(AttributeNotFoundException::class);
        $checker->check([new FakePluginWithoutMetadata()]);
    }

    public function testARichProvidesRecordSatisfiesABareRequireByItsInterface(): void
    {
        $checker = new CapabilityGraphChecker();

        // T087: a structured capability record provides BOTH identities — its capability id and its
        // interface FQCN — so a legacy bare-FQCN consumer still finds its provider.
        $checker->check([
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'RichProvider',
                type: 'Service',
                provides: [[
                    'id' => 'crm.widget.v1',
                    'interface' => 'Fake\\WidgetServiceInterface',
                    'contractVersion' => '1.0.0',
                    'service' => 'Fake\\WidgetService',
                ]],
            ),
            new FakeConsumerPlugin(),
        ]);

        $this->addToAssertionCount(1);
    }

    public function testARichRequireIsMatchedByItsCapabilityId(): void
    {
        $checker = new CapabilityGraphChecker();

        $checker->check([
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'RichProvider',
                type: 'Service',
                provides: [[
                    'id' => 'crm.widget.v1',
                    'interface' => 'Fake\\WidgetServiceInterface',
                    'contractVersion' => '1.0.0',
                    'service' => 'Fake\\WidgetService',
                ]],
            ),
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'RichConsumer',
                type: 'Service',
                requires: [[
                    'id' => 'crm.widget.v1',
                    'interface' => 'Fake\\SomeOtherInterface',
                    'constraint' => '^1.0',
                ]],
            ),
        ]);

        $this->addToAssertionCount(1);
    }

    public function testARichRequireIsSatisfiedByAOneOfAlternative(): void
    {
        $checker = new CapabilityGraphChecker();

        // Identity-only check, like everything here — but a requirement the engine would resolve via
        // a `oneOf` alternative must not fail pre-boot either.
        $checker->check([
            new FakeProviderPlugin(), // provides the bare 'Fake\WidgetServiceInterface'
            new PluginMetadata(
                version: '1.0.0',
                author: 'A',
                site: 'https://example.com',
                name: 'OneOfConsumer',
                type: 'Service',
                requires: [[
                    'id' => 'crm.widget.v1',
                    'interface' => 'Fake\\UnprovidedInterface',
                    'constraint' => '^1.0',
                    'oneOf' => ['Fake\\WidgetServiceInterface'],
                ]],
            ),
        ]);

        $this->addToAssertionCount(1);
    }

    public function testAnUnmetRichRequireNamesItsCapabilityIdInTheException(): void
    {
        $checker = new CapabilityGraphChecker();

        try {
            $checker->check([
                new PluginMetadata(
                    version: '1.0.0',
                    author: 'A',
                    site: 'https://example.com',
                    name: 'RichConsumer',
                    type: 'Service',
                    requires: [[
                        'id' => 'crm.widget.v1',
                        'interface' => 'Fake\\UnprovidedInterface',
                        'constraint' => '^1.0',
                    ]],
                ),
            ]);
            $this->fail('Expected a PluginDependencyException.');
        } catch (PluginDependencyException $exception) {
            $this->assertStringContainsString('RichConsumer', $exception->getMessage());
            $this->assertStringContainsString('crm.widget.v1', $exception->getMessage());
        }
    }
}

#[PluginMetadata(
    version: '1.0.0',
    author: 'Fixture',
    site: 'https://example.com',
    name: 'Provider',
    type: 'Service',
    provides: ['Fake\\WidgetServiceInterface'],
)]
final class FakeProviderPlugin
{
}

#[PluginMetadata(
    version: '1.0.0',
    author: 'Fixture',
    site: 'https://example.com',
    name: 'Consumer',
    type: 'Service',
    requires: ['Fake\\WidgetServiceInterface'],
)]
final class FakeConsumerPlugin
{
}

#[PluginMetadata(
    version: '1.0.0',
    author: 'Fixture',
    site: 'https://example.com',
    name: 'Suggester',
    type: 'Service',
    suggests: ['Fake\\OptionalLoggerInterface'],
)]
final class FakeSuggesterPlugin
{
}

#[PluginMetadata(
    version: '1.0.0',
    author: 'Fixture',
    site: 'https://example.com',
    name: 'SelfSatisfying',
    type: 'Service',
    provides: ['Fake\\SelfInterface'],
    requires: ['Fake\\SelfInterface'],
)]
final class FakeSelfSatisfyingPlugin
{
}

final class FakePluginWithoutMetadata
{
}
