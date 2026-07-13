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

namespace Milpa\Docs;

/**
 * Branding/grouping config {@see SiteGenerator} and {@see Shell} render from, instead of the
 * "Milpa Core" strings they used to hardcode. Every field defaults to today's core-branded
 * values, so `new SiteConfig()` (or omitting the constructor argument entirely — both
 * `SiteGenerator` and `Shell` default to it) reproduces the exact output core has always
 * generated: this is additive, not a rebrand.
 *
 * Every other package in the family (`milpa/http`, `milpa/mercure`, `milpa/tool-runtime`, …)
 * used to get its own docs site by post-processing core's hardcoded-"Milpa Core" HTML with a
 * `strtr()` rebrand map in its own `tools/gen-docs.php` (brand name, install snippet, repo/pages
 * URLs, hero prose, `utm_content` — six-plus string-replace pairs, repeated near-verbatim across
 * ~10 entry scripts). That `strtr` map still works unmodified — this config is the seam a
 * package's `tools/gen-docs.php` can migrate to instead, passing structured config in rather
 * than rewriting generated HTML after the fact. Migrating the existing ~10 entries is a
 * separate, later pass (out of scope here): this ships the seam, not the migration.
 */
final class SiteConfig
{
    public function __construct(
        /** Product name shown in the `<title>`, the index page `<h1>`, and the index title. */
        public readonly string $brand = 'Milpa Core',
        /** Namespace prefix stripped when computing a type's nav/index group (see `SiteGenerator::groupOf()`); types outside this prefix fall into a single "Core" group. */
        public readonly string $nsPrefix = 'Milpa\\',
        /** Install snippet shown in the index page's code block. */
        public readonly string $installCommand = 'composer require milpa/core',
        /** GitHub repo URL — the topbar's GitHub icon link. */
        public readonly string $repoUrl = 'https://github.com/getmilpa/core',
        /** Docs site / marketing URL — the topbar brand link. */
        public readonly string $pagesUrl = 'https://getmilpa.github.io/core/',
        /** Inner HTML of the index page's lead paragraph (no wrapping `<p>`). */
        public readonly string $heroParagraph = 'The framework-agnostic <strong>contracts core</strong> of Milpa — a modular PHP runtime for '
            . 'applications operable by <strong>both humans and agents</strong>. No ORM, no HTTP client, no kernel: '
            . 'just the primitives every Milpa module builds on.',
        /** `utm_content` value on the footer's "Developed by TeamX Agency" credit link. */
        public readonly string $utmContent = 'core',
    ) {
    }

    /**
     * Kebab-case slug derived from {@see self::$brand} (e.g. "Milpa Core" -> "milpa-core"),
     * used as the index page `<h1>` anchor id.
     */
    public function brandSlug(): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $this->brand) ?? $this->brand;

        return strtolower(trim($slug, '-'));
    }
}
