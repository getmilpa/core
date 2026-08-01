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

namespace Milpa\Tests\Exceptions;

use Milpa\Exceptions\ServiceRedefinitionException;
use PHPUnit\Framework\TestCase;

/**
 * El mensaje ES la funcionalidad de esta excepción.
 *
 * Quien la recibe está mirando dos registros del mismo id y tiene que decidir cuál sobra. Sin los
 * dos orígenes en el texto, la única salida es buscarlos a mano — y el contenedor los tenía cuando
 * decidió negarse.
 */
final class ServiceRedefinitionExceptionTest extends TestCase
{
    /** Los cinco datos entran al mensaje: sin cualquiera de ellos, quien lee tiene que ir a buscarlo. */
    public function testTheMessageCarriesBothRegistrationsAndWhereEachCameFrom(): void
    {
        $e = ServiceRedefinitionException::of(
            'Acme\\Contracts\\Mailer',
            'SmtpMailer',
            'SesMailer',
            'AcmePlugin',
            'MailPlugin',
        );

        $mensaje = $e->getMessage();

        self::assertStringContainsString('Acme\\Contracts\\Mailer', $mensaje);
        self::assertStringContainsString('SmtpMailer', $mensaje, 'el que ya estaba');
        self::assertStringContainsString('SesMailer', $mensaje, 'el que llegaba');
        self::assertStringContainsString('AcmePlugin', $mensaje, 'de dónde venía el primero');
        self::assertStringContainsString('MailPlugin', $mensaje, 'de dónde venía el segundo');
    }

    /**
     * Y dice POR QUÉ se niega, que es lo que evita que alguien lo lea como un defecto.
     *
     * El contenedor no está roto: está declarando que nadie decidió si un segundo proveedor está
     * permitido, y negarse a elegir en silencio es la conducta correcta cuando falta esa decisión.
     */
    public function testItExplainsThatNobodyDecidedInsteadOfLookingLikeABug(): void
    {
        $e = ServiceRedefinitionException::of('id', 'a', 'b', '?', '?');

        self::assertStringContainsString('refuses to', $e->getMessage());
        self::assertStringContainsString('distinct id', $e->getMessage(), 'y ofrece la salida');
    }
}
