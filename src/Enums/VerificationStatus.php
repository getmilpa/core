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

namespace Milpa\Enums;

/**
 * Verdict of a verification. Tri-state pass/fail/waived, plus PENDING for
 * asynchronous verifiers (human/agent) whose verdict is not yet resolved.
 *
 * @see \Milpa\ValueObjects\Verification\VerificationResult
 */
enum VerificationStatus: string
{
    /** Requested but not yet resolved (async human/agent verify). */
    case PENDING = 'pending';
    /** Verified OK. */
    case PASSED = 'passed';
    /** Rejected / requirements not met. */
    case FAILED = 'failed';
    /** Exempted with justification — counts as satisfied. */
    case WAIVED = 'waived';

    /**
     * Whether the verification clears the gate. PASSED and WAIVED both satisfy it;
     * FAILED and PENDING do not.
     */
    public function isSatisfied(): bool
    {
        return $this === self::PASSED || $this === self::WAIVED;
    }

    /**
     * Whether the verdict is terminal (anything except PENDING).
     */
    public function isFinal(): bool
    {
        return $this !== self::PENDING;
    }
}
