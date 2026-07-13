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

use Milpa\Docs\SiteConfig;
use PHPUnit\Framework\TestCase;

final class SiteConfigTest extends TestCase
{
    public function testDefaultsMatchTodaysHardcodedCoreBranding(): void
    {
        $config = new SiteConfig();

        $this->assertSame('Milpa Core', $config->brand);
        $this->assertSame('Milpa\\', $config->nsPrefix);
        $this->assertSame('composer require milpa/core', $config->installCommand);
        $this->assertSame('https://github.com/getmilpa/core', $config->repoUrl);
        $this->assertSame('https://getmilpa.github.io/core/', $config->pagesUrl);
        $this->assertSame('core', $config->utmContent);
        $this->assertStringContainsString('framework-agnostic', $config->heroParagraph);
        $this->assertStringContainsString('modular PHP runtime', $config->heroParagraph);
    }

    public function testBrandSlugDerivesKebabCaseIdFromBrand(): void
    {
        $this->assertSame('milpa-core', (new SiteConfig())->brandSlug());
        $this->assertSame('milpa-fake-brand', (new SiteConfig(brand: 'Milpa Fake Brand'))->brandSlug());
    }

    public function testCustomValuesOverrideEveryDefault(): void
    {
        $config = new SiteConfig(
            brand: 'Milpa Fake',
            nsPrefix: 'Fake\\',
            installCommand: 'composer require milpa/fake',
            repoUrl: 'https://github.com/getmilpa/fake',
            pagesUrl: 'https://getmilpa.github.io/fake/',
            heroParagraph: 'A fake package for testing.',
            utmContent: 'fake',
        );

        $this->assertSame('Milpa Fake', $config->brand);
        $this->assertSame('Fake\\', $config->nsPrefix);
        $this->assertSame('composer require milpa/fake', $config->installCommand);
        $this->assertSame('https://github.com/getmilpa/fake', $config->repoUrl);
        $this->assertSame('https://getmilpa.github.io/fake/', $config->pagesUrl);
        $this->assertSame('A fake package for testing.', $config->heroParagraph);
        $this->assertSame('fake', $config->utmContent);
    }
}
