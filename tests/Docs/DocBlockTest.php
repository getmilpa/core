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

use Milpa\Docs\DocBlock;
use PHPUnit\Framework\TestCase;

final class DocBlockTest extends TestCase
{
    public function testParsesAllTags(): void
    {
        $raw = "/**\n * Dispatches the request.\n *\n * Longer detail here.\n * @param Request \$request the incoming request\n * @return Response the output\n * @throws NotFoundException when unmatched\n * @since 0.9\n * @deprecated use handle2()\n */";
        $d = DocBlock::of($raw);

        $this->assertSame('Dispatches the request.', $d->summary);
        $this->assertSame('Longer detail here.', $d->description);
        $this->assertSame([['type' => 'Request', 'name' => '$request', 'desc' => 'the incoming request']], $d->params);
        $this->assertSame(['type' => 'Response', 'desc' => 'the output'], $d->return);
        $this->assertSame([['type' => 'NotFoundException', 'desc' => 'when unmatched']], $d->throws);
        $this->assertSame('0.9', $d->since);
        $this->assertSame('use handle2()', $d->deprecated);
    }

    public function testEmptyDocblockYieldsEmpty(): void
    {
        $d = DocBlock::of(false);
        $this->assertSame('', $d->summary);
        $this->assertSame([], $d->params);
        $this->assertNull($d->return);
        $this->assertNull($d->deprecated);
    }
}
