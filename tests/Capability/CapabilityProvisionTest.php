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
use Milpa\ValueObjects\Capability\CapabilityProvision;

/**
 * The `provides` capability record:
 *   { id, interface, contractVersion, service?, priority?, exclusive? }
 *
 * Must also accept the legacy bare-FQCN string form so existing manifests
 * (contracts.provides = ["Foo\\BarInterface", ...]) keep working.
 */
final class CapabilityProvisionTest extends TestCase
{
    public function testFromArrayParsesFullRecord(): void
    {
        $vo = CapabilityProvision::fromArray([
            'id' => 'example.cache.redis',
            'interface' => 'App\\Contracts\\CacheInterface',
            'contractVersion' => '1.0.0',
            'service' => 'App\\Cache\\RedisCache',
            'priority' => 100,
            'exclusive' => true,
        ]);

        $this->assertSame('example.cache.redis', $vo->id);
        $this->assertSame('App\\Contracts\\CacheInterface', $vo->interface);
        $this->assertSame('1.0.0', $vo->contractVersion);
        $this->assertSame('App\\Cache\\RedisCache', $vo->service);
        $this->assertSame(100, $vo->priority);
        $this->assertTrue($vo->exclusive);
    }

    public function testFromArrayAppliesDefaultsForOptionalFields(): void
    {
        $vo = CapabilityProvision::fromArray([
            'id' => 'example.logger',
            'interface' => 'App\\Contracts\\LoggerInterface',
            'contractVersion' => '2.1.0',
        ]);

        $this->assertNull($vo->service);
        $this->assertSame(0, $vo->priority);
        $this->assertTrue(
            $vo->exclusive,
            'capability-spec §3.1: "id MUST be globally unique in the installed graph unless '
            . '`exclusive=false` allows multi-provider lists" — a record that does not declare '
            . '`exclusive` defaults to true, matching milpa-plugin.schema.json.'
        );
    }

    /**
     * The primary constructor applies the same §3.1 canon default as fromArray(): a
     * hand-built record that does not declare `exclusive` claims its id exclusively.
     */
    public function testConstructorDefaultsExclusiveToTrue(): void
    {
        $vo = new CapabilityProvision(id: 'x.y', interface: 'App\\Contracts\\Thing', contractVersion: '1.0.0');

        $this->assertTrue($vo->exclusive, 'the constructor default follows the schema default (true)');
    }

    /**
     * Declaring `exclusive: false` is the spec's explicit opt-in to multi-provider lists —
     * the default flipping to true must not swallow the declared opt-out.
     */
    public function testExplicitExclusiveFalseIsPreserved(): void
    {
        $fromArray = CapabilityProvision::fromArray([
            'id' => 'example.logger',
            'interface' => 'App\\Contracts\\LoggerInterface',
            'contractVersion' => '2.1.0',
            'exclusive' => false,
        ]);
        $this->assertFalse($fromArray->exclusive);

        $constructed = new CapabilityProvision(
            id: 'x.y',
            interface: 'App\\Contracts\\Thing',
            contractVersion: '1.0.0',
            exclusive: false,
        );
        $this->assertFalse($constructed->exclusive);
    }

    public function testFromInterfaceWrapsLegacyBareFqcn(): void
    {
        $fqcn = 'App\\Plugins\\ExamplePlugin\\Interfaces\\WidgetServiceInterface';
        $vo = CapabilityProvision::fromInterface($fqcn);

        $this->assertSame($fqcn, $vo->interface);
        $this->assertSame($fqcn, $vo->id, 'legacy id falls back to the interface FQCN');
        $this->assertSame('0.0.0', $vo->contractVersion, 'legacy capability is unversioned');
        $this->assertNull($vo->service);
        $this->assertFalse(
            $vo->exclusive,
            'legacy bare-FQCN declarations predate the `exclusive` field and cannot opt out of it, '
            . 'so the legacy wrapper pins exclusive=false — preserving the documented multi-provider '
            . 'last-wins semantics the resolver replicates from the legacy ContractResolver.'
        );
    }

    public function testParseDispatchesStringToLegacyAndArrayToRecord(): void
    {
        $fromString = CapabilityProvision::parse('App\\Contracts\\Thing');
        $this->assertSame('App\\Contracts\\Thing', $fromString->interface);
        $this->assertSame('0.0.0', $fromString->contractVersion);

        $fromArray = CapabilityProvision::parse([
            'id' => 'x.y',
            'interface' => 'App\\Contracts\\Thing',
            'contractVersion' => '3.0.0',
        ]);
        $this->assertSame('3.0.0', $fromArray->contractVersion);
    }

    public function testRejectsMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityProvision::fromArray([
            'interface' => 'App\\Contracts\\Thing',
            'contractVersion' => '1.0.0',
        ]);
    }

    public function testRejectsMissingInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityProvision::fromArray([
            'id' => 'x.y',
            'contractVersion' => '1.0.0',
        ]);
    }

    public function testRejectsInvalidContractVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityProvision::fromArray([
            'id' => 'x.y',
            'interface' => 'App\\Contracts\\Thing',
            'contractVersion' => 'not-a-semver',
        ]);
    }

    /**
     * The primary constructor validates exactly like fromArray() does — hand-building
     * a VO (as consumers historically did, see the capability-graph checker) can no
     * longer silently produce an invalid record.
     */
    public function testConstructorRejectsEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CapabilityProvision(id: '', interface: 'App\\Contracts\\Thing', contractVersion: '1.0.0');
    }

    public function testConstructorRejectsEmptyInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CapabilityProvision(id: 'x.y', interface: '', contractVersion: '1.0.0');
    }

    public function testConstructorRejectsInvalidContractVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CapabilityProvision(id: 'x.y', interface: 'App\\Contracts\\Thing', contractVersion: 'not-a-semver');
    }

    public function testConstructorAcceptsAValidRecord(): void
    {
        $vo = new CapabilityProvision(id: 'x.y', interface: 'App\\Contracts\\Thing', contractVersion: '1.0.0');

        $this->assertSame('x.y', $vo->id);
        $this->assertSame('App\\Contracts\\Thing', $vo->interface);
        $this->assertSame('1.0.0', $vo->contractVersion);
    }
}
