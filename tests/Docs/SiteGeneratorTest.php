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

namespace Milpa\Tests\Docs;

use Milpa\Docs\SiteGenerator;
use PHPUnit\Framework\TestCase;

final class SiteGeneratorTest extends TestCase
{
    public function testGeneratesIndexAndClassPages(): void
    {
        $out = sys_get_temp_dir() . '/milpa-docs-' . getmypid();
        $count = (new SiteGenerator(dirname(__DIR__, 2) . '/src', $out, 'https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0'))->generate();

        $this->assertGreaterThan(35, $count);
        $this->assertFileExists($out . '/index.html');
        $index = file_get_contents($out . '/index.html');
        $this->assertStringContainsString('SemanticVersion', $index);
        // landing: descriptive hero + install snippet + grouped, anchored API index
        $this->assertStringContainsString('modular PHP runtime', $index);
        $this->assertStringContainsString('composer require milpa/core', $index);
        $this->assertStringContainsString('id="group-ValueObjects"', $index);
        // a class page carries a mui-api entry for one of its methods, under a breadcrumb
        $page = glob($out . '/ValueObjects/SemanticVersion.html')[0] ?? '';
        $this->assertNotSame('', $page);
        $pageHtml = file_get_contents($page);
        $this->assertStringContainsString('mui-api__signature', $pageHtml);
        $this->assertStringContainsString('mui-breadcrumbs', $pageHtml);
    }
}
