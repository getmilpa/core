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
use Milpa\Enums\ApprovalPolicy;
use Milpa\ValueObjects\Verification\VerificationContext;
use Milpa\ValueObjects\Verification\VerificationRequest;

/**
 * the request (what is being verified + under which policy) and the context
 * (who/what evidence backs it). Opaque principals; no Doctrine.
 */
final class VerificationRequestContextTest extends TestCase
{
    public function testRequestDefaults(): void
    {
        $r = new VerificationRequest('gate:proposal.sent');

        $this->assertSame('gate:proposal.sent', $r->subject);
        $this->assertSame(ApprovalPolicy::SINGLE, $r->policy);
        $this->assertSame([], $r->payload);
        $this->assertNull($r->requestedBy);
    }

    public function testRequestFull(): void
    {
        $r = new VerificationRequest(
            subject: 'task:42',
            policy: ApprovalPolicy::DUAL,
            payload: ['amount' => 1000],
            requestedBy: 'user:7',
        );

        $this->assertSame(ApprovalPolicy::DUAL, $r->policy);
        $this->assertSame(['amount' => 1000], $r->payload);
        $this->assertSame('user:7', $r->requestedBy);
    }

    public function testRequestRejectsEmptySubject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VerificationRequest('   ');
    }

    public function testRequestIdDefaultsToNullAndIsBcFriendly(): void
    {
        $r = new VerificationRequest('gate:proposal.sent');

        $this->assertNull($r->id);
    }

    public function testRequestIdCanBeSetExplicitly(): void
    {
        $r = new VerificationRequest('gate:proposal.sent', id: 'corr-1');

        $this->assertSame('corr-1', $r->id);
    }

    public function testWithGeneratedIdAssignsAUuid(): void
    {
        $r = VerificationRequest::withGeneratedId('gate:proposal.sent');

        $this->assertNotNull($r->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $r->id,
        );
    }

    public function testTwoRequestsWithSameSubjectAreDistinguishableByGeneratedId(): void
    {
        $a = VerificationRequest::withGeneratedId('gate:proposal.sent');
        $b = VerificationRequest::withGeneratedId('gate:proposal.sent');

        $this->assertSame($a->subject, $b->subject);
        $this->assertNotSame($a->id, $b->id);
    }

    public function testContextDefaults(): void
    {
        $c = new VerificationContext();

        $this->assertNull($c->principal);
        $this->assertSame([], $c->approvals);
        $this->assertSame(0, $c->approvalCount());
        $this->assertFalse($c->hasEvidence());
    }

    public function testContextCountsDistinctApprovalsAndEvidence(): void
    {
        $c = new VerificationContext(
            principal: 'user:1',
            approvals: ['user:1', 'user:2', 'user:1'],
            evidence: ['contract' => 'signed.pdf'],
        );

        $this->assertSame('user:1', $c->principal);
        $this->assertSame(2, $c->approvalCount(), 'distinct principals only');
        $this->assertTrue($c->hasEvidence());
    }
}
