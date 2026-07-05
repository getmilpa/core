<?php

declare(strict_types=1);

namespace Milpa\app\ValueObjects\Verification;

/**
 * Ambient information a verifier evaluates against: the acting principal, the set of
 * approver principals gathered so far, and any evidence/attestations. All opaque — no Doctrine,
 * no entity resolution. The verifier decides whether this satisfies the request's policy.
 */
final class VerificationContext
{
    /**
     * @param list<string>         $approvals opaque approver principals
     * @param array<string, mixed> $evidence  generic attestations keyed by kind
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly ?string $principal = null,
        public readonly array $approvals = [],
        public readonly array $evidence = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Count of *distinct* approver principals (an approver cannot count twice).
     */
    public function approvalCount(): int
    {
        return count(array_unique($this->approvals));
    }

    /**
     * Whether any evidence/attestation was supplied.
     */
    public function hasEvidence(): bool
    {
        return $this->evidence !== [];
    }
}
