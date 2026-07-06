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

namespace Milpa\Events;

use Milpa\ValueObjects\Verification\VerificationRequest;
use Milpa\ValueObjects\Verification\VerificationResult;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when a verification resolves as satisfied (passed or waived).
 */
class VerificationGrantedEvent extends Event
{
    public function __construct(
        private readonly VerificationRequest $request,
        private readonly VerificationResult $result,
    ) {
    }

    public function getRequest(): VerificationRequest
    {
        return $this->request;
    }

    public function getResult(): VerificationResult
    {
        return $this->result;
    }

    /**
     * Correlation id (#7) of the originating request, if one was assigned. Distinguishes
     * concurrent verifications that share the same `subject`.
     */
    public function getRequestId(): ?string
    {
        return $this->request->id;
    }
}
