<?php

declare(strict_types=1);

namespace Milpa\app\ValueObjects\Verification;

use Milpa\app\Enums\VerificationStatus;

/**
 * Immutable verdict of a verification: tri-state (passed / failed / waived) + PENDING for
 * async verifiers. Framework-agnostic — carries only opaque strings, no Doctrine, no domain types.
 *
 * `principal` and `verifier` are opaque identifiers (e.g. "user:42", "human_verify"); the core
 * never resolves them to entities. `missing` is a generic list of unmet requirement keys.
 *
 * @see \Milpa\app\Interfaces\Verification\VerifierInterface
 */
final class VerificationResult
{
    /**
     * @param list<string>         $missing
     * @param array<string, mixed> $metadata
     */
    private function __construct(
        public readonly VerificationStatus $status,
        public readonly ?string $reason = null,
        public readonly ?string $verifier = null,
        public readonly ?string $principal = null,
        public readonly array $missing = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Build a PASSED result: the verification was satisfied on its merits.
     *
     * @param array<string, mixed> $metadata
     */
    public static function pass(?string $verifier = null, ?string $principal = null, array $metadata = []): self
    {
        return new self(VerificationStatus::PASSED, verifier: $verifier, principal: $principal, metadata: $metadata);
    }

    /**
     * Build a FAILED result, optionally listing the unmet requirement keys in `$missing`.
     *
     * @param list<string>         $missing
     * @param array<string, mixed> $metadata
     */
    public static function fail(string $reason, array $missing = [], ?string $verifier = null, array $metadata = [], ?string $principal = null): self
    {
        return new self(VerificationStatus::FAILED, reason: $reason, verifier: $verifier, principal: $principal, missing: $missing, metadata: $metadata);
    }

    /**
     * Exempted with a justification — clears the gate despite not passing on merit.
     *
     * @param array<string, mixed> $metadata
     */
    public static function waived(string $reason, ?string $principal = null, ?string $verifier = null, array $metadata = []): self
    {
        return new self(VerificationStatus::WAIVED, reason: $reason, verifier: $verifier, principal: $principal, metadata: $metadata);
    }

    /**
     * Requested but unresolved (async human/agent verify).
     *
     * @param array<string, mixed> $metadata
     */
    public static function pending(?string $verifier = null, array $metadata = []): self
    {
        return new self(VerificationStatus::PENDING, verifier: $verifier, metadata: $metadata);
    }

    /**
     * Whether the gate this result represents is satisfied, i.e. PASSED or WAIVED.
     *
     * {@see VerificationStatus::isSatisfied()}
     */
    public function isSatisfied(): bool
    {
        return $this->status->isSatisfied();
    }

    /**
     * Whether this result is still awaiting an async human/agent verifier.
     */
    public function isPending(): bool
    {
        return $this->status === VerificationStatus::PENDING;
    }

    /**
     * Whether this result is terminal, i.e. will not transition to another status.
     *
     * {@see VerificationStatus::isFinal()}
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Whether this result carries any unmet requirement keys.
     */
    public function hasMissing(): bool
    {
        return $this->missing !== [];
    }

    /**
     * Serialize to a plain array for logging/transport (status as its scalar `.value`).
     *
     * @return array{status:string, reason:?string, verifier:?string, principal:?string, missing:list<string>, metadata:array<string,mixed>}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'reason' => $this->reason,
            'verifier' => $this->verifier,
            'principal' => $this->principal,
            'missing' => $this->missing,
            'metadata' => $this->metadata,
        ];
    }
}
