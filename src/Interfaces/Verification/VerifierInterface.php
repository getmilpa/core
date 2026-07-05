<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Verification;

use Milpa\app\ValueObjects\Verification\VerificationContext;
use Milpa\app\ValueObjects\Verification\VerificationRequest;
use Milpa\app\ValueObjects\Verification\VerificationResult;

/**
 * the verification seam: a strategy that evaluates a {@see VerificationRequest} against a
 * {@see VerificationContext} and returns a {@see VerificationResult}. Deterministic evaluators,
 * human/agent approvals, and attest-with-evidence all implement this single contract.
 *
 * Implementations MUST NOT depend on Doctrine or resolve principals to entities — the core stays
 * framework-agnostic (ADR-001). Async verifiers return a PENDING result and resolve later via the
 * verification events.
 *
 * `human_verify` is one such verifier, exposed as a tool through the ToolRegistry.
 */
interface VerifierInterface
{
    /**
     * Evaluates the given request against the given context and returns the
     * outcome. May return a PENDING result for verifiers that resolve
     * asynchronously (e.g. awaiting a human/agent approval).
     */
    public function verify(VerificationRequest $request, VerificationContext $context): VerificationResult;
}
