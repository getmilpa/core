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

namespace Milpa\Docs;

/**
 * El subconjunto de Markdown que las páginas narrativas usan, renderizado sin dependencias.
 *
 * ── POR QUÉ NO SE USA UN PARSER DE VERDAD ───────────────────────────────────────────────────────
 *
 * Porque `gen-docs.php` corre en el CI de treinta repositorios publicados, y una dependencia de
 * desarrollo que falte en uno de ellos revienta el gate de docs **en el runner y en ningún otro
 * lado** — nadie ejecuta el generador a mano. Eso pasó dos veces el mismo día: `Milpa\Docs\SiteConfig`
 * y `phpstan/phpdoc-parser`, las dos veces con el mismo síntoma y en el mismo sitio.
 *
 * Un parser completo sería mejor Markdown. Cero dependencias es mejor generador.
 *
 * ── EL SUBCONJUNTO, DECLARADO ───────────────────────────────────────────────────────────────────
 *
 * Encabezados `#`…`####` · párrafos · bloques de código con acentos graves · código en línea ·
 * **negritas** · *cursivas* · enlaces · listas con y sin número · citas · reglas horizontales ·
 * tablas con pipe.
 *
 * Lo que NO soporta se degrada a párrafo con su texto escapado — y el generador **cuenta esas
 * líneas y las reporta**. Una línea que no se supo clasificar y sale como párrafo se ve idéntica a
 * una línea que era un párrafo; el contador es lo único que distingue «se renderizó» de «se
 * ignoró en silencio».
 */
final class Markdown
{
    /** @var list<array{0: string, 1: string}> encabezados encontrados: [ancla, texto] */
    private array $headings = [];

    private int $sinClasificar = 0;

    /** Convierte el documento a HTML. */
    public function render(string $md): string
    {
        $lineas = preg_split('/\R/', $md) ?: [];
        $html = [];
        $i = 0;
        $n = \count($lineas);

        while ($i < $n) {
            $linea = $lineas[$i];

            // Bloque de código: se copia VERBATIM. Nada de adentro se interpreta — es la única
            // región donde un asterisco o un guion bajo tienen que sobrevivir tal cual.
            if (preg_match('/^```\s*([A-Za-z0-9+#-]*)\s*$/', $linea, $m)) {
                $lang = $m[1];
                $cuerpo = [];
                ++$i;
                while ($i < $n && !preg_match('/^```\s*$/', $lineas[$i])) {
                    $cuerpo[] = $lineas[$i];
                    ++$i;
                }
                ++$i;   // la línea de cierre
                $clase = $lang !== '' ? ' class="language-' . self::esc($lang) . '"' : '';
                $html[] = '<pre class="mui-code"><code' . $clase . '>'
                    . self::esc(implode("\n", $cuerpo)) . '</code></pre>';
                continue;
            }

            if (trim($linea) === '') {
                ++$i;
                continue;
            }

            if (preg_match('/^(#{1,4})\s+(.*)$/', $linea, $m)) {
                $nivel = \strlen($m[1]);
                $texto = trim($m[2]);
                $ancla = self::slug($texto);
                $this->headings[] = [$ancla, $texto];
                $html[] = sprintf(
                    '<h%d id="%s">%s</h%d>',
                    $nivel,
                    self::esc($ancla),
                    $this->inline($texto),
                    $nivel
                );
                ++$i;
                continue;
            }

            if (preg_match('/^(-{3,}|\*{3,}|_{3,})\s*$/', $linea)) {
                $html[] = '<hr class="mui-docs__rule">';
                ++$i;
                continue;
            }

            // Tabla: la cabecera, la línea de guiones, y las filas.
            if (str_contains($linea, '|') && isset($lineas[$i + 1])
                && preg_match('/^\s*\|?[\s:|-]+\|[\s:|-]*$/', $lineas[$i + 1])) {
                $html[] = $this->tabla($lineas, $i, $n);
                continue;
            }

            if (preg_match('/^\s*([-*+]|\d+\.)\s+/', $linea)) {
                $html[] = $this->lista($lineas, $i, $n);
                continue;
            }

            if (preg_match('/^>\s?(.*)$/', $linea)) {
                $cuerpo = [];
                while ($i < $n && preg_match('/^>\s?(.*)$/', $lineas[$i], $m)) {
                    $cuerpo[] = $m[1];
                    ++$i;
                }
                $html[] = '<blockquote class="mui-docs__note">' . $this->inline(implode(' ', $cuerpo))
                    . '</blockquote>';
                continue;
            }

            // Párrafo: líneas consecutivas hasta la siguiente vacía o el siguiente bloque.
            $cuerpo = [];
            while ($i < $n && trim($lineas[$i]) !== ''
                   && !preg_match('/^(#{1,4}\s|```|>|\s*([-*+]|\d+\.)\s|-{3,}\s*$)/', $lineas[$i])) {
                $cuerpo[] = $lineas[$i];
                ++$i;
            }
            if ($cuerpo === []) {
                // Ninguna regla casó y tampoco es párrafo: se cuenta y se emite escapado, para que
                // el contenido no desaparezca sin que nadie se entere.
                ++$this->sinClasificar;
                $html[] = '<p>' . self::esc($lineas[$i]) . '</p>';
                ++$i;
                continue;
            }
            $html[] = '<p>' . $this->inline(implode(' ', $cuerpo)) . '</p>';
        }

        return implode("\n", $html);
    }

    /** @return list<array{0: string, 1: string}> los encabezados, para armar la tabla de contenido */
    public function headings(): array
    {
        return $this->headings;
    }

    /** Cuántas líneas no encajaron en ninguna regla del subconjunto. */
    public function unclassified(): int
    {
        return $this->sinClasificar;
    }

    /**
     * @param list<string> $lineas
     */
    private function lista(array $lineas, int &$i, int $n): string
    {
        $ordenada = (bool) preg_match('/^\s*\d+\.\s+/', $lineas[$i]);
        $items = [];
        while ($i < $n && preg_match('/^\s*([-*+]|\d+\.)\s+(.*)$/', $lineas[$i], $m)) {
            $items[] = '<li>' . $this->inline(trim($m[2])) . '</li>';
            ++$i;
        }
        $tag = $ordenada ? 'ol' : 'ul';

        return '<' . $tag . ' class="mui-docs__list">' . implode('', $items) . '</' . $tag . '>';
    }

    /**
     * @param list<string> $lineas
     */
    private function tabla(array $lineas, int &$i, int $n): string
    {
        $celdas = static function (string $l): array {
            $l = trim($l);
            $l = preg_replace('/^\||\|$/', '', $l) ?? $l;

            return array_map('trim', explode('|', $l));
        };

        $cabecera = $celdas($lineas[$i]);
        $i += 2;   // cabecera + separador
        $filas = [];
        while ($i < $n && str_contains($lineas[$i], '|') && trim($lineas[$i]) !== '') {
            $filas[] = $celdas($lineas[$i]);
            ++$i;
        }

        $html = '<div class="mui-docs__table-wrap"><table class="mui-table"><thead><tr>';
        foreach ($cabecera as $c) {
            $html .= '<th>' . $this->inline($c) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($filas as $fila) {
            $html .= '<tr>';
            foreach ($fila as $c) {
                $html .= '<td>' . $this->inline($c) . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    }

    /**
     * Marcado en línea. El orden importa: el código va PRIMERO y su contenido queda apartado, para
     * que un asterisco dentro de `código` no se convierta en cursiva.
     */
    private function inline(string $texto): string
    {
        $apartados = [];
        $texto = preg_replace_callback('/`([^`]+)`/', function (array $m) use (&$apartados): string {
            $clave = "\x00" . \count($apartados) . "\x00";
            $apartados[$clave] = '<code>' . self::esc($m[1]) . '</code>';

            return $clave;
        }, $texto) ?? $texto;

        $texto = self::esc($texto);
        $texto = preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $texto) ?? $texto;
        $texto = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $texto) ?? $texto;
        $texto = preg_replace('/(?<![\w*])\*([^*]+)\*(?![\w*])/', '<em>$1</em>', $texto) ?? $texto;

        return strtr($texto, $apartados);
    }

    private static function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Ancla legible a partir del texto del encabezado.
     *
     * Los acentos se transliteran ANTES de filtrar: sin eso, «Título» produce `t-tulo` — un ancla
     * que nadie escribiría a mano y que rompe cualquier enlace hecho a ojo. La familia documenta en
     * español y en inglés, así que perder la í no es un caso raro.
     */
    private static function slug(string $s): string
    {
        $s = strtr(mb_strtolower(trim($s), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c',
        ]);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? $s;

        return trim($s, '-');
    }
}
