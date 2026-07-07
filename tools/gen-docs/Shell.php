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

namespace Milpa\Docs;

/**
 * The `mui-docs` page wrapper (per `@milpa/design`'s `layouts/milpa-docs.contract.json` +
 * `proof/docs.html`): dresses a rendered nav / main / toc fragment with the shell's `<head>`,
 * topbar, 3-col grid and footer. Reference scope only (Phase 2) — the proof's search dialog and
 * interactive version-switcher menu are intentionally dropped, along with the JS they need; only the
 * theme-toggle behavior ships, plus a non-interactive version indicator threaded from the caller.
 */
final class Shell
{
    public function __construct(private readonly string $cssBase, private readonly string $version)
    {
    }

    public function page(string $title, string $navHtml, string $mainHtml, string $tocHtml): string
    {
        $base = rtrim($this->cssBase, '/');

        return '<!doctype html>'
            . '<html lang="en" data-theme="dark">'
            . $this->head($base, $title)
            . '<body>'
            . '<a class="mui-shell__skip" href="#main">Skip to content</a>'
            . $this->wordmarkSymbol()
            . $this->topbar()
            . '<div class="mui-docs">'
            . '<nav class="mui-docs__nav" id="docs-nav" aria-label="Documentation">' . $navHtml . '</nav>'
            . '<main class="mui-docs__main" id="main" tabindex="-1">' . $mainHtml . '</main>'
            . '<aside class="mui-docs__aside">' . $tocHtml . '</aside>'
            . '</div>'
            . $this->footer()
            . $this->themeToggleScript()
            . '</body>'
            . '</html>';
    }

    private function head(string $base, string $title): string
    {
        return '<head>'
            . '<meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . self::esc($title) . ' · Milpa Core v' . self::esc($this->version) . '</title>'
            . '<meta name="description" content="Milpa Core API reference, generated from source.">'
            . '<link rel="preconnect" href="https://fonts.googleapis.com">'
            . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            . '<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">'
            . '<link rel="icon" type="image/svg+xml" href="' . self::esc($base) . '/logo/symbol/milpa-symbol-color.svg">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/dist/milpa-tokens.css">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/motion/milpa-motion.css">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/primitives/milpa-primitives.css">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/components/milpa-components.css">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/artifacts/milpa-artifacts.css">'
            . '<link rel="stylesheet" href="' . self::esc($base) . '/layouts/milpa-layouts.css">'
            . '<style>'
            . '/* ===== page styles (docs-*) — the system does the rest; all tokens ===== */'
            . 'body { margin:0; background:var(--bg); color:var(--text); font-family:var(--font-display); font-size:var(--text-base); line-height:var(--leading-normal); -webkit-font-smoothing:antialiased; }'
            . '.docs-wordmark { height:1rem; width:auto; display:block; flex:none; }'
            . '.docs-theme-toggle { font-family:var(--font-mono); }'
            . '.docs-footer { display:flex; flex-wrap:wrap; align-items:center; gap:var(--space-2) var(--space-3); max-width:90rem; margin-inline:auto; padding:var(--space-6) clamp(var(--space-4),3vw,var(--space-8)); border-block-start:1px solid var(--border-subtle); color:var(--text-muted); font-size:var(--text-sm); }'
            . '.docs-footer__tagline { margin:0; font-family:var(--font-mono); font-size:var(--text-xs); }'
            . '.docs-footer__credit { margin:0; font-size:var(--text-xs); }'
            . '.docs-footer__credit a { color:inherit; text-decoration:underline; text-underline-offset:2px; text-decoration-color:var(--border-strong); }'
            . '.docs-footer__credit a:hover { color:var(--text); text-decoration-color:currentColor; }'
            . '.docs-footer__legal { margin:0; margin-inline-start:auto; font-size:var(--text-xs); }'
            . '.docs-gap { margin-block-end:var(--space-6); }'
            // Subtle, occasional logo life: the plot squares twinkle once per ~16s cycle,
            // lightly staggered, and only when the viewer hasn't asked for reduced motion.
            . '@media (prefers-reduced-motion: no-preference){'
            . '.docs-plot rect{animation:docs-plot-twinkle 16s ease-in-out infinite}'
            . '.docs-plot rect:nth-child(2n){animation-delay:.4s}'
            . '.docs-plot rect:nth-child(3n){animation-delay:.9s}'
            . '.docs-plot rect:nth-child(5n){animation-delay:1.3s}'
            . '}'
            . '@keyframes docs-plot-twinkle{0%,80%,100%{opacity:1}87%{opacity:.35}93%{opacity:1}}'
            . '</style>'
            . '</head>';
    }

    /** The wordmark vector (official kit snippet — letters currentColor, grain oro-300 constant). */
    private function wordmarkSymbol(): string
    {
        return '<svg style="display:none" aria-hidden="true"><symbol id="wm-milpa" viewBox="0 0 2406.90 900.00"><g fill="currentColor"><path d="M76.299560546875 0V492.500244140625H177.201171875V435.00048828125H193.101318359375Q206.80126953125 460.700439453125 237.8511962890625 480.9503173828125Q268.901123046875 501.2001953125 322.100830078125 501.2001953125Q377.20068359375 501.2001953125 410.77557373046875 478.42535400390625Q444.3504638671875 455.6505126953125 461.00048828125 420.900634765625H476.900634765625Q493.900634765625 455.00048828125 526.6505126953125 478.100341796875Q559.400390625 501.2001953125 619.2001953125 501.2001953125Q666.60009765625 501.2001953125 703.9251098632812 481.3502197265625Q741.2501220703125 461.500244140625 763.2251586914062 423.0501708984375Q785.2001953125 384.60009765625 785.2001953125 328.4498291015625V0H682.298583984375V320.499755859375Q682.298583984375 365.3494873046875 657.6238403320312 389.69921875Q632.9490966796875 414.0489501953125 588.299560546875 414.0489501953125Q540.0499267578125 414.0489501953125 511.12530517578125 383.0491943359375Q482.20068359375 352.0494384765625 482.20068359375 293.699951171875V0H379.299072265625V320.499755859375Q379.299072265625 365.3494873046875 354.62432861328125 389.69921875Q329.9495849609375 414.0489501953125 285.300048828125 414.0489501953125Q237.0504150390625 414.0489501953125 208.12579345703125 383.0491943359375Q179.201171875 352.0494384765625 179.201171875 293.699951171875V0Z" transform="translate(-76.30,700.00) scale(1,-1)"/><path d="M76.299560546875 0V492.500244140625H179.201171875V0Z" transform="translate(760.70,700.00) scale(1,-1)"/><path d="M76.299560546875 0V700H179.201171875V0Z" transform="translate(996.70,700.00) scale(1,-1)"/><path d="M76.299560546875 -200V492.500244140625H177.201171875V422.3509521484375H193.101318359375Q211.501220703125 455.20068359375 250.47601318359375 480.8504638671875Q289.4508056640625 506.500244140625 361.00048828125 506.500244140625Q422.7503662109375 506.500244140625 474.4503173828125 476.6502685546875Q526.1502685546875 446.80029296875 557.3252563476562 390.1502685546875Q588.500244140625 333.500244140625 588.500244140625 253.5501708984375V238.9500732421875Q588.500244140625 159.3499755859375 557.500244140625 102.52496337890625Q526.500244140625 45.699951171875 474.80029296875 15.8499755859375Q423.100341796875 -14 361.00048828125 -14Q313.20068359375 -14 280.2508544921875 -2.10009765625Q247.301025390625 9.7998046875 227.0511474609375 28.57464599609375Q206.80126953125 47.3494873046875 195.101318359375 66.79931640625H179.201171875V-200ZM331.39990234375 76.4013671875Q398.79931640625 76.4013671875 441.8489990234375 119.32598876953125Q484.898681640625 162.2506103515625 484.898681640625 241.60009765625V250.900146484375Q484.898681640625 330.2496337890625 441.52398681640625 373.17425537109375Q398.1492919921875 416.098876953125 331.39990234375 416.098876953125Q265.00048828125 416.098876953125 221.4508056640625 373.17425537109375Q177.901123046875 330.2496337890625 177.901123046875 250.900146484375V241.60009765625Q177.901123046875 162.2506103515625 221.4508056640625 119.32598876953125Q265.00048828125 76.4013671875 331.39990234375 76.4013671875Z" transform="translate(1232.70,700.00) scale(1,-1)"/><path d="M228.5496826171875 -14Q176.2496337890625 -14 134.42462158203125 4.1500244140625Q92.599609375 22.300048828125 68.27459716796875 57.27508544921875Q43.9495849609375 92.2501220703125 43.9495849609375 142.2001953125Q43.9495849609375 192.500244140625 68.27459716796875 226.12530517578125Q92.599609375 259.7503662109375 135.22467041015625 276.72540283203125Q177.8497314453125 293.700439453125 231.7498779296875 293.700439453125H382.4488525390625V325.5501708984375Q382.4488525390625 368.9495849609375 356.2491455078125 395.12432861328125Q330.0494384765625 421.299072265625 276.099853515625 421.299072265625Q223.1502685546875 421.299072265625 195.07562255859375 396.29931640625Q167.0009765625 371.299560546875 157.901123046875 330.6500244140625L62.19970703125 361.9505615234375Q74.19970703125 401.700439453125 100.52471923828125 434.2503662109375Q126.8497314453125 466.80029296875 170.82476806640625 486.6502685546875Q214.7998046875 506.500244140625 277.39990234375 506.500244140625Q373.300048828125 506.500244140625 428.02520751953125 457.8502197265625Q482.7503662109375 409.2001953125 482.7503662109375 318.6500244140625V115.80126953125Q482.7503662109375 85.80126953125 510.7503662109375 85.80126953125H553.2001953125V0H476.19970703125Q441.299560546875 0 419.3994140625 18.17498779296875Q397.499267578125 36.3499755859375 397.499267578125 67.0499267578125V70.1497802734375H381.6490478515625Q374.499267578125 54.6500244140625 358.22442626953125 34.62506103515625Q341.9495849609375 14.60009765625 310.899658203125 0.300048828125Q279.8497314453125 -14 228.5496826171875 -14ZM244.2501220703125 71.201171875Q305.6495361328125 71.201171875 344.0491943359375 106.6507568359375Q382.4488525390625 142.100341796875 382.4488525390625 203.699462890625V214.3994140625H237.9500732421875Q197.00048828125 214.3994140625 171.92584228515625 196.77459716796875Q146.8511962890625 179.1497802734375 146.8511962890625 145.1502685546875Q146.8511962890625 111.1507568359375 172.92584228515625 91.17596435546875Q199.00048828125 71.201171875 244.2501220703125 71.201171875Z" transform="translate(1853.70,700.00) scale(1,-1)"/></g><rect x="818.99" y="40.80" width="138.92" height="138.92" rx="34.73" fill="var(--oro-300)"/></symbol></svg>';
    }

    /** Topbar: brand + version indicator + theme-toggle + GitHub link (search / interactive switcher are P2 non-goals). */
    private function topbar(): string
    {
        return '<header class="mui-docs__topbar">'
            . '<a class="mui-docs__brand" href="https://getmilpa.github.io/core/">'
            . '<svg class="docs-plot" aria-hidden="true" width="20" height="20" viewBox="0 0 60 60">'
            . '<g fill="var(--oro-300)">'
            . '<rect x="0" y="0" width="10" height="10" rx="2.5"/><rect x="50" y="0" width="10" height="10" rx="2.5"/>'
            . '<rect x="0" y="12.5" width="10" height="10" rx="2.5"/><rect x="12.5" y="12.5" width="10" height="10" rx="2.5"/><rect x="37.5" y="12.5" width="10" height="10" rx="2.5"/><rect x="50" y="12.5" width="10" height="10" rx="2.5"/>'
            . '<rect x="0" y="25" width="10" height="10" rx="2.5"/><rect x="25" y="25" width="10" height="10" rx="2.5"/><rect x="50" y="25" width="10" height="10" rx="2.5"/>'
            . '<rect x="0" y="37.5" width="10" height="10" rx="2.5"/><rect x="50" y="37.5" width="10" height="10" rx="2.5"/>'
            . '<rect x="0" y="50" width="10" height="10" rx="2.5"/><rect x="50" y="50" width="10" height="10" rx="2.5"/>'
            . '</g>'
            . '</svg>'
            . '<svg class="docs-wordmark" viewBox="0 0 2406.90 900.00" role="img" aria-label="milpa"><use href="#wm-milpa"/></svg>'
            . '<span class="mui-badge">docs</span>'
            . '</a>'
            . '<span class="mui-version-switcher">v' . self::esc($this->version) . '</span>'
            . '<div class="mui-docs__topbar-actions">'
            . '<button type="button" class="mui-btn mui-btn--sm docs-theme-toggle" id="theme-toggle" aria-label="Toggle theme">◐ dark</button>'
            . '<a class="mui-btn mui-btn--ghost mui-btn--icon" href="https://github.com/getmilpa/core" aria-label="Milpa on GitHub">'
            . '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>'
            . '</a>'
            . '</div>'
            . '</header>';
    }

    private function footer(): string
    {
        return '<footer class="docs-footer">'
            . '<svg class="docs-wordmark" viewBox="0 0 2406.90 900.00" role="img" aria-label="milpa"><use href="#wm-milpa"/></svg>'
            . '<p class="docs-footer__tagline">Siembra módulos, cosecha aplicaciones.</p>'
            . '<p class="docs-footer__credit">Developed by <a href="https://teamx.agency/?utm_source=milpa-docs&utm_medium=footer&utm_campaign=milpa&utm_content=core">TeamX Agency</a></p>'
            . '<p class="docs-footer__legal">© 2026 Milpa · Apache-2.0 · docs built from v' . self::esc($this->version) . '</p>'
            . '</footer>';
    }

    /** Only the theme-toggle behavior ships in P2 — no drawer, no scroll-spy, no search JS. */
    private function themeToggleScript(): string
    {
        return '<script>'
            . "(() => {"
            . "'use strict';"
            . "const root = document.documentElement, themeBtn = document.getElementById('theme-toggle');"
            . 'const applyTheme = t => {'
            . 'root.dataset.theme = t;'
            . "themeBtn.textContent = '◐ ' + t;"
            . "try { localStorage.setItem('milpa-theme', t); } catch {}"
            . '};'
            . 'let saved = null;'
            . "try { saved = localStorage.getItem('milpa-theme'); } catch {}"
            . "applyTheme(saved === 'light' || saved === 'dark' ? saved : root.dataset.theme);"
            . "themeBtn.addEventListener('click', () => applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark'));"
            . '})();'
            . '</script>';
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
