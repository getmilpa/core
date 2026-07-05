<?php

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
    private const ROOT_NAMESPACE_PREFIX = 'Milpa\\app\\';

    public function __construct(
        private readonly string $srcRoot,
        private readonly string $outDir,
        private readonly string $cssBase,
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

        $shell = new Shell($this->cssBase);
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

    /** Namespace segment right after `Milpa\app\` (e.g. `ValueObjects`, `Events`), used for grouping/pathing. */
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
        $mainHtml = $renderer->type($rc);
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
        $html = '<h1 class="mui-api__name">Milpa Core API</h1>';
        foreach ($groups as $group => $typesInGroup) {
            $html .= '<h2 class="mui-api__section">' . self::esc($group) . '</h2><ul class="mui-toc__list">';
            foreach ($typesInGroup as $rc) {
                $href = $group . '/' . $rc->getShortName() . '.html';
                $html .= '<li class="mui-toc__item"><a class="mui-toc__link" href="' . self::esc($href) . '">'
                    . self::esc($rc->getShortName()) . '</a></li>';
            }
            $html .= '</ul>';
        }

        return $html;
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
