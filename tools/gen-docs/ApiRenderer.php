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
 * Renders a `.mui-api` entry (per `@milpa/design`'s `milpa-api.contract.json`)
 * for a type (class/interface) or a method, from reflection + its parsed
 * `DocBlock`. Pure display: no state, no I/O — just HTML string building.
 */
final class ApiRenderer
{
    public function type(\ReflectionClass $rc): string
    {
        $doc = DocBlock::of($rc->getDocComment());
        $name = $rc->getShortName();
        $id = 'api-' . self::slugify($name);

        $html = '<section class="mui-api" aria-labelledby="' . self::esc($id) . '">';
        $html .= '<div class="mui-api__header">';
        $html .= '<h1 class="mui-api__name" id="' . self::esc($id) . '">' . self::esc($name) . '</h1>';
        $html .= self::metaHtml($doc);
        $html .= '</div>';

        $html .= self::descHtml($doc);
        $html .= self::deprecatedNoteHtml($doc);

        return $html . '</section>';
    }

    public function method(\ReflectionMethod $m): string
    {
        $doc = DocBlock::of($m->getDocComment());
        $class = $m->getDeclaringClass()->getShortName();
        $methodName = $m->getName();
        $id = 'api-' . self::slugify($class . '-' . $methodName);

        $html = '<section class="mui-api" aria-labelledby="' . self::esc($id) . '">';
        $html .= '<div class="mui-api__header">';
        $html .= '<h2 class="mui-api__name" id="' . self::esc($id) . '">'
            . self::esc($class . '::' . $methodName . '()') . '</h2>';
        $html .= self::metaHtml($doc);
        $html .= '</div>';

        $html .= '<pre class="mui-api__signature"><code>' . Signature::of($m) . '</code></pre>';

        $html .= self::descHtml($doc);
        $html .= self::deprecatedNoteHtml($doc);

        if ($m->getParameters() !== []) {
            $html .= '<h3 class="mui-api__section">Parameters</h3>';
            $html .= self::paramsTableHtml($doc, $m, $methodName);
        }

        if ($doc->return !== null && $doc->return['desc'] !== '') {
            $html .= '<h3 class="mui-api__section">Returns</h3>';
            $html .= '<p class="mui-api__desc">' . self::esc($doc->return['desc']) . '</p>';
        }

        if ($doc->throws !== []) {
            $html .= '<h3 class="mui-api__section">Throws</h3>';
            foreach ($doc->throws as $t) {
                $descSuffix = $t['desc'] !== '' ? ' ' . self::esc($t['desc']) : '';
                $html .= '<p class="mui-api__desc"><code class="mui-api__type">' . self::esc($t['type']) . '</code>'
                    . $descSuffix . '</p>';
            }
        }

        return $html . '</section>';
    }

    private static function metaHtml(DocBlock $doc): string
    {
        $badges = '';
        if ($doc->since !== null) {
            $version = ltrim($doc->since, 'vV ');
            $badges .= '<span class="mui-badge mui-badge--since">' . self::esc('Since v' . $version) . '</span>';
        }
        if ($doc->deprecated !== null) {
            [$version] = self::splitDeprecated($doc->deprecated);
            $label = $version !== null ? 'Deprecated in v' . $version : 'Deprecated';
            $badges .= '<span class="mui-badge mui-badge--deprecated">' . self::esc($label) . '</span>';
        }

        return $badges === '' ? '' : '<div class="mui-api__meta">' . $badges . '</div>';
    }

    private static function deprecatedNoteHtml(DocBlock $doc): string
    {
        if ($doc->deprecated === null) {
            return '';
        }

        [, $note] = self::splitDeprecated($doc->deprecated);

        return '<p class="mui-api__deprecated-note"><strong>Deprecated:</strong> ' . self::esc($note) . '</p>';
    }

    private static function descHtml(DocBlock $doc): string
    {
        $text = trim($doc->summary . ($doc->description !== '' ? ' ' . $doc->description : ''));

        return $text === '' ? '' : '<p class="mui-api__desc">' . self::esc($text) . '</p>';
    }

    private static function paramsTableHtml(DocBlock $doc, \ReflectionMethod $m, string $methodName): string
    {
        $docByName = [];
        foreach ($doc->params as $p) {
            // $p['name'] already carries the leading '$', e.g. "$path"
            $docByName[ltrim($p['name'], '$')] = $p;
        }

        // Row existence + order come from the real parameter list (reflection),
        // not from @param tags — most methods have partial or no @param coverage.
        $rows = '';
        foreach ($m->getParameters() as $rp) {
            $bareName = $rp->getName();
            $docParam = $docByName[$bareName] ?? null;

            $type = $docParam['type'] ?? '';
            if ($type === '') {
                $type = self::reflectionTypeToString($rp->getType());
            }
            $desc = $docParam['desc'] ?? '';

            $rows .= '<tr><td class="mui-table__lead">' . self::esc('$' . $bareName) . '</td>'
                . '<td><code class="mui-api__type">' . self::esc($type) . '</code></td>'
                . '<td>' . self::esc($desc) . '</td></tr>';
        }

        return '<div class="mui-table-wrap mui-api__params"><table class="mui-table">'
            . '<caption class="mui-sr-only">' . self::esc('Parameters of ' . $methodName . '()') . '</caption>'
            . '<thead><tr><th>Name</th><th>Type</th><th>Description</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody></table></div>';
    }

    /**
     * @return array{0: ?string, 1: string} [version|null, remaining note text]
     */
    private static function splitDeprecated(string $raw): array
    {
        // Only a dotted version number (e.g. "1.2", "v2.0.1") is treated as a
        // version; a bare integer or plain prose stays entirely in the note
        // (avoids false positives like "@deprecated 10 years is too long...").
        if (preg_match('/^v?(\d+\.\d+(?:\.\d+)*)\s+(.*)$/s', $raw, $matches) === 1) {
            return [$matches[1], trim($matches[2])];
        }

        return [null, $raw];
    }

    private static function reflectionTypeToString(?\ReflectionType $type): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof \ReflectionUnionType) {
            return implode('|', array_map(
                static fn (\ReflectionType $t): string => self::reflectionTypeToString($t),
                $type->getTypes()
            ));
        }

        if ($type instanceof \ReflectionIntersectionType) {
            return implode('&', array_map(
                static fn (\ReflectionType $t): string => self::reflectionTypeToString($t),
                $type->getTypes()
            ));
        }

        /** @var \ReflectionNamedType $type */
        $name = $type->getName();
        if ($type->allowsNull() && strtolower($name) !== 'null') {
            return '?' . $name;
        }

        return $name;
    }

    private static function slugify(string $s): string
    {
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $s) ?? $s;

        return strtolower(trim($slug, '-'));
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
