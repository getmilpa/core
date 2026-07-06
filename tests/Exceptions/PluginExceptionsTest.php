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

namespace Milpa\Tests\Exceptions;

use Milpa\Exceptions\MilpaExceptionInterface;
use Milpa\Exceptions\Plugin\PluginBootException;
use Milpa\Exceptions\Plugin\PluginDependencyException;
use Milpa\Exceptions\Plugin\PluginInstallException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

final class PluginExceptionsTest extends TestCase
{
    /**
     * @return list<class-string>
     */
    public static function pluginExceptionProvider(): array
    {
        return [
            [PluginBootException::class],
            [PluginDependencyException::class],
            [PluginInstallException::class],
        ];
    }

    #[DataProvider('pluginExceptionProvider')]
    public function testEachPluginExceptionImplementsTheMarker(string $fqcn): void
    {
        $this->assertTrue(
            is_a($fqcn, MilpaExceptionInterface::class, true),
            sprintf('%s must implement MilpaExceptionInterface.', $fqcn)
        );
        $this->assertTrue(
            is_a($fqcn, Throwable::class, true),
            sprintf('%s must be Throwable.', $fqcn)
        );
    }

    public function testPluginBootExceptionNamesThePlugin(): void
    {
        $exception = PluginBootException::forPlugin('MiPlugin', 'missing service X');

        $this->assertInstanceOf(MilpaExceptionInterface::class, $exception);
        $this->assertStringContainsString('MiPlugin', $exception->getMessage());
        $this->assertStringContainsString('missing service X', $exception->getMessage());
    }

    public function testPluginDependencyExceptionNamesPluginAndDependency(): void
    {
        $exception = PluginDependencyException::unmet('MiPlugin', 'OtherPlugin');

        $this->assertInstanceOf(MilpaExceptionInterface::class, $exception);
        $this->assertStringContainsString('MiPlugin', $exception->getMessage());
        $this->assertStringContainsString('OtherPlugin', $exception->getMessage());
    }

    public function testPluginInstallExceptionNamesTheSource(): void
    {
        $exception = PluginInstallException::forSource('owner/repo:^2.0', 'download failed');

        $this->assertInstanceOf(MilpaExceptionInterface::class, $exception);
        $this->assertStringContainsString('owner/repo:^2.0', $exception->getMessage());
        $this->assertStringContainsString('download failed', $exception->getMessage());
    }

    public function testEachExceptionCanBeThrownAndCaughtAsTheMarker(): void
    {
        try {
            throw PluginBootException::forPlugin('MiPlugin', 'boot failure');
        } catch (MilpaExceptionInterface $caught) {
            $this->assertInstanceOf(PluginBootException::class, $caught);
            return;
        }

        $this->fail('Expected PluginBootException to be thrown and caught as MilpaExceptionInterface.');
    }
}
