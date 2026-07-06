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

namespace Milpa\Tests\ValueObjects\Tooling;

use Milpa\ValueObjects\Tooling\ToolOptions;
use PHPUnit\Framework\TestCase;

/**
 * ToolOptions replaces the untyped `array $options` on
 * ToolRegistryInterface::register(). fromArray() rejecting unknown keys is
 * the regression guard for the demonstrated silent `category` drop (#6):
 * ToolDefinition has no `category` property, so a raw options array with
 * that key used to vanish with no error.
 */
final class ToolOptionsTest extends TestCase
{
    public function testFromArrayRejectsUnknownKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/category/');

        ToolOptions::fromArray(['category' => 'x']);
    }

    public function testFromArrayRoundTripsViaToArray(): void
    {
        $input = [
            'scopes' => ['admin', 'project'],
            'mutating' => true,
            'requiresConfirmation' => true,
            'timeout' => 30,
            'clamps' => ['page' => ['min' => 1, 'max' => 100]],
            'version' => '2.0.0',
            'outputSchema' => ['type' => 'object'],
        ];

        $options = ToolOptions::fromArray($input);

        $this->assertSame($input, $options->toArray());
    }

    public function testDefaultsAreEmptyAndFalse(): void
    {
        $options = ToolOptions::fromArray([]);

        $this->assertSame([], $options->scopes);
        $this->assertFalse($options->mutating);
        $this->assertFalse($options->requiresConfirmation);
        $this->assertNull($options->timeout);
        $this->assertSame([], $options->clamps);
        $this->assertNull($options->version);
        $this->assertNull($options->outputSchema);
    }
}
