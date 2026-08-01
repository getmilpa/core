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

use Milpa\Services\CapabilityMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CapabilityMatcher::class)]
final class CapabilityMatcherTest extends TestCase
{
    private CapabilityMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new CapabilityMatcher();
    }

    // ---------------------------------------------------------------- identidad

    public function testTheLegacyBareFqcnAndTheCanonicalPhpFormAreOneIdentity(): void
    {
        self::assertTrue($this->matcher->satisfies('Acme\ThingInterface', 'php:Acme\ThingInterface'));
        self::assertTrue($this->matcher->satisfies('php:Acme\ThingInterface', 'Acme\ThingInterface'));
    }

    public function testALeadingBackslashNeverDistinguishes(): void
    {
        self::assertTrue($this->matcher->satisfies('\Acme\ThingInterface', 'Acme\ThingInterface'));
        self::assertTrue($this->matcher->satisfies('php:\Acme\ThingInterface', '\php:Acme\ThingInterface'));
    }

    public function testAnEnvIdentityNeverCollidesWithAPhpOne(): void
    {
        self::assertFalse($this->matcher->satisfies('env:DATABASE_URL', 'DATABASE_URL'));
        self::assertTrue($this->matcher->satisfies('env:DATABASE_URL', 'env:DATABASE_URL'));
    }

    public function testAnOpaqueIdIsKeptVerbatimAndNotReadAsAClass(): void
    {
        self::assertTrue($this->matcher->satisfies('crm.oauth.google.v1', 'crm.oauth.google.v1'));
        self::assertFalse($this->matcher->satisfies('crm.oauth.google.v1', 'php:crm.oauth.google.v2'));
    }

    public function testARecordOffersBothItsIdAndItsInterface(): void
    {
        $provision = ['id' => 'crm.oauth.v1', 'interface' => 'Acme\OauthInterface', 'contractVersion' => '1.0.0'];

        self::assertTrue($this->matcher->satisfies($provision, ['id' => 'crm.oauth.v1', 'interface' => 'x']));
        self::assertTrue($this->matcher->satisfies($provision, 'Acme\OauthInterface'));
    }

    public function testTheValidatorsBlindSpotIsClosedARecordWithNoInterfaceStillHasAnIdentity(): void
    {
        // `CapabilityGraphValidator` sólo leía `interface`: esta entrada era invisible para él,
        // y el motor sí la resolvía. El desacuerdo producía una violación falsa.
        self::assertTrue($this->matcher->satisfies(['id' => 'crm.oauth.v1'], ['id' => 'crm.oauth.v1']));
    }

    // ------------------------------------------------------------------- oneOf

    public function testARequirementIsSatisfiedByAnyOneOfAlternative(): void
    {
        $requirement = ['id' => 'mail.smtp', 'interface' => 'Acme\MailerInterface', 'oneOf' => ['mail.sendgrid', 'mail.ses']];

        self::assertTrue($this->matcher->satisfies('mail.ses', $requirement));
        self::assertFalse($this->matcher->satisfies('mail.postmark', $requirement));
    }

    // -------------------------------------------------- compatibilidad de contrato

    /** @return iterable<string, array{0: string|null, 1: string, 2: bool}> */
    public static function contractCases(): iterable
    {
        yield 'compatible' => ['1.2.0', '^1.0', true];
        yield 'demasiado nueva' => ['2.0.0', '^1.0', false];
        yield 'sin restricción acepta cualquiera' => ['2.0.0', '*', true];
        yield 'desconocida contra restricción' => [null, '^1.0', false];
        yield 'desconocida contra comodín' => [null, '*', true];
        yield 'ilegible no es compatible' => ['no-es-semver', '^1.0', false];
    }

    #[DataProvider('contractCases')]
    public function testContractCompatibility(?string $version, string $constraint, bool $expected): void
    {
        self::assertSame($expected, $this->matcher->contractIsCompatible($version, $constraint));
    }

    public function testAnUnversionedProviderCannotSatisfyAVersionedRequirement(): void
    {
        // La forma legacy pinta `0.0.0` al construir el value object, y `0.0.0` contra `^1.0`
        // significaría «demasiado vieja». Aquí significa lo que de verdad pasó: nadie dijo.
        self::assertNull($this->matcher->contractVersionOf('Acme\ThingInterface'));
        self::assertFalse($this->matcher->satisfies(
            'Acme\ThingInterface',
            ['id' => 'Acme\ThingInterface', 'interface' => 'Acme\ThingInterface', 'constraint' => '^1.0'],
        ));
    }

    public function testIdentityMatchesButContractDoesNotIsNotASatisfaction(): void
    {
        $provision = ['id' => 'crm.oauth.v1', 'interface' => 'Acme\OauthInterface', 'contractVersion' => '2.0.0'];
        $requirement = ['id' => 'crm.oauth.v1', 'interface' => 'Acme\OauthInterface', 'constraint' => '^1.0'];

        self::assertSame(
            $this->matcher->identitiesOffered($provision),
            $this->matcher->identitiesAccepted($requirement),
            'las identidades sí coinciden',
        );
        self::assertFalse($this->matcher->satisfies($provision, $requirement));
    }

    // ------------------------------------------------------- la tercera sintaxis

    public function testAConstraintPackedIntoTheIdentityIsReadAsAConstraint(): void
    {
        self::assertSame('crm.oauth.google.v1', $this->matcher->identitiesOffered('crm.oauth.google.v1@^1.0')[0]);
        self::assertSame('^1.0', $this->matcher->constraintOf('crm.oauth.google.v1@^1.0'));
        self::assertSame('*', $this->matcher->constraintOf('crm.oauth.google.v1'));
    }

    public function testAPackedConstraintStillMatchesTheSameIdentity(): void
    {
        $provision = ['id' => 'crm.oauth.google.v1', 'interface' => 'Acme\O', 'contractVersion' => '1.4.0'];

        self::assertTrue($this->matcher->satisfies($provision, 'crm.oauth.google.v1@^1.0'));
        self::assertFalse($this->matcher->satisfies($provision, 'crm.oauth.google.v1@^2.0'));
    }

    public function testAnExplicitConstraintFieldWinsOverAPackedOne(): void
    {
        self::assertSame('^3.0', $this->matcher->constraintOf(['id' => 'a@^1.0', 'constraint' => '^3.0']));
    }

    // ------------------------------------------------------------------ vacíos

    public function testAnEmptyOrUnreadableEntryOffersNothing(): void
    {
        self::assertSame([], $this->matcher->identitiesOffered('   '));
        self::assertSame([], $this->matcher->identitiesOffered([]));
        self::assertSame([], $this->matcher->identitiesOffered(['id' => 42]));
        self::assertFalse($this->matcher->satisfies('', ''));
    }
}
