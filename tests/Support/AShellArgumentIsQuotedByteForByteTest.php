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

namespace Milpa\Tests\Support;

use Milpa\Support\ShellArgument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A shell argument is quoted byte for byte, whatever the locale (greenhouse decisions/0624).
 *
 * `escapeshellarg()` reads LC_CTYPE: under the C locale (PHP 8.2 to 8.5) it drops the bytes it cannot place, so
 * `escapeshellarg('sí')` is `'s'`. Every case here runs under LC_CTYPE=C, set by the test: PHP starts at C.UTF-8, and
 * C is what a setlocale() from a C/POSIX environment gives.
 */
final class AShellArgumentIsQuotedByteForByteTest extends TestCase
{
    private string|false $locale = false;

    protected function setUp(): void
    {
        $this->locale = setlocale(\LC_CTYPE, '0');
        setlocale(\LC_CTYPE, 'C');
    }

    protected function tearDown(): void
    {
        if ($this->locale !== false) {
            setlocale(\LC_CTYPE, $this->locale);
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function values(): iterable
    {
        yield 'an accent' => ['sí'];
        yield 'a path with an ñ' => ['/var/lab/señal'];
        yield 'a single quote' => ["it's"];
        yield 'two single quotes in a row' => ["''"];
        yield 'spaces and a dollar' => ['a $HOME b'];
        yield 'a backtick' => ['`id`'];
        yield 'a command substitution' => ['$(id)'];
        yield 'a backslash before a quote' => ["\\'"];
        yield 'a newline' => ["one\ntwo"];
        yield 'invalid UTF-8' => ["\xff\xfe"];
        yield 'emoji' => ['🌽'];
        yield 'empty' => [''];
    }

    #[DataProvider('values')]
    public function testTheShellReceivesExactlyTheBytesThatWereQuoted(string $value): void
    {
        $said = shell_exec('printf %s ' . ShellArgument::quote($value));

        self::assertSame($value, (string) $said);
    }

    public function testTwoQuotedArgumentsStayTwoArguments(): void
    {
        $said = shell_exec('set -- ' . ShellArgument::quote("a b") . ' ' . ShellArgument::quote("c'd") . '; echo $#');

        self::assertSame("2\n", (string) $said);
    }

    public function testForASCIIItSaysWhatEscapeshellargSays(): void
    {
        foreach (['', 'plain', "it's", 'a b', '$x', '-n', "tab\there"] as $value) {
            self::assertSame(escapeshellarg($value), ShellArgument::quote($value), $value);
        }
    }

    public function testANulByteIsRefusedAsEscapeshellargRefusesIt(): void
    {
        $this->expectException(\ValueError::class);

        ShellArgument::quote("a\0b");
    }
}
