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

namespace Milpa\Tests\Docs;

use Milpa\Docs\Signature;
use PHPUnit\Framework\TestCase;

final class SignatureTest extends TestCase
{
    public function testRendersTokenizedSignature(): void
    {
        $fixture = new class () {
            public function handle(string $path, int $code = 200): bool
            {
                return $path !== '' && $code > 0;
            }
        };
        $html = Signature::of(new \ReflectionMethod($fixture, 'handle'));

        $this->assertStringContainsString('<span class="tok-kw">public function</span>', $html);
        $this->assertStringContainsString('<span class="tok-fn">handle</span>', $html);
        $this->assertStringContainsString('<span class="tok-var">$path</span>', $html);
        $this->assertStringContainsString('<span class="tok-var">$code</span>', $html);
        $this->assertStringContainsString('<span class="tok-kw">string</span>', $html);
        $this->assertStringContainsString('<span class="tok-kw">int</span>', $html);
        $this->assertStringContainsString('<span class="tok-kw">bool</span>', $html); // return type
    }

    public function testClassTypesRenderPlainNotWrappedAsKeywords(): void
    {
        $fixture = new class () {
            public function route(\ReflectionClass $rc): \ReflectionMethod
            {
                return new \ReflectionMethod($rc->getName(), 'route');
            }
        };
        $html = Signature::of(new \ReflectionMethod($fixture, 'route'));

        // Class types must NOT be wrapped in tok-kw (that's reserved for builtins).
        $this->assertStringNotContainsString('<span class="tok-kw">ReflectionClass', $html);
        $this->assertStringNotContainsString('<span class="tok-kw">ReflectionMethod', $html);
        // But the bare class names must still be present, unwrapped.
        $this->assertStringContainsString('ReflectionClass', $html);
        $this->assertStringContainsString('ReflectionMethod', $html);
    }

    public function testDnfUnionKeepsParensAroundIntersectionMember(): void
    {
        $fixture = new class () {
            public function combo((\Countable&\ArrayAccess)|null $items): void
            {
            }
        };
        $html = Signature::of(new \ReflectionMethod($fixture, 'combo'));

        // (Countable&ArrayAccess)|null must keep its grouping parens, or the
        // rendered signature is no longer reconstructable as valid PHP.
        $this->assertStringContainsString(
            '<span class="tok-punc">(</span>Countable&ArrayAccess<span class="tok-punc">)</span>'
            . '|<span class="tok-kw">null</span>',
            $html
        );
    }
}
