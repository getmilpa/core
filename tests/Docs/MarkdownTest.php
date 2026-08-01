<?php

/**
 * This file is part of Milpa Core — the framework-agnostic contracts core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Docs;

use Milpa\Docs\Markdown;
use PHPUnit\Framework\TestCase;

/**
 * El subconjunto de Markdown de las páginas narrativas (D14 fase 3).
 *
 * Lo que estas pruebas cuidan no es que el HTML sea bonito: es que **nada del documento desaparezca
 * en silencio**, y que lo que va dentro de un bloque de código salga tal cual — el fallo clásico de
 * un renderizador hecho a mano es convertir un asterisco de código en cursiva.
 */
final class MarkdownTest extends TestCase
{
    public function test_un_bloque_de_codigo_sale_verbatim(): void
    {
        $md = "```php\n\$x = 1 * 2;\n__no_cursiva__\n```";
        $html = (new Markdown())->render($md);

        self::assertStringContainsString('$x = 1 * 2;', $html);
        self::assertStringContainsString('__no_cursiva__', $html);
        self::assertStringNotContainsString('<em>', $html, 'lo de adentro de un code fence no se interpreta');
    }

    public function test_un_asterisco_dentro_de_codigo_en_linea_no_es_cursiva(): void
    {
        $html = (new Markdown())->render('Usa `array_map(*)` con cuidado y *esto* sí va en cursiva.');

        self::assertStringContainsString('<code>array_map(*)</code>', $html);
        self::assertStringContainsString('<em>esto</em>', $html);
    }

    public function test_los_encabezados_llevan_ancla_transliterada(): void
    {
        $m = new Markdown();
        $html = $m->render("# Título con acentos\n\n## Sección ñ");

        self::assertStringContainsString('<h1 id="titulo-con-acentos">', $html);
        self::assertStringContainsString('<h2 id="seccion-n">', $html);
        self::assertSame(
            [['titulo-con-acentos', 'Título con acentos'], ['seccion-n', 'Sección ñ']],
            $m->headings(),
            'los encabezados se exportan para armar la tabla de contenido'
        );
    }

    public function test_html_del_documento_queda_escapado(): void
    {
        $html = (new Markdown())->render('Un <script>alert(1)</script> en el texto.');

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_tablas_listas_citas_y_reglas(): void
    {
        $md = <<<'MD'
            | campo | qué es |
            |---|---|
            | `id` | el identificador |

            - uno
            - dos

            1. primero
            2. segundo

            > una nota

            ---
            MD;
        $html = (new Markdown())->render($md);

        self::assertStringContainsString('<th>campo</th>', $html);
        self::assertStringContainsString('<td><code>id</code></td>', $html);
        self::assertStringContainsString('<ul', $html);
        self::assertStringContainsString('<ol', $html);
        self::assertStringContainsString('<blockquote', $html);
        self::assertStringContainsString('<hr', $html);
    }

    /**
     * El contador es la única forma de distinguir «se renderizó» de «se degradó a párrafo». Un
     * documento del subconjunto tiene que dar cero: si diera más, el renderizador estaría
     * silenciosamente convirtiendo estructura en prosa.
     */
    public function test_un_documento_del_subconjunto_no_deja_nada_sin_clasificar(): void
    {
        $m = new Markdown();
        $m->render("# T\n\nPárrafo con **negrita**.\n\n- item\n\n```\ncode\n```\n");

        self::assertSame(0, $m->unclassified());
    }
}
