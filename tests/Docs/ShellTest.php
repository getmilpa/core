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

namespace Milpa\Tests\Docs;

use Milpa\Docs\Shell;
use Milpa\Docs\SiteConfig;
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

    public function testDefaultConfigCarriesCoreBrandingByteForByte(): void
    {
        $html = (new Shell('https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0'))
            ->page('SemanticVersion', '', '', '');

        $this->assertStringContainsString('<title>SemanticVersion · Milpa Core v0.2.0</title>', $html);
        $this->assertStringContainsString('href="https://getmilpa.github.io/core/"', $html);
        $this->assertStringContainsString('href="https://github.com/getmilpa/core"', $html);
        $this->assertStringContainsString('utm_content=core', $html);
    }

    public function testCustomConfigCarriesTheGivenBrand(): void
    {
        $config = new SiteConfig(
            brand: 'Milpa Fake Brand',
            repoUrl: 'https://github.com/getmilpa/fake',
            pagesUrl: 'https://getmilpa.github.io/fake/',
            utmContent: 'fake',
        );

        $html = (new Shell('https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0', $config))
            ->page('SemanticVersion', '', '', '');

        $this->assertStringContainsString('<title>SemanticVersion · Milpa Fake Brand v0.2.0</title>', $html);
        $this->assertStringContainsString('href="https://getmilpa.github.io/fake/"', $html);
        $this->assertStringContainsString('href="https://github.com/getmilpa/fake"', $html);
        $this->assertStringContainsString('utm_content=fake', $html);
        $this->assertStringNotContainsString('Milpa Core', $html);
    }
}
