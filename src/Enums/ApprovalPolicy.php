<?php

declare(strict_types=1);

namespace Milpa\app\Enums;

/**
 * How many independent approvals a verification requires. Policy *enforcement*
 * (e.g. anti-self-approval, quorum sizing) lives in the verifier implementation, not here.
 */
enum ApprovalPolicy: string
{
    /** One approval. */
    case SINGLE = 'single';
    /** Two independent approvals. */
    case DUAL = 'dual';
    /** A dynamic number of approvals (quorum size decided by the verifier). */
    case QUORUM = 'quorum';
    /** Auto-approved once sufficient evidence is attached. */
    case AUTO = 'auto';

    /**
     * Minimum distinct approvals required, or 0 when the count is not statically known
     * (QUORUM = dynamic, AUTO = evidence-driven).
     */
    public function requiredApprovals(): int
    {
        return match ($this) {
            self::SINGLE => 1,
            self::DUAL => 2,
            self::QUORUM, self::AUTO => 0,
        };
    }

    /**
     * Whether this policy auto-approves without requiring explicit human approvals.
     */
    public function isAutomatic(): bool
    {
        return $this === self::AUTO;
    }
}
