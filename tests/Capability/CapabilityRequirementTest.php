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

namespace Milpa\Tests\Capability;

use PHPUnit\Framework\TestCase;
use Milpa\ValueObjects\Capability\CapabilityRequirement;

/**
 * The `requires` capability record:
 *   { id, interface, constraint, oneOf? }
 *
 * `constraint` is a semver RANGE (e.g. "^1.0"), not an exact version.
 */
final class CapabilityRequirementTest extends TestCase
{
    public function testFromArrayParsesFullRecord(): void
    {
        $vo = CapabilityRequirement::fromArray([
            'id' => 'example.cache',
            'interface' => 'App\\Contracts\\CacheInterface',
            'constraint' => '^1.0',
            'oneOf' => ['example.cache.redis', 'example.cache.memcached'],
        ]);

        $this->assertSame('example.cache', $vo->id);
        $this->assertSame('App\\Contracts\\CacheInterface', $vo->interface);
        $this->assertSame('^1.0', $vo->constraint);
        $this->assertSame(['example.cache.redis', 'example.cache.memcached'], $vo->oneOf);
    }

    public function testFromArrayAppliesDefaults(): void
    {
        $vo = CapabilityRequirement::fromArray([
            'id' => 'x.y',
            'interface' => 'App\\Contracts\\Thing',
        ]);

        $this->assertSame('*', $vo->constraint, 'missing constraint accepts any version');
        $this->assertSame([], $vo->oneOf);
    }

    public function testFromInterfaceWrapsLegacyBareFqcn(): void
    {
        $fqcn = 'App\\Contracts\\Thing';
        $vo = CapabilityRequirement::fromInterface($fqcn);

        $this->assertSame($fqcn, $vo->interface);
        $this->assertSame($fqcn, $vo->id);
        $this->assertSame('*', $vo->constraint);
        $this->assertSame([], $vo->oneOf);
    }

    public function testParseDispatchesStringAndArray(): void
    {
        $this->assertSame('*', CapabilityRequirement::parse('App\\X')->constraint);
        $this->assertSame('^2.0', CapabilityRequirement::parse([
            'id' => 'a',
            'interface' => 'App\\X',
            'constraint' => '^2.0',
        ])->constraint);
    }

    public function testRejectsMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityRequirement::fromArray(['interface' => 'App\\X']);
    }

    public function testRejectsMissingInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityRequirement::fromArray(['id' => 'a']);
    }

    /**
     * The primary constructor validates exactly like fromArray() does — hand-building
     * a VO can no longer silently produce an invalid record.
     */
    public function testConstructorRejectsEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CapabilityRequirement(id: '', interface: 'App\\Contracts\\Thing');
    }

    public function testConstructorRejectsEmptyInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CapabilityRequirement(id: 'a', interface: '');
    }

    public function testConstructorAcceptsAValidRecord(): void
    {
        $vo = new CapabilityRequirement(id: 'a', interface: 'App\\Contracts\\Thing');

        $this->assertSame('a', $vo->id);
        $this->assertSame('App\\Contracts\\Thing', $vo->interface);
    }
}
