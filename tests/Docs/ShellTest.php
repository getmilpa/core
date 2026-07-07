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

namespace Milpa\Tests\Docs;

use Milpa\Docs\Shell;
use PHPUnit\Framework\TestCase;

final class ShellTest extends TestCase
{
    public function testWrapsContentWithCdnCssAndShell(): void
    {
        $html = (new Shell('https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0'))
            ->page('SemanticVersion', '<nav class="mui-docs__nav">N</nav>', '<p>MAIN</p>', '<nav class="mui-toc">T</nav>');

        $this->assertStringContainsString('<html lang="en" data-theme="dark">', $html);
        $this->assertStringContainsString('@milpa/design@0.8.0/layouts/milpa-layouts.css', $html);
        $this->assertStringContainsString('@milpa/design@0.8.0/artifacts/milpa-artifacts.css', $html);
        $this->assertStringContainsString('class="mui-docs__topbar"', $html);
        $this->assertStringContainsString('class="mui-docs"', $html);
        $this->assertStringContainsString('id="theme-toggle"', $html);
        $this->assertStringContainsString('MAIN', $html);
        $this->assertStringContainsString('<title>SemanticVersion', $html);
        $this->assertStringContainsString('mui-version-switcher', $html);
        $this->assertStringContainsString('v0.2.0', $html);
        $this->assertStringContainsString('teamx.agency', $html);
    }
}
