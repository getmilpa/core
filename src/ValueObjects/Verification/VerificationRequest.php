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

namespace Milpa\ValueObjects\Verification;

use Milpa\Enums\ApprovalPolicy;
use Milpa\Support\UuidGenerator;

/**
 * What is being verified, and under which approval policy. `subject` is an opaque
 * identifier the verifier understands (e.g. "gate:proposal.sent", "task:42"); the core never
 * resolves it. `payload` is arbitrary verifier input. `requestedBy` is an opaque principal.
 *
 * `id` is an optional correlation id (#7): two requests can share the same `subject` (e.g.
 * concurrent verifications of the same gate) but remain distinguishable by `id`. It is opaque
 * to the core and reachable from the granted/rejected lifecycle events so callers can correlate
 * a verdict back to the request that produced it.
 */
final class VerificationRequest
{
    use UuidGenerator;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $subject,
        public readonly ApprovalPolicy $policy = ApprovalPolicy::SINGLE,
        public readonly array $payload = [],
        public readonly ?string $requestedBy = null,
        public readonly ?string $id = null,
    ) {
        if (trim($subject) === '') {
            throw new \InvalidArgumentException('VerificationRequest requires a non-empty "subject".');
        }
    }

    /**
     * Build a request with a freshly generated correlation `id`.
     *
     * @param array<string, mixed> $payload
     */
    public static function withGeneratedId(
        string $subject,
        ApprovalPolicy $policy = ApprovalPolicy::SINGLE,
        array $payload = [],
        ?string $requestedBy = null,
    ): self {
        return new self($subject, $policy, $payload, $requestedBy, self::generateUuid());
    }
}
