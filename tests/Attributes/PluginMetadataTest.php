<?php

declare(strict_types=1);

namespace Milpa\Tests\Attributes;

use PHPUnit\Framework\TestCase;
use Milpa\app\Attributes\PluginMetadata;

/**
 * Core-only coverage for the `#[PluginMetadata]` attribute: plugin identity
 * (version/author/site/name/type) plus the provides/requires/suggests
 * dependency lists applied to a plugin's main class.
 */
final class PluginMetadataTest extends TestCase
{
    public function testConstructorSetsIdentityFields(): void
    {
        $metadata = new PluginMetadata(
            version: '1.0.0',
            author: 'Test Author',
            site: 'https://example.com',
            name: 'TestPlugin',
            type: 'Web'
        );

        $this->assertSame('1.0.0', $metadata->version);
        $this->assertSame('Test Author', $metadata->author);
        $this->assertSame('https://example.com', $metadata->site);
        $this->assertSame('TestPlugin', $metadata->name);
        $this->assertSame('Web', $metadata->type);
    }

    public function testDependencyListsDefaultToEmptyArrays(): void
    {
        $metadata = new PluginMetadata(
            version: '1.0.0',
            author: 'Test Author',
            site: 'https://example.com',
            name: 'TestPlugin',
            type: 'Web'
        );

        $this->assertSame([], $metadata->provides);
        $this->assertSame([], $metadata->requires);
        $this->assertSame([], $metadata->suggests);
    }

    public function testConstructorSetsDependencyLists(): void
    {
        $metadata = new PluginMetadata(
            version: '2.0.0',
            author: 'Author',
            site: 'https://site.com',
            name: 'DependentPlugin',
            type: 'Service',
            provides: ['DatabaseInterface', 'CacheInterface'],
            requires: ['CorePlugin'],
            suggests: ['LoggingPlugin']
        );

        $this->assertSame(['DatabaseInterface', 'CacheInterface'], $metadata->provides);
        $this->assertSame(['CorePlugin'], $metadata->requires);
        $this->assertSame(['LoggingPlugin'], $metadata->suggests);
    }

    public function testPositionalArgumentsMatchNamedArguments(): void
    {
        $positional = new PluginMetadata('1.0', 'A', 'S', 'N', 'CLI');
        $named = new PluginMetadata(version: '1.0', author: 'A', site: 'S', name: 'N', type: 'CLI');

        $this->assertEquals($named, $positional);
    }

    public function testAttributeIsTargetClass(): void
    {
        $reflection = new \ReflectionClass(PluginMetadata::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();
        $this->assertSame(\Attribute::TARGET_CLASS, $attribute->flags);
    }

    public function testAttributeIsReadableViaReflectionOnAnnotatedClass(): void
    {
        $reflection = new \ReflectionClass(FakePluginAnnotatedWithMetadata::class);
        $attributes = $reflection->getAttributes(PluginMetadata::class);

        $this->assertCount(1, $attributes);

        /** @var PluginMetadata $instance */
        $instance = $attributes[0]->newInstance();
        $this->assertSame('3.2.1', $instance->version);
        $this->assertSame('Fake', $instance->name);
        $this->assertSame(['FakeInterface'], $instance->provides);
    }
}

#[PluginMetadata(
    version: '3.2.1',
    author: 'Fixture Author',
    site: 'https://fixture.example.com',
    name: 'Fake',
    type: 'Mixed',
    provides: ['FakeInterface']
)]
final class FakePluginAnnotatedWithMetadata
{
}
