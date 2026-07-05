<?php

declare(strict_types=1);

namespace Milpa\app\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Milpa\app\ValueObjects\Verification\VerificationRequest;

/**
 * Dispatched when a verification is requested but not yet resolved (async human/agent verify).
 * Listeners can route it to an approver, open a gate passage, etc.
 */
class VerificationRequestedEvent extends Event
{
    public function __construct(private readonly VerificationRequest $request)
    {
    }

    public function getRequest(): VerificationRequest
    {
        return $this->request;
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
