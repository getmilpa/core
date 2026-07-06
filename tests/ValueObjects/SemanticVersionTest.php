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

namespace Milpa\Tests\ValueObjects;

use Milpa\ValueObjects\SemanticVersion;
use PHPUnit\Framework\TestCase;

/**
 * Standalone smoke + behavior test for the core's SemanticVersion.
 */
final class SemanticVersionTest extends TestCase
{
    public function testParsesAndCompares(): void
    {
        $a = SemanticVersion::parse('1.2.3');
        $b = SemanticVersion::parse('1.10.0');

        $this->assertTrue($b->greaterThan($a));
        $this->assertFalse($a->greaterThan($b));
        $this->assertTrue($a->equals(SemanticVersion::parse('1.2.3')));
    }
}
