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

namespace Milpa\Tests\Docs;

use Milpa\Docs\ApiRenderer;
use PHPUnit\Framework\TestCase;

/** @see the @milpa/design `milpa-api.contract.json` artifact for the exact HTML contract. */
final class ApiRendererTest extends TestCase
{
    public function testMethodEntryHasContractStructure(): void
    {
        $fixture = new class () {
            /**
             * Dispatches the request to the matching controller.
             *
             * @param string $path the request path
             *
             * @return bool whether it matched
             *
             * @throws \RuntimeException on failure
             */
            public function handle(string $path): bool
            {
                return $path !== '';
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'handle'));

        $this->assertStringContainsString('class="mui-api"', $html);
        $this->assertStringContainsString('class="mui-api__name"', $html);
        $this->assertStringContainsString('class="mui-api__signature"', $html);
        $this->assertStringContainsString('Dispatches the request', $html); // desc
        $this->assertStringContainsString('mui-api__params', $html);         // params table
        $this->assertStringContainsString('mui-table', $html);
        $this->assertStringContainsString('mui-api__type', $html);           // param/throws type
        $this->assertStringContainsString('Returns', $html);
        $this->assertStringContainsString('Throws', $html);
    }

    public function testDeprecatedRendersBadgeAndNote(): void
    {
        $fixture = new class () {
            /**
             * Old renderer.
             *
             * @deprecated use stream() instead
             */
            public function render(): string
            {
                return '';
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'render'));
        $this->assertStringContainsString('mui-badge--deprecated', $html);
        $this->assertStringContainsString('mui-api__deprecated-note', $html);
    }

    public function testDeprecatedBareIntegerIsNotTreatedAsVersion(): void
    {
        $fixture = new class () {
            /**
             * Old renderer.
             *
             * @deprecated 10 years, use newMethod() instead
             */
            public function render(): string
            {
                return '';
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'render'));

        $this->assertStringContainsString('10 years', $html);
        $this->assertStringNotContainsString('v10', $html);
    }

    public function testReturnAndThrowsWithoutProseOmitEmptyParagraphs(): void
    {
        $fixture = new class () {
            /**
             * Old renderer.
             *
             * @return bool
             *
             * @throws \RuntimeException
             */
            public function render(): bool
            {
                return true;
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'render'));

        $this->assertStringNotContainsString('mui-api__desc"></p>', $html);
        $this->assertStringNotContainsString('Returns', $html);
        $this->assertStringContainsString('Throws', $html);
        $this->assertStringContainsString('mui-api__type">\RuntimeException', $html);
    }

    public function testParamsTableIsBuiltFromReflectionWithoutAnyParamTags(): void
    {
        $fixture = new class () {
            public function route(string $path, int $code): void
            {
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'route'));

        $this->assertStringContainsString('mui-api__params', $html);
        $this->assertStringContainsString('mui-table__lead">$path<', $html);
        $this->assertStringContainsString('mui-api__type">string<', $html);
        $this->assertStringContainsString('mui-table__lead">$code<', $html);
        $this->assertStringContainsString('mui-api__type">int<', $html);
    }

    public function testParamsTableShowsBothParamsWithPartialDocCoverage(): void
    {
        $fixture = new class () {
            /**
             * Does a thing.
             *
             * @param string $path the request path
             */
            public function route(string $path, int $code): void
            {
            }
        };
        $html = (new ApiRenderer())->method(new \ReflectionMethod($fixture, 'route'));

        $this->assertStringContainsString('mui-table__lead">$path<', $html);
        $this->assertStringContainsString('the request path', $html);
        $this->assertStringContainsString('mui-table__lead">$code<', $html);
        $this->assertStringContainsString('mui-api__type">int<', $html);
    }

    public function testTypeEntryHasContractStructure(): void
    {
        /**
         * A value object representing a semantic version.
         *
         * @since 1.2
         * @deprecated 2.0 use NewVersion instead
         */
        $fixture = new class () {
        };

        $html = (new ApiRenderer())->type(new \ReflectionClass($fixture));

        $this->assertStringContainsString('class="mui-api__name"', $html);
        $this->assertStringContainsString('A value object representing a semantic version.', $html);
        $this->assertStringContainsString('mui-badge--since', $html);
        $this->assertStringContainsString('Since v1.2', $html);
        $this->assertStringContainsString('mui-badge--deprecated', $html);
        $this->assertStringContainsString('Deprecated in v2.0', $html);
    }
}
