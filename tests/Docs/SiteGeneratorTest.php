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

use Milpa\Docs\SiteConfig;
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

    public function testCustomConfigCarriesTheGivenBrandInstallHeroAndUtmContent(): void
    {
        $out = sys_get_temp_dir() . '/milpa-docs-fake-' . getmypid();
        $config = new SiteConfig(
            brand: 'Milpa Fake Brand',
            installCommand: 'composer require milpa/fake-brand',
            repoUrl: 'https://github.com/getmilpa/fake-brand',
            pagesUrl: 'https://getmilpa.github.io/fake-brand/',
            heroParagraph: 'A fake package, built only to prove the config seam.',
            utmContent: 'fake-brand',
        );

        (new SiteGenerator(dirname(__DIR__, 2) . '/src', $out, 'https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0', $config))->generate();

        $index = file_get_contents($out . '/index.html');
        $this->assertStringContainsString('<title>Milpa Fake Brand API · Milpa Fake Brand v0.2.0</title>', $index);
        $this->assertStringContainsString('id="milpa-fake-brand"', $index);
        $this->assertStringContainsString('Milpa Fake Brand <span', $index);
        $this->assertStringContainsString('composer require milpa/fake-brand', $index);
        $this->assertStringContainsString('A fake package, built only to prove the config seam.', $index);
        $this->assertStringContainsString('href="https://getmilpa.github.io/fake-brand/"', $index);
        $this->assertStringContainsString('href="https://github.com/getmilpa/fake-brand"', $index);
        $this->assertStringContainsString('utm_content=fake-brand', $index);
        $this->assertStringNotContainsString('Milpa Core', $index);
        $this->assertStringNotContainsString('composer require milpa/core', $index);
    }

    public function testCustomNsPrefixThatMatchesNothingFallsEveryTypeIntoTheCoreGroup(): void
    {
        $out = sys_get_temp_dir() . '/milpa-docs-nsprefix-' . getmypid();
        $config = new SiteConfig(nsPrefix: 'Bogus\\');

        (new SiteGenerator(dirname(__DIR__, 2) . '/src', $out, 'https://cdn.jsdelivr.net/npm/@milpa/design@0.8.0', '0.2.0', $config))->generate();

        $index = file_get_contents($out . '/index.html');
        $this->assertStringContainsString('id="group-Core"', $index);
        $this->assertStringNotContainsString('id="group-ValueObjects"', $index);
        $this->assertFileExists($out . '/Core/SemanticVersion.html');
    }
}
