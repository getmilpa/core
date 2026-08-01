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

namespace Milpa\Tests\DTO;

use Milpa\DTO\PluginRemoveResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The answer `coa:plugins uninstall` hands back. Published, and until now
 * never constructed by anything but production.
 */
#[CoversClass(PluginRemoveResult::class)]
final class PluginRemoveResultTest extends TestCase
{
    public function testASuccessCarriesTheNameAndNoError(): void
    {
        $result = PluginRemoveResult::success('BlogEngine');

        self::assertTrue($result->success);
        self::assertSame('BlogEngine', $result->pluginName);
        self::assertFalse($result->dataKept, 'Removal drops the data unless asked otherwise.');
        self::assertSame(0, $result->migrationsReverted);
        self::assertNull($result->error);
    }

    public function testASuccessRemembersThatTheDataWasKept(): void
    {
        // The difference between "uninstalled" and "uninstalled but the tables
        // are still there" is the whole reason this flag exists.
        $result = PluginRemoveResult::success('BlogEngine', dataKept: true, migrationsReverted: 3);

        self::assertTrue($result->dataKept);
        self::assertSame(3, $result->migrationsReverted);
    }

    public function testAFailureCarriesTheReasonAndNotASuccessFlag(): void
    {
        $result = PluginRemoveResult::failure('BlogEngine', 'still required by ShopPlugin');

        self::assertFalse($result->success);
        self::assertSame('BlogEngine', $result->pluginName);
        self::assertSame('still required by ShopPlugin', $result->error);
        self::assertSame(0, $result->migrationsReverted, 'Nothing was reverted, because nothing ran.');
    }
    /**
     * El constructor primario, no sólo los nombrados.
     *
     * Esta cobertura vivía en una copia del host con el mismo nombre de archivo; se movió aquí al
     * consolidar (P17.3) porque el DTO es del paquete y su prueba también. Sin ella, `success()` y
     * `failure()` quedaban probados y la construcción directa no.
     */
    public function testTheConstructorDefaultsToNoDataKeptAndNoMigrations(): void
    {
        $result = new PluginRemoveResult(success: true, pluginName: 'BlogEngine');

        self::assertTrue($result->success);
        self::assertSame('BlogEngine', $result->pluginName);
        self::assertFalse($result->dataKept);
        self::assertSame(0, $result->migrationsReverted);
        self::assertNull($result->error);
    }

    /** Y con todos los campos puestos a mano, incluido un fallo que sí conservó datos. */
    public function testTheConstructorCarriesEveryField(): void
    {
        $result = new PluginRemoveResult(
            success: false,
            pluginName: 'BlogEngine',
            dataKept: true,
            migrationsReverted: 3,
            error: 'still required by ShopPlugin',
        );

        self::assertFalse($result->success);
        self::assertSame('BlogEngine', $result->pluginName);
        self::assertTrue($result->dataKept);
        self::assertSame(3, $result->migrationsReverted);
        self::assertSame('still required by ShopPlugin', $result->error);
    }
}
