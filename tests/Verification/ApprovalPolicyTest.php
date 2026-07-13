<?php

/**
 * This file is part of Milpa Core — the framework-agnostic core of the Milpa PHP framework.
 *
 * (c) Rodrigo Vicente - TeamX Agency — https://teamx.agency <hola@teamx.agency>
 *
 * @license Apache-2.0
 *
 * @link    https://github.com/getmilpa/core
 */

declare(strict_types=1);

namespace Milpa\Tests\Verification;

use PHPUnit\Framework\TestCase;
use Milpa\Enums\ApprovalPolicy;

/**
 * ApprovalPolicy ported into core, generic (no product labels).
 */
final class ApprovalPolicyTest extends TestCase
{
    public function testCases(): void
    {
        $this->assertSame('single', ApprovalPolicy::SINGLE->value);
        $this->assertSame('dual', ApprovalPolicy::DUAL->value);
        $this->assertSame('quorum', ApprovalPolicy::QUORUM->value);
        $this->assertSame('auto', ApprovalPolicy::AUTO->value);
    }

    public function testRequiredApprovals(): void
    {
        $this->assertSame(1, ApprovalPolicy::SINGLE->requiredApprovals());
        $this->assertSame(2, ApprovalPolicy::DUAL->requiredApprovals());
        $this->assertSame(0, ApprovalPolicy::QUORUM->requiredApprovals(), 'dynamic');
        $this->assertSame(0, ApprovalPolicy::AUTO->requiredApprovals(), 'evidence-driven');
    }

    public function testIsAutomatic(): void
    {
        $this->assertTrue(ApprovalPolicy::AUTO->isAutomatic());
        $this->assertFalse(ApprovalPolicy::SINGLE->isAutomatic());
        $this->assertFalse(ApprovalPolicy::DUAL->isAutomatic());
        $this->assertFalse(ApprovalPolicy::QUORUM->isAutomatic());
    }
}
