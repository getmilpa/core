<?php

declare(strict_types=1);

namespace Milpa\Tests\Docs;

use Milpa\Docs\SiteGenerator;
use PHPUnit\Framework\TestCase;

final class SiteGeneratorTest extends TestCase
{
    public function testGeneratesIndexAndClassPages(): void
    {
        $out = sys_get_temp_dir() . '/milpa-docs-' . getmypid();
        $count = (new SiteGenerator(dirname(__DIR__, 2) . '/src', $out, 'https://cdn.jsdelivr.net/npm/@milpa/design@0.3.0'))->generate();

        $this->assertGreaterThan(35, $count);
        $this->assertFileExists($out . '/index.html');
        $index = file_get_contents($out . '/index.html');
        $this->assertStringContainsString('SemanticVersion', $index);
        // a class page carries a mui-api entry for one of its methods
        $page = glob($out . '/ValueObjects/SemanticVersion.html')[0] ?? '';
        $this->assertNotSame('', $page);
        $this->assertStringContainsString('mui-api__signature', file_get_contents($page));
    }
}
