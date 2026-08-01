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

use Milpa\Docs\SiteConfig;
use Milpa\Docs\SiteGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Las guías narrativas entran al sitio generado (D14 fase 3).
 *
 * La propiedad que importa: **un paquete sin guías sigue generando su referencia.** Si la ausencia
 * del directorio rompiera el generador, añadir la fase 3 habría roto el gate de docs de los treinta
 * repositorios publicados a la vez — y se habría descubierto en el runner, no aquí.
 */
final class NarrativeSiteTest extends TestCase
{
    private string $out = '';

    protected function tearDown(): void
    {
        if ($this->out !== '' && is_dir($this->out)) {
            exec('rm -rf ' . escapeshellarg($this->out));
        }
    }

    private function genera(string $narrativeDir): string
    {
        $this->out = sys_get_temp_dir() . '/milpa-docs-' . bin2hex(random_bytes(4));
        (new SiteGenerator(
            \dirname(__DIR__, 2) . '/src',
            $this->out,
            'https://example.invalid/ds',
            'test',
            new SiteConfig(narrativeDir: $narrativeDir),
        ))->generate();

        return $this->out;
    }

    public function test_las_guias_aparecen_en_el_nav_antes_de_la_referencia(): void
    {
        $out = $this->genera('docs');
        $index = (string) file_get_contents($out . '/index.html');

        $guias = strpos($index, 'nav-heading">Guías');
        self::assertNotFalse($guias, 'el grupo de guías tiene que existir');

        preg_match_all('/nav-heading">(?!Guías)/', $index, $m, PREG_OFFSET_CAPTURE);
        self::assertNotEmpty($m[0], 'y también los grupos de referencia');
        self::assertLessThan(
            $m[0][0][1],
            $guias,
            'las guías van primero: quien llega no busca una clase, busca por dónde empezar'
        );
    }

    public function test_cada_guia_produce_su_pagina_con_titulo_del_encabezado(): void
    {
        $out = $this->genera('docs');

        self::assertFileExists($out . '/guias/quickstart.html');
        $html = (string) file_get_contents($out . '/guias/quickstart.html');
        self::assertStringContainsString(
            '<title>Empezar',
            $html,
            'el título sale del primer encabezado del archivo, no del nombre'
        );
    }

    public function test_un_paquete_sin_guias_sigue_generando_su_referencia(): void
    {
        $out = $this->genera('no-existe-este-directorio');

        self::assertFileExists($out . '/index.html');
        self::assertDirectoryDoesNotExist($out . '/guias');
        self::assertStringNotContainsString(
            'nav-heading">Guías',
            (string) file_get_contents($out . '/index.html')
        );
    }
}
