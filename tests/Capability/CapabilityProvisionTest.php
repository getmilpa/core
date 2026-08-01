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
        $this->assertNull(
            $vo->exclusive,
            'P17.3: un registro que no declara `exclusive` no decide nada. El default §3.1 se retiró '
            . '—del esquema también— porque, junto al `false` que fijaba la forma legacy, hacía que '
            . 'la cardinalidad dependiera de CÓMO se escribió la capacidad (ADR-0037).'
        );
    }

    /**
     * El constructor no inventa cardinalidad, igual que fromArray(): un registro hecho a mano que
     * no declara `exclusive` deja la decisión sin tomar, y quien la reporta es el resolver.
     */
    public function testConstructorLeavesCardinalityUndecided(): void
    {
        $vo = new CapabilityProvision(id: 'x.y', interface: 'App\\Contracts\\Thing', contractVersion: '1.0.0');

        $this->assertNull($vo->exclusive, 'sin declarar = sin decidir');
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
        $this->assertNull($vo->contractVersion, 'legacy = versión DESCONOCIDA, no 0.0.0');
        $this->assertNull($vo->service);
        $this->assertNull(
            $vo->exclusive,
            'una declaración legacy es anterior al campo, así que no dice nada sobre la multiplicidad. '
            . 'Fijar `false` aquí era decidir por quien no decidió, igual que el `true` del registro rico.'
        );
    }

    public function testParseDispatchesStringToLegacyAndArrayToRecord(): void
    {
        $fromString = CapabilityProvision::parse('App\\Contracts\\Thing');
        $this->assertSame('App\\Contracts\\Thing', $fromString->interface);
        $this->assertNull($fromString->contractVersion);

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
