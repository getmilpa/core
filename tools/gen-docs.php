<?php

declare(strict_types=1);

/**
 * Generates the static API reference site for Milpa Core (live-docs Phase 2).
 *
 * Reflects over `src/`, renders one `mui-api`-styled page per public type
 * (plus its own public methods) wrapped in the `mui-docs` shell, a
 * namespace-grouped nav, a per-page table of contents, and `index.html`.
 *
 * Usage: php tools/gen-docs.php --out <dir> [--css-base <url>]
 */

require dirname(__DIR__) . '/vendor/autoload.php';

// Required-value long options (`name:`, not `name::`) so `--css-base /ds` with a
// space is captured; optional (`::`) only binds `--css-base=/ds`. getopt yields
// `false` for a flag it can't bind a value to, so guard with is_string, not `??`
// (which only rescues null) before falling back to the default.
$opts = getopt('', ['out:', 'css-base:']);
$out = is_string($opts['out'] ?? null) ? $opts['out'] : 'build/docs';
$cssBase = is_string($opts['css-base'] ?? null) ? $opts['css-base'] : 'https://cdn.jsdelivr.net/npm/@milpa/design@0.3.0';

$count = (new Milpa\Docs\SiteGenerator(dirname(__DIR__) . '/src', $out, $cssBase))->generate();
echo "generated {$count} page(s) to {$out} (css-base: {$cssBase})\n";
exit(0);
