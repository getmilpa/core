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

namespace Milpa\Tests\Services;

use Milpa\Services\TopologicalSorter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * El orden en que algo puede arrancar.
 *
 * Una dependencia que sale después de quien la necesita es un arranque que
 * falla en el momento más caro: cuando ya se creyó exitoso. Por eso lo que se
 * fija acá no es UNA secuencia —los empates admiten varias válidas— sino la
 * invariante: cada dependencia antes de cada dependiente.
 */
#[CoversClass(TopologicalSorter::class)]
final class TopologicalSorterTest extends TestCase
{
    /**
     * @param list<string>                      $orden
     * @param list<array{0: string, 1: string}> $aristas
     */
    private function assertCadaDependenciaAntes(array $orden, array $aristas): void
    {
        $posicion = array_flip($orden);
        foreach ($aristas as [$dependencia, $dependiente]) {
            if (!isset($posicion[$dependencia], $posicion[$dependiente])) {
                continue;
            }
            self::assertLessThan(
                $posicion[$dependiente],
                $posicion[$dependencia],
                "{$dependencia} tiene que salir antes que {$dependiente}",
            );
        }
    }

    public function testADependencyComesOutBeforeWhoeverNeedsIt(): void
    {
        $aristas = [['db', 'orm'], ['orm', 'app']];

        $orden = (new TopologicalSorter())->sort(['app', 'orm', 'db'], $aristas);

        self::assertSame(['db', 'orm', 'app'], $orden);
        $this->assertCadaDependenciaAntes($orden, $aristas);
    }

    public function testEveryNodeComesOutExactlyOnce(): void
    {
        // Un nodo repetido arrancaría dos veces; uno perdido, ninguna.
        $nodos = ['a', 'b', 'c', 'd'];
        $aristas = [['a', 'b'], ['a', 'c'], ['b', 'd'], ['c', 'd']];

        $orden = (new TopologicalSorter())->sort($nodos, $aristas);

        sort($nodos);
        $salida = $orden;
        sort($salida);
        self::assertSame($nodos, $salida);
        $this->assertCadaDependenciaAntes($orden, $aristas);
    }

    public function testNodesThatDependOnNothingStillComeOut(): void
    {
        $orden = (new TopologicalSorter())->sort(['solo', 'otro'], []);

        self::assertCount(2, $orden);
        self::assertContains('solo', $orden);
        self::assertContains('otro', $orden);
    }

    public function testAnEmptyGraphSortsToNothing(): void
    {
        self::assertSame([], (new TopologicalSorter())->sort([], []));
    }

    public function testAnEdgeThatPointsOutsideTheSetIsIgnored(): void
    {
        // Un grafo parcial es lo normal: se ordena el subconjunto que se está
        // arrancando, y sus aristas hacia afuera no deben tumbarlo.
        $orden = (new TopologicalSorter())->sort(['a', 'b'], [['a', 'b'], ['fantasma', 'a'], ['b', 'otro-fantasma']]);

        self::assertSame(['a', 'b'], $orden);
    }

    public function testACycleIsRefusedInsteadOfLoopingForever(): void
    {
        // Kahn no se cuelga con un ciclo: emite menos nodos de los que entraron.
        // Devolver ese resultado corto en silencio sería arrancar la mitad del
        // sistema y creer que arrancó entero.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Circular dependency detected');

        (new TopologicalSorter())->sort(['a', 'b'], [['a', 'b'], ['b', 'a']]);
    }

    public function testALongerCycleIsAlsoRefused(): void
    {
        $this->expectException(\RuntimeException::class);

        (new TopologicalSorter())->sort(['a', 'b', 'c'], [['a', 'b'], ['b', 'c'], ['c', 'a']]);
    }

    public function testANodeThatDependsOnItselfIsACycle(): void
    {
        $this->expectException(\RuntimeException::class);

        (new TopologicalSorter())->sort(['a'], [['a', 'a']]);
    }

    public function testACycleAmongSomeNodesStopsTheWholeSortNotJustThatPart(): void
    {
        // Devolver los sanos y callar los enredados dejaría al llamador con una
        // lista que parece completa y no lo es.
        $this->expectException(\RuntimeException::class);

        (new TopologicalSorter())->sort(['libre', 'a', 'b'], [['a', 'b'], ['b', 'a']]);
    }

    public function testADiamondKeepsBothBranchesBeforeTheJoin(): void
    {
        $aristas = [['base', 'izq'], ['base', 'der'], ['izq', 'techo'], ['der', 'techo']];

        $orden = (new TopologicalSorter())->sort(['techo', 'izq', 'der', 'base'], $aristas);

        self::assertSame('base', $orden[0]);
        self::assertSame('techo', $orden[3]);
        $this->assertCadaDependenciaAntes($orden, $aristas);
    }

    public function testTheSameGraphSortsTheSameWayEveryTime(): void
    {
        // El orden de arranque tiene que ser reproducible: un empate resuelto
        // distinto en cada corrida hace que un bug de orden aparezca una vez de
        // cada tres y nunca cuando alguien está mirando.
        $nodos = ['d', 'c', 'b', 'a'];
        $aristas = [['a', 'b'], ['c', 'd']];
        $sorter = new TopologicalSorter();

        $primera = $sorter->sort($nodos, $aristas);

        for ($i = 0; $i < 5; $i++) {
            self::assertSame($primera, $sorter->sort($nodos, $aristas));
        }
    }
}
