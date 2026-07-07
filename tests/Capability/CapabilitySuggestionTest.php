<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Capability;

use PHPUnit\Framework\TestCase;
use Milpa\ValueObjects\Capability\CapabilitySuggestion;

/**
 * The `suggests` capability record:
 *   { id, interface, constraint, fallback? }
 *
 * Missing suggested capabilities MUST NOT fail boot; `fallback` names the
 * graceful-degradation path (e.g. "noop").
 */
final class CapabilitySuggestionTest extends TestCase
{
    public function testFromArrayParsesFullRecord(): void
    {
        $vo = CapabilitySuggestion::fromArray([
            'id' => 'example.logger',
            'interface' => 'App\\Contracts\\LoggerInterface',
            'constraint' => '^1.0',
            'fallback' => 'noop',
        ]);

        $this->assertSame('example.logger', $vo->id);
        $this->assertSame('App\\Contracts\\LoggerInterface', $vo->interface);
        $this->assertSame('^1.0', $vo->constraint);
        $this->assertSame('noop', $vo->fallback);
    }

    public function testFromArrayAppliesDefaults(): void
    {
        $vo = CapabilitySuggestion::fromArray([
            'id' => 'x.y',
            'interface' => 'App\\Contracts\\Thing',
        ]);

        $this->assertSame('*', $vo->constraint);
        $this->assertNull($vo->fallback);
    }

    public function testFromInterfaceWrapsLegacyBareFqcn(): void
    {
        $fqcn = 'App\\Contracts\\Thing';
        $vo = CapabilitySuggestion::fromInterface($fqcn);

        $this->assertSame($fqcn, $vo->interface);
        $this->assertSame($fqcn, $vo->id);
        $this->assertSame('*', $vo->constraint);
        $this->assertNull($vo->fallback);
    }

    public function testParseDispatchesStringAndArray(): void
    {
        $this->assertNull(CapabilitySuggestion::parse('App\\X')->fallback);
        $this->assertSame('noop', CapabilitySuggestion::parse([
            'id' => 'a',
            'interface' => 'App\\X',
            'fallback' => 'noop',
        ])->fallback);
    }

    public function testRejectsMissingId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilitySuggestion::fromArray(['interface' => 'App\\X']);
    }

    public function testRejectsMissingInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilitySuggestion::fromArray(['id' => 'a']);
    }
}
