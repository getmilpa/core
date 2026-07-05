<?php

declare(strict_types=1);

namespace Milpa\app\Events;

use Milpa\app\ValueObjects\Verification\VerificationRequest;
use Milpa\app\ValueObjects\Verification\VerificationResult;
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
