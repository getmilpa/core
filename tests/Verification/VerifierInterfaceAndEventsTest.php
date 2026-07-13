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

namespace Milpa\Tests\Verification;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;
use Milpa\Enums\ApprovalPolicy;
use Milpa\Events\VerificationGrantedEvent;
use Milpa\Events\VerificationRejectedEvent;
use Milpa\Events\VerificationRequestedEvent;
use Milpa\Interfaces\Verification\VerifierInterface;
use Milpa\ValueObjects\Verification\VerificationContext;
use Milpa\ValueObjects\Verification\VerificationRequest;
use Milpa\ValueObjects\Verification\VerificationResult;

/**
 * the VerifierInterface contract + the three lifecycle events. A fake verifier
 * proves the pieces compose (request + context + policy -> result) with only core types.
 */
final class VerifierInterfaceAndEventsTest extends TestCase
{
    private function fakeVerifier(): VerifierInterface
    {
        return new class () implements VerifierInterface {
            public function verify(VerificationRequest $request, VerificationContext $context): VerificationResult
            {
                if ($request->policy->isAutomatic()) {
                    return $context->hasEvidence()
                        ? VerificationResult::pass(verifier: 'fake', principal: $context->principal)
                        : VerificationResult::fail('evidence required', ['evidence'], verifier: 'fake');
                }

                return $context->approvalCount() >= $request->policy->requiredApprovals()
                    ? VerificationResult::pass(verifier: 'fake', principal: $context->principal)
                    : VerificationResult::fail('insufficient approvals', ['approvals'], verifier: 'fake');
            }
        };
    }

    public function testVerifierApprovesWhenApprovalsMeetPolicy(): void
    {
        $result = $this->fakeVerifier()->verify(
            new VerificationRequest('gate:x', ApprovalPolicy::DUAL),
            new VerificationContext(principal: 'user:1', approvals: ['user:1', 'user:2']),
        );

        $this->assertTrue($result->isSatisfied());
        $this->assertSame('fake', $result->verifier);
    }

    public function testVerifierFailsWhenApprovalsInsufficient(): void
    {
        $result = $this->fakeVerifier()->verify(
            new VerificationRequest('gate:x', ApprovalPolicy::DUAL),
            new VerificationContext(approvals: ['user:1']),
        );

        $this->assertFalse($result->isSatisfied());
        $this->assertSame(['approvals'], $result->missing);
    }

    public function testVerifierAutoApprovesWithEvidence(): void
    {
        $verifier = $this->fakeVerifier();
        $req = new VerificationRequest('gate:x', ApprovalPolicy::AUTO);

        $this->assertTrue($verifier->verify($req, new VerificationContext(evidence: ['doc' => 'x']))->isSatisfied());
        $this->assertFalse($verifier->verify($req, new VerificationContext())->isSatisfied());
    }

    public function testRequestedEventCarriesRequest(): void
    {
        $req = new VerificationRequest('gate:x');
        $event = new VerificationRequestedEvent($req);

        $this->assertInstanceOf(Event::class, $event, 'dispatchable/stoppable');
        $this->assertSame($req, $event->getRequest());
    }

    public function testGrantedAndRejectedEventsCarryRequestAndResult(): void
    {
        $req = new VerificationRequest('gate:x');

        $granted = new VerificationGrantedEvent($req, VerificationResult::pass());
        $this->assertSame($req, $granted->getRequest());
        $this->assertTrue($granted->getResult()->isSatisfied());

        $rejected = new VerificationRejectedEvent($req, VerificationResult::fail('nope'));
        $this->assertSame($req, $rejected->getRequest());
        $this->assertFalse($rejected->getResult()->isSatisfied());
    }

    public function testConcurrentRequestsWithSameSubjectAreDistinguishableById(): void
    {
        $first = VerificationRequest::withGeneratedId('gate:x');
        $second = VerificationRequest::withGeneratedId('gate:x');

        $this->assertSame('gate:x', $first->subject);
        $this->assertSame($first->subject, $second->subject);
        $this->assertNotNull($first->id);
        $this->assertNotNull($second->id);
        $this->assertNotSame($first->id, $second->id, 'concurrent verifications of the same subject must be distinguishable');
    }

    public function testLifecycleEventsExposeTheRequestCorrelationId(): void
    {
        $req = new VerificationRequest('gate:x', id: 'corr-123');

        $requested = new VerificationRequestedEvent($req);
        $granted = new VerificationGrantedEvent($req, VerificationResult::pass());
        $rejected = new VerificationRejectedEvent($req, VerificationResult::fail('nope'));

        $this->assertSame('corr-123', $requested->getRequestId());
        $this->assertSame('corr-123', $granted->getRequestId());
        $this->assertSame('corr-123', $rejected->getRequestId());
    }

    /**
     * The resolution seam (#7 follow-up): a caller resolving a pending verification by
     * `request_id` alone — no request-store seam exists, so `subject` is genuinely unknown —
     * builds the reconstruction via {@see VerificationRequest::forResolution()} instead of
     * fabricating a fake subject. The granted/rejected events still carry a full, valid
     * request and still expose the correlation id; only `subject` is null.
     */
    public function testGrantedAndRejectedEventsAcceptAResolutionOnlyRequest(): void
    {
        $req = VerificationRequest::forResolution('corr-456');

        $granted = new VerificationGrantedEvent($req, VerificationResult::pass());
        $rejected = new VerificationRejectedEvent($req, VerificationResult::fail('nope'));

        $this->assertNull($granted->getRequest()->subject);
        $this->assertSame('corr-456', $granted->getRequestId());
        $this->assertNull($rejected->getRequest()->subject);
        $this->assertSame('corr-456', $rejected->getRequestId());
    }
}
