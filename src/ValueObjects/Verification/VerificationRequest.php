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
 * `subject` is nullable. It is REQUIRED (and, if given, must not be empty/whitespace-only) for
 * a request that opens a verification — you cannot open a verification without saying what is
 * being verified. `null` is reserved for a resolve-only reconstruction: resolution is keyed by
 * `id`, not by `subject`, and core defines no request-store seam, so a caller resolving a
 * pending verification by `request_id` alone (the common case) genuinely has no subject to
 * supply. Use {@see self::forResolution()} for that case instead of fabricating one (e.g.
 * falling back to `id` as `subject`, which previously forced that exact workaround on
 * `milpa/tool-runtime`'s `resolve_verification` tool).
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
        public readonly ?string $subject,
        public readonly ApprovalPolicy $policy = ApprovalPolicy::SINGLE,
        public readonly array $payload = [],
        public readonly ?string $requestedBy = null,
        public readonly ?string $id = null,
    ) {
        if ($subject !== null && trim($subject) === '') {
            throw new \InvalidArgumentException('VerificationRequest "subject" must not be empty when provided; pass null (or use ::forResolution()) for a resolve-only reconstruction.');
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

    /**
     * Build a resolve-only reconstruction: identified purely by `id`, with `subject` left
     * `null` instead of fabricated. This is the resolution seam — resolving a pending
     * verification only ever needs the `request_id` a prior `verify()` call handed back; core
     * defines no request-store, so nothing else about the original request can honestly be
     * recovered here. Named constructor (rather than `new self(null, id: $id)`) so the
     * omission reads as deliberate — "resolving by id" — not as a caller forgetting `subject`.
     */
    public static function forResolution(string $id, ApprovalPolicy $policy = ApprovalPolicy::SINGLE): self
    {
        if (trim($id) === '') {
            throw new \InvalidArgumentException('VerificationRequest::forResolution() requires a non-empty "id".');
        }

        return new self(null, $policy, id: $id);
    }
}
