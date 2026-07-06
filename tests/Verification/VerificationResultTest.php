<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Verification;

use PHPUnit\Framework\TestCase;
use Milpa\Enums\VerificationStatus;
use Milpa\ValueObjects\Verification\VerificationResult;

/**
 * VerificationResult is the tri-state verdict (passed / failed / waived) plus
 * PENDING for async (human/agent) verifiers. Generic: opaque principal,
 * no Doctrine, no domain concepts.
 */
final class VerificationResultTest extends TestCase
{
    public function testPassIsSatisfiedAndFinal(): void
    {
        $r = VerificationResult::pass(verifier: 'human_verify', principal: 'user:42');

        $this->assertSame(VerificationStatus::PASSED, $r->status);
        $this->assertTrue($r->isSatisfied());
        $this->assertTrue($r->isFinal());
        $this->assertFalse($r->isPending());
        $this->assertSame('human_verify', $r->verifier);
        $this->assertSame('user:42', $r->principal);
    }

    public function testFailCarriesReasonAndMissing(): void
    {
        $r = VerificationResult::fail('requirements unmet', missing: ['field:budget', 'evidence:contract']);

        $this->assertSame(VerificationStatus::FAILED, $r->status);
        $this->assertFalse($r->isSatisfied());
        $this->assertTrue($r->isFinal());
        $this->assertTrue($r->hasMissing());
        $this->assertSame(['field:budget', 'evidence:contract'], $r->missing);
        $this->assertSame('requirements unmet', $r->reason);
    }

    public function testWaivedIsSatisfiedWithJustification(): void
    {
        $r = VerificationResult::waived('exec override', principal: 'user:1');

        $this->assertSame(VerificationStatus::WAIVED, $r->status);
        $this->assertTrue($r->isSatisfied(), 'waived clears the gate');
        $this->assertTrue($r->isFinal());
        $this->assertSame('exec override', $r->reason);
        $this->assertSame('user:1', $r->principal);
    }

    public function testWaivedCarriesMetadata(): void
    {
        $r = VerificationResult::waived('exec override', metadata: ['gateCode' => 'budget_approval_gate']);

        $this->assertSame(VerificationStatus::WAIVED, $r->status);
        $this->assertSame('exec override', $r->reason);
        $this->assertSame(['gateCode' => 'budget_approval_gate'], $r->metadata);
    }

    public function testPendingIsNotSatisfiedNorFinal(): void
    {
        $r = VerificationResult::pending(verifier: 'human_verify');

        $this->assertSame(VerificationStatus::PENDING, $r->status);
        $this->assertTrue($r->isPending());
        $this->assertFalse($r->isSatisfied());
        $this->assertFalse($r->isFinal());
    }

    public function testStatusEnumHelpers(): void
    {
        $this->assertTrue(VerificationStatus::PASSED->isSatisfied());
        $this->assertTrue(VerificationStatus::WAIVED->isSatisfied());
        $this->assertFalse(VerificationStatus::FAILED->isSatisfied());
        $this->assertFalse(VerificationStatus::PENDING->isSatisfied());
        $this->assertTrue(VerificationStatus::FAILED->isFinal());
        $this->assertFalse(VerificationStatus::PENDING->isFinal());
    }

    public function testToArrayExposesScalars(): void
    {
        $r = VerificationResult::fail('nope', missing: ['x'], verifier: 'v', metadata: ['k' => 'val']);
        $a = $r->toArray();

        $this->assertSame('failed', $a['status']);
        $this->assertSame('nope', $a['reason']);
        $this->assertSame(['x'], $a['missing']);
        $this->assertSame('v', $a['verifier']);
        $this->assertSame(['k' => 'val'], $a['metadata']);
    }
}
