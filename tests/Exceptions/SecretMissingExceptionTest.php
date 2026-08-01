<?php

declare(strict_types=1);

namespace Milpa\Tests\Exceptions;

use Milpa\Exceptions\MilpaExceptionInterface;
use Milpa\Exceptions\SecretMissingException;
use PHPUnit\Framework\TestCase;

final class SecretMissingExceptionTest extends TestCase
{
    public function test_required_carries_the_learnable_code_and_names_the_secret(): void
    {
        $e = SecretMissingException::required('MYSQL_PASSWORD');

        self::assertInstanceOf(MilpaExceptionInterface::class, $e);
        self::assertSame('MILPA_SECRET_MISSING', $e->errorCode());
        self::assertStringContainsString('[MILPA_SECRET_MISSING]', $e->getMessage());
        self::assertStringContainsString('MYSQL_PASSWORD', $e->getMessage());
        self::assertStringContainsString("insecure defaults like 'root'", $e->getMessage());
        self::assertStringContainsString('academy.milpa.lat/learn/fundamentos/secretos-config', $e->getMessage());
    }
}
