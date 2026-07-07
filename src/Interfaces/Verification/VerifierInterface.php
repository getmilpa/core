<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) TeamX — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Interfaces\Verification;

use Milpa\Events\VerificationGrantedEvent;
use Milpa\Events\VerificationRejectedEvent;
use Milpa\Events\VerificationRequestedEvent;
use Milpa\ValueObjects\Verification\VerificationContext;
use Milpa\ValueObjects\Verification\VerificationRequest;
use Milpa\ValueObjects\Verification\VerificationResult;

/**
 * the verification seam: a strategy that evaluates a {@see VerificationRequest} against a
 * {@see VerificationContext} and returns a {@see VerificationResult}. Deterministic evaluators,
 * human/agent approvals, and attest-with-evidence all implement this single contract.
 *
 * Implementations MUST NOT depend on Doctrine or resolve principals to entities — the core stays
 * framework-agnostic (ADR-001). Async verifiers return a PENDING result and resolve later via the
 * verification events: {@see VerificationRequestedEvent} (`verification.requested`),
 * {@see VerificationGrantedEvent} (`verification.granted`), and
 * {@see VerificationRejectedEvent} (`verification.rejected`). Each is dispatched with a payload
 * of exactly `['event' => $event]` — the event object itself, not its fields flattened into the
 * payload array (see each event class's own docblock for the exact contract).
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
