<?php

declare(strict_types=1);

namespace Milpa\Tests\ValueObjects;

use Milpa\app\ValueObjects\SemanticVersion;
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
