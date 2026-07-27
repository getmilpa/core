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

use Milpa\ValueObjects\SemanticVersion;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The truth table of the value object every version decision in the family
 * rests on: what parses, what a constraint admits, and how two versions order.
 *
 * `milpa/plugin` picks which release to install from these answers, and
 * `milpa/resolver` orders the boot from them. They were being exercised only
 * as a side effect of those packages' own tests — never here, where the class
 * is published from.
 */
#[CoversClass(SemanticVersion::class)]
final class SemanticVersionConstraintsTest extends TestCase
{
    // ---- parsing ---------------------------------------------------------------

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function parsable(): iterable
    {
        yield 'full' => ['1.2.3', '1.2.3'];
        yield 'a leading v' => ['v1.2.3', '1.2.3'];
        yield 'a leading uppercase V' => ['V1.2.3', '1.2.3'];
        yield 'surrounding space' => ['  1.2.3  ', '1.2.3'];
        yield 'minor omitted' => ['1', '1.0.0'];
        yield 'patch omitted' => ['1.2', '1.2.0'];
        yield 'a prerelease' => ['1.2.3-beta', '1.2.3-beta'];
        yield 'a dotted prerelease' => ['1.2.3-beta.1', '1.2.3-beta.1'];
        yield 'build metadata' => ['1.2.3+build.42', '1.2.3+build.42'];
        yield 'both' => ['1.2.3-rc.1+build.42', '1.2.3-rc.1+build.42'];
    }

    #[DataProvider('parsable')]
    public function testWhatParsesRoundTripsThroughItsString(string $input, string $expected): void
    {
        self::assertSame($expected, (string) SemanticVersion::parse($input));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unparsable(): iterable
    {
        yield 'empty' => [''];
        yield 'words' => ['latest'];
        yield 'a trailing dot' => ['1.2.'];
        yield 'four parts' => ['1.2.3.4'];
        yield 'a negative number' => ['-1.0.0'];
        yield 'an underscore in the prerelease' => ['1.0.0-beta_1'];
    }

    #[DataProvider('unparsable')]
    public function testWhatDoesNotParseSaysSoWithTheStringItWasGiven(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid semantic version');

        SemanticVersion::parse($input);
    }

    #[DataProvider('unparsable')]
    public function testTryParseAnswersNullWhereParseThrows(string $input): void
    {
        self::assertNull(SemanticVersion::tryParse($input));
    }

    public function testTryParseAnswersTheVersionWhenThereIsOne(): void
    {
        self::assertSame('2.0.0', (string) SemanticVersion::tryParse('v2'));
    }

    // ---- constraints -------------------------------------------------------------

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function constraints(): iterable
    {
        // Caret: compatible-with. The ceiling moves down as the leading
        // non-zero moves right, which is the whole reason 0.x is special.
        yield 'caret admits a higher minor' => ['1.4.0', '^1.2.3', true];
        yield 'caret admits the floor itself' => ['1.2.3', '^1.2.3', true];
        yield 'caret refuses below the floor' => ['1.2.2', '^1.2.3', false];
        yield 'caret refuses the next major' => ['2.0.0', '^1.2.3', false];
        yield 'caret on 0.x admits a higher patch' => ['0.2.9', '^0.2.3', true];
        yield 'caret on 0.x refuses the next minor' => ['0.3.0', '^0.2.3', false];
        yield 'caret on 0.0.x admits only that patch' => ['0.0.3', '^0.0.3', true];
        yield 'caret on 0.0.x refuses the next patch' => ['0.0.4', '^0.0.3', false];

        // Tilde: patch-level only.
        yield 'tilde admits a higher patch' => ['1.2.9', '~1.2.3', true];
        yield 'tilde refuses the next minor' => ['1.3.0', '~1.2.3', false];
        yield 'tilde refuses below the floor' => ['1.2.2', '~1.2.3', false];
        yield 'tilde without a patch means the whole minor' => ['1.2.7', '~1.2', true];

        // Comparators.
        yield '>= admits equal' => ['1.0.0', '>=1.0.0', true];
        yield '>= admits above' => ['1.0.1', '>=1.0.0', true];
        yield '>= refuses below' => ['0.9.9', '>=1.0.0', false];
        yield '> refuses equal' => ['1.0.0', '>1.0.0', false];
        yield '> admits above' => ['1.0.1', '>1.0.0', true];
        yield '<= admits equal' => ['1.0.0', '<=1.0.0', true];
        yield '< refuses equal' => ['1.0.0', '<1.0.0', false];
        yield '< admits below' => ['0.9.9', '<1.0.0', true];
        yield '= admits only equal' => ['1.0.0', '=1.0.0', true];
        yield '= refuses anything else' => ['1.0.1', '=1.0.0', false];

        // Bare and wildcards.
        yield 'a bare version is an exact match' => ['1.0.0', '1.0.0', true];
        yield 'a bare version refuses anything else' => ['1.0.1', '1.0.0', false];
        yield 'a star admits anything' => ['9.9.9', '*', true];
        yield 'an empty constraint admits anything' => ['9.9.9', '', true];
        yield 'whitespace only admits anything' => ['9.9.9', '   ', true];

        // Space-separated parts are AND-ed: every one has to hold.
        yield 'a range that holds' => ['2.0.0', '>=1.0 <3.0', true];
        yield 'a range whose upper bound fails' => ['3.0.0', '>=1.0 <3.0', false];
        yield 'a range whose lower bound fails' => ['0.9.0', '>=1.0 <3.0', false];
    }

    #[DataProvider('constraints')]
    public function testAConstraintAdmitsExactlyWhatItSays(string $version, string $constraint, bool $admitted): void
    {
        self::assertSame($admitted, SemanticVersion::parse($version)->satisfies($constraint));
    }

    // ---- ordering -----------------------------------------------------------------

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function ordering(): iterable
    {
        yield 'by major' => ['2.0.0', '1.9.9', 1];
        yield 'by minor' => ['1.10.0', '1.9.0', 1];
        yield 'by patch' => ['1.0.10', '1.0.9', 1];
        yield 'equal' => ['1.2.3', '1.2.3', 0];

        // Per semver: a prerelease sorts BEFORE its own release.
        yield 'a prerelease sorts before its release' => ['1.0.0-alpha', '1.0.0', -1];
        yield 'a release sorts after its prerelease' => ['1.0.0', '1.0.0-alpha', 1];

        // Within a prerelease, identifiers compare part by part.
        yield 'numeric identifiers compare as numbers' => ['1.0.0-alpha.10', '1.0.0-alpha.9', 1];
        yield 'alphabetic identifiers compare as text' => ['1.0.0-beta', '1.0.0-alpha', 1];
        yield 'a numeric identifier sorts before an alphabetic one' => ['1.0.0-1', '1.0.0-alpha', -1];
        yield 'fewer identifiers sort first' => ['1.0.0-alpha', '1.0.0-alpha.1', -1];
        yield 'more identifiers sort last' => ['1.0.0-alpha.1', '1.0.0-alpha', 1];
    }

    #[DataProvider('ordering')]
    public function testTwoVersionsOrderTheWaySemverSaysTheyDo(string $left, string $right, int $expected): void
    {
        $a = SemanticVersion::parse($left);
        $b = SemanticVersion::parse($right);

        self::assertSame($expected, $a->compareTo($b));
        self::assertSame($expected > 0, $a->greaterThan($b));
        self::assertSame($expected >= 0, $a->greaterThanOrEqual($b));
        self::assertSame($expected < 0, $a->lessThan($b));
        self::assertSame($expected <= 0, $a->lessThanOrEqual($b));
        self::assertSame($expected === 0, $a->equals($b));
    }

    public function testBuildMetadataDoesNotAffectPrecedence(): void
    {
        // Semver is explicit about this: two versions differing only in build
        // metadata have the same precedence. A resolver that ordered by the
        // string would pick one of them arbitrarily.
        $a = SemanticVersion::parse('1.0.0+build.1');
        $b = SemanticVersion::parse('1.0.0+build.2');

        self::assertTrue($a->equals($b));
    }

    // ---- the rest of the surface ------------------------------------------------------

    public function testAPrereleaseIsNotStableAndAPlainVersionIs(): void
    {
        self::assertTrue(SemanticVersion::parse('1.0.0')->isStable());
        self::assertFalse(SemanticVersion::parse('1.0.0-rc.1')->isStable());
        self::assertTrue(SemanticVersion::parse('1.0.0+build.1')->isStable(), 'Build metadata is not a prerelease.');
    }

    public function testIncrementingResetsEverythingBelowItAndDropsThePrerelease(): void
    {
        // Carrying `-rc.1` into the next release is how a stable version ends
        // up looking like a prerelease of itself.
        $version = SemanticVersion::parse('1.2.3-rc.1+build.9');

        self::assertSame('2.0.0', (string) $version->incrementMajor());
        self::assertSame('1.3.0', (string) $version->incrementMinor());
        self::assertSame('1.2.4', (string) $version->incrementPatch());
    }
}
