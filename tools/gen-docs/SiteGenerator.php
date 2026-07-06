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

namespace Milpa\Docs;

/**
 * Orchestrates reflection over the core `src/` tree to emit a static API
 * reference site: one `mui-api`-styled page per type (class/interface/trait/
 * enum) plus its own public methods, wrapped in the `mui-docs` shell, a
 * namespace-grouped nav shared by every page, a per-page table of contents,
 * and an `index.html` listing every type by namespace group.
 *
 * Discovery mirrors `tools/validate-docblocks.php`: scan every `.php` file
 * under `$srcRoot`, pull the namespace + type name via regex, and confirm it
 * via `class_exists()`/`interface_exists()`/`trait_exists()`/`enum_exists()`
 * so only autoloadable, declared types are reflected.
 */
final class SiteGenerator
{
    private const ROOT_NAMESPACE_PREFIX = 'Milpa\\';

    public function __construct(
        private readonly string $srcRoot,
        private readonly string $outDir,
        private readonly string $cssBase,
        private readonly string $version,
    ) {
    }

    /**
     * Generate the full site and return the number of HTML pages written.
     */
    public function generate(): int
    {
        $types = $this->discoverTypes();

        $groups = [];
        foreach ($types as $fqcn => $rc) {
            $groups[$this->groupOf($rc)][] = $rc;
        }
        ksort($groups);
        foreach ($groups as &$typesInGroup) {
            usort($typesInGroup, static fn (\ReflectionClass $a, \ReflectionClass $b): int => $a->getShortName() <=> $b->getShortName());
        }
        unset($typesInGroup);

        $shell = new Shell($this->cssBase, $this->version);
        $renderer = new ApiRenderer();

        $navForRoot = $this->buildNav($groups, '');
        $navForType = $this->buildNav($groups, '../');

        $this->ensureDir($this->outDir);

        $count = 0;
        foreach ($groups as $group => $typesInGroup) {
            $groupDir = $this->outDir . '/' . $group;
            $this->ensureDir($groupDir);

            foreach ($typesInGroup as $rc) {
                [$mainHtml, $tocHtml] = $this->renderTypePage($renderer, $rc);
                $page = $shell->page($rc->getShortName(), $navForType, $mainHtml, $tocHtml);
                file_put_contents($groupDir . '/' . $rc->getShortName() . '.html', $page);
                $count++;
            }
        }

        $indexPage = $shell->page('Milpa Core API', $navForRoot, $this->buildIndexMain($groups), '');
        file_put_contents($this->outDir . '/index.html', $indexPage);
        $count++;

        return $count;
    }

    /**
     * @return array<string, \ReflectionClass<object>> map of FQCN => reflection, discovered the
     *     same way `tools/validate-docblocks.php` does (file-scan + anchored regex + reflection).
     */
    private function discoverTypes(): array
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->srcRoot, \FilesystemIterator::SKIP_DOTS));

        $types = [];
        foreach ($it as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $src = file_get_contents($file->getPathname());
            if ($src === false
                || !preg_match('/namespace\s+([^;]+);/', $src, $ns)
                || !preg_match('/^\s*(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|enum|trait)\s+(\w+)/m', $src, $cm)) {
                continue;
            }
            $fqcn = trim($ns[1]) . '\\' . $cm[1];
            if (!class_exists($fqcn) && !interface_exists($fqcn) && !trait_exists($fqcn) && !enum_exists($fqcn)) {
                continue;
            }

            $types[$fqcn] = new \ReflectionClass($fqcn);
        }

        ksort($types);

        return $types;
    }

    /** Namespace segment right after `Milpa\` (e.g. `ValueObjects`, `Events`), used for grouping/pathing. */
    private function groupOf(\ReflectionClass $rc): string
    {
        $ns = $rc->getNamespaceName();
        if (str_starts_with($ns, self::ROOT_NAMESPACE_PREFIX)) {
            $tail = substr($ns, strlen(self::ROOT_NAMESPACE_PREFIX));
            $segment = explode('\\', $tail, 2)[0];
            if ($segment !== '') {
                return $segment;
            }
        }

        return 'Core';
    }

    /**
     * @param array<string, list<\ReflectionClass<object>>> $groups
     */
    private function buildNav(array $groups, string $prefix): string
    {
        $html = '';
        foreach ($groups as $group => $typesInGroup) {
            $html .= '<div class="mui-docs__nav-group">';
            $html .= '<p class="mui-docs__nav-heading">' . self::esc($group) . '</p>';
            foreach ($typesInGroup as $rc) {
                $href = $prefix . $group . '/' . $rc->getShortName() . '.html';
                $html .= '<a class="mui-docs__nav-item" href="' . self::esc($href) . '">' . self::esc($rc->getShortName()) . '</a>';
            }
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * @return array{0: string, 1: string} [mainHtml, tocHtml]
     */
    private function renderTypePage(ApiRenderer $renderer, \ReflectionClass $rc): array
    {
        $mainHtml = $this->breadcrumb($rc) . $renderer->type($rc);
        $tocItems = [['#' . self::slugify($rc->getShortName()), $rc->getShortName()]];

        foreach ($rc->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->getDeclaringClass()->getName() !== $rc->getName()) {
                continue; // inherited — belongs to its declaring type's own page
            }
            $mainHtml .= $renderer->method($m);
            $id = self::slugify($rc->getShortName() . '-' . $m->getName());
            $tocItems[] = ['#' . $id, $rc->getShortName() . '::' . $m->getName() . '()'];
        }

        return [$mainHtml, $this->buildToc($tocItems)];
    }

    /**
     * @param list<array{0: string, 1: string}> $items [href, label] pairs
     */
    private function buildToc(array $items): string
    {
        $lis = '';
        foreach ($items as [$href, $label]) {
            $lis .= '<li class="mui-toc__item"><a class="mui-toc__link" href="' . self::esc($href) . '">' . self::esc($label) . '</a></li>';
        }

        return '<nav class="mui-toc" aria-label="On this page"><ul class="mui-toc__list">' . $lis . '</ul></nav>';
    }

    /**
     * @param array<string, list<\ReflectionClass<object>>> $groups
     */
    private function buildIndexMain(array $groups): string
    {
        $totalTypes = array_sum(array_map('count', $groups));

        // Hero: what Milpa Core is, in a breath, plus the live contract loop.
        $html = '<article class="mui-prose docs-gap">'
            . '<h1 id="milpa-core">Milpa Core <span class="mui-badge mui-badge--accent">v' . self::esc($this->version) . '</span></h1>'
            . '<p>The framework-agnostic <strong>contracts core</strong> of Milpa — a modular PHP runtime for '
            . 'applications operable by <strong>both humans and agents</strong>. No ORM, no HTTP client, no kernel: '
            . 'just the primitives every Milpa module builds on.</p>'
            . '<p>Modules declare the <em>capabilities</em> they provide and require, expose <em>tools</em> an agent '
            . 'can invoke, and gate mutating actions behind <em>verification</em>. The contract loop that runs through '
            . 'the whole system:</p>'
            . '<p><code>plugin &rarr; capability &rarr; tool &rarr; verification &rarr; event &rarr; result</code></p>'
            . '</article>';

        // Install snippet, dressed as a design-system code block.
        $html .= '<div class="mui-code docs-gap"><div class="mui-code__header">'
            . '<span class="mui-code__file">install</span><span class="mui-code__lang">bash</span></div>'
            . '<div class="mui-code__body"><pre><code>composer require milpa/core</code></pre></div></div>';

        // API index — grouped by namespace, each group anchored so breadcrumbs can deep-link.
        $html .= '<article class="mui-prose docs-gap"><h2 id="api-reference">API reference</h2>'
            . '<p>' . $totalTypes . ' public types across ' . count($groups) . ' namespaces — every symbol carries a '
            . 'DocBlock, and this reference is generated straight from them.</p></article>';

        foreach ($groups as $group => $typesInGroup) {
            $html .= '<section class="docs-gap" aria-labelledby="group-' . self::esc($group) . '">'
                . '<h3 class="mui-api__section" id="group-' . self::esc($group) . '">'
                . self::esc($group) . ' <span class="mui-badge">' . count($typesInGroup) . '</span></h3>'
                . '<ul class="mui-toc__list">';
            foreach ($typesInGroup as $rc) {
                $href = $group . '/' . $rc->getShortName() . '.html';
                $html .= '<li class="mui-toc__item"><a class="mui-toc__link" href="' . self::esc($href) . '">'
                    . self::esc($rc->getShortName()) . '</a></li>';
            }
            $html .= '</ul></section>';
        }

        return $html;
    }

    /** Breadcrumb trail for a type page: Docs / &lt;group&gt; / &lt;TypeName&gt;. */
    private function breadcrumb(\ReflectionClass $rc): string
    {
        $group = $this->groupOf($rc);

        return '<nav class="mui-breadcrumbs docs-gap" aria-label="Breadcrumb">'
            . '<ol class="mui-breadcrumbs__list">'
            . '<li class="mui-breadcrumbs__item"><a class="mui-breadcrumbs__link" href="../index.html">Docs</a></li>'
            . '<li class="mui-breadcrumbs__item"><a class="mui-breadcrumbs__link" href="../index.html#group-'
            . self::esc($group) . '">' . self::esc($group) . '</a></li>'
            . '<li class="mui-breadcrumbs__item"><span aria-current="page">' . self::esc($rc->getShortName()) . '</span></li>'
            . '</ol></nav>';
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /** Mirrors `ApiRenderer`'s private slugify so toc anchors match its `.mui-api__name` ids exactly. */
    private static function slugify(string $s): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $s) ?? $s;

        return 'api-' . strtolower(trim($slug, '-'));
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
