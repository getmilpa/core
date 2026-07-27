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

namespace Milpa\Tests\ValueObjects;

use Milpa\Exceptions\MilpaExceptionInterface;
use Milpa\Exceptions\SecretMissingException;
use Milpa\ValueObjects\Secret;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Un objeto que existe para NO filtrar.
 *
 * Por eso casi todo lo que se prueba acá es lo que se niega a hacer: un
 * `Secret` que se deja serializar, clonar o imprimir en un dump es un secreto
 * en un log, y un log es para siempre. La única salida deliberada es
 * {@see Secret::value()}, en una frontera auditada.
 */
#[CoversClass(Secret::class)]
#[CoversClass(SecretMissingException::class)]
final class SecretTest extends TestCase
{
    // ---- lo que deja pasar ------------------------------------------------------

    public function testAValueGoesInAndComesBackExactlyAsItWent(): void
    {
        $secret = new Secret('MYSQL_PASSWORD', 's3cr3t');

        self::assertSame('s3cr3t', $secret->value());
        self::assertSame('MYSQL_PASSWORD', $secret->name());
    }

    public function testARequiredSecretIsBuiltFromWhatTheEnvironmentGave(): void
    {
        self::assertSame('abc123', Secret::required('API_KEY', 'abc123')->value());
    }

    public function testAnOptionalSecretIsNullWhenNobodySetIt(): void
    {
        // Una instancia de Redis sin autenticación es el caso normal, no un
        // error: pedir que falle ahí obligaría a inventar una contraseña.
        self::assertNull(Secret::optional('REDIS_PASSWORD', null));
        self::assertNull(Secret::optional('REDIS_PASSWORD', ''));
        self::assertNull(Secret::optional('REDIS_PASSWORD', '   '));
    }

    public function testAnOptionalSecretThatWasSetIsCarried(): void
    {
        $secret = Secret::optional('REDIS_PASSWORD', 'pw');

        self::assertNotNull($secret);
        self::assertSame('pw', $secret->value());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function blancos(): iterable
    {
        yield 'vacío' => [''];
        yield 'un espacio' => [' '];
        yield 'espacios' => ['     '];
        yield 'tabulador' => ["\t"];
        yield 'salto de línea' => ["\n"];
    }

    #[DataProvider('blancos')]
    public function testARequiredSecretThatIsBlankFailsClosed(string $raw): void
    {
        // Falla cerrado a propósito: Milpa dejó de caer en defaults inseguros
        // como 'root'. Una cadena en blanco es ausencia, no un secreto raro.
        $this->expectException(SecretMissingException::class);

        Secret::required('MYSQL_PASSWORD', $raw);
    }

    public function testARequiredSecretThatIsNullFailsClosed(): void
    {
        $this->expectException(SecretMissingException::class);

        Secret::required('MYSQL_PASSWORD', null);
    }

    public function testTheValueIsNeverTrimmedOnlyCheckedForAbsence(): void
    {
        // Una contraseña con espacios al principio o al final es una contraseña
        // válida. Recortarla al guardarla es cómo una app "pierde" credenciales
        // que el usuario copió bien.
        $secret = Secret::required('MYSQL_PASSWORD', '  con espacios  ');

        self::assertSame('  con espacios  ', $secret->value());
    }

    // ---- lo que se niega a hacer ----------------------------------------------------

    public function testADumpRedactsTheValueButKeepsTheName(): void
    {
        // print_r/var_dump son el camino por el que un secreto llega a un log
        // sin que nadie lo haya pedido. El nombre sí viaja: es lo que permite
        // saber QUÉ secreto es sin saber cuál.
        $volcado = print_r(new Secret('MYSQL_PASSWORD', 's3cr3t'), true);

        self::assertStringNotContainsString('s3cr3t', $volcado);
        self::assertStringContainsString('[redacted]', $volcado);
        self::assertStringContainsString('MYSQL_PASSWORD', $volcado);
    }

    public function testSerializingIsRefusedOutright(): void
    {
        // Un secreto serializado es un secreto en una sesión, en una cola o en
        // un caché — tres lugares que sobreviven al proceso.
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must not be serialized');

        serialize(new Secret('MYSQL_PASSWORD', 's3cr3t'));
    }

    public function testCloningIsRefusedOutright(): void
    {
        $secret = new Secret('MYSQL_PASSWORD', 's3cr3t');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must not be cloned');

        clone $secret;
    }

    public function testThereIsDeliberatelyNoStringConversion(): void
    {
        // Sin __toString(), interpolarlo en una cadena es un TypeError en vez
        // de un secreto impreso. El error ruidoso es la característica.
        self::assertFalse(method_exists(Secret::class, '__toString'));
    }

    public function testTheValueIsPrivateSoNothingReadsItByAccident(): void
    {
        $propiedad = new \ReflectionProperty(Secret::class, 'value');

        self::assertTrue($propiedad->isPrivate());
        self::assertTrue($propiedad->isReadOnly());
    }

    public function testTheConstructorMarksTheValueAsSensitive(): void
    {
        // #[\SensitiveParameter] es lo que borra el argumento de los dumps de
        // stack trace. Sin él, un throw en cualquier punto del constructor
        // imprime el secreto en el reporte de error.
        $parametros = (new \ReflectionMethod(Secret::class, '__construct'))->getParameters();
        $valor = $parametros[1];

        self::assertSame('value', $valor->getName());
        self::assertNotSame([], $valor->getAttributes(\SensitiveParameter::class));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function fabricas(): iterable
    {
        yield 'required' => ['required'];
        yield 'optional' => ['optional'];
    }

    #[DataProvider('fabricas')]
    public function testEveryFactoryAlsoMarksTheRawValueAsSensitive(string $metodo): void
    {
        $parametros = (new \ReflectionMethod(Secret::class, $metodo))->getParameters();

        self::assertNotSame([], $parametros[1]->getAttributes(\SensitiveParameter::class), $metodo);
    }

    // ---- lo que dice cuando falta ------------------------------------------------------

    public function testTheAbsenceErrorNamesTheVariableAndHowToSetIt(): void
    {
        // El mensaje es lo único que tiene quien está mirando un arranque
        // roto: tiene que nombrar la variable exacta y decir qué hacer.
        try {
            Secret::required('MYSQL_PASSWORD', null);
            self::fail('Se esperaba que un secreto requerido ausente fallara.');
        } catch (SecretMissingException $e) {
            self::assertStringContainsString('MYSQL_PASSWORD', $e->getMessage());
            self::assertStringContainsString('MILPA_SECRET_MISSING', $e->getMessage());
            self::assertStringContainsString("insecure defaults like 'root'", $e->getMessage());
            self::assertSame('MILPA_SECRET_MISSING', $e->errorCode());
        }
    }

    public function testTheAbsenceErrorIsAMilpaExceptionSoBroadHandlersCatchIt(): void
    {
        $e = SecretMissingException::required('API_KEY');

        self::assertInstanceOf(MilpaExceptionInterface::class, $e);
        self::assertInstanceOf(\RuntimeException::class, $e);
    }

    public function testTheAbsenceErrorCarriesNoValueBecauseThereWasNone(): void
    {
        // Obvio y por eso vale fijarlo: el mensaje de "falta el secreto" nunca
        // debe aprender a incluir el secreto que sí llegó.
        $parametros = (new \ReflectionMethod(SecretMissingException::class, 'required'))->getParameters();

        self::assertCount(1, $parametros);
        self::assertSame('name', $parametros[0]->getName());
    }
}
