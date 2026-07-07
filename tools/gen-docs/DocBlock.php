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

namespace Milpa\Docs;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * Parsed view of a docblock: summary, description, and the tags the API
 * reference renders (@param / @return / @throws / @since / @deprecated).
 */
final class DocBlock
{
    /**
     * @param list<array{type:string,name:string,desc:string}> $params
     * @param array{type:string,desc:string}|null              $return
     * @param list<array{type:string,desc:string}>             $throws
     */
    private function __construct(
        public readonly string $summary,
        public readonly string $description,
        public readonly array $params,
        public readonly ?array $return,
        public readonly array $throws,
        public readonly ?string $since,
        public readonly ?string $deprecated,
    ) {
    }

    public static function of(string|false $raw): self
    {
        if ($raw === false || trim($raw) === '') {
            return new self('', '', [], null, [], null, null);
        }

        $config = new ParserConfig(usedAttributes: []);
        $lexer = new Lexer($config);
        $const = new ConstExprParser($config);
        $parser = new PhpDocParser($config, new TypeParser($config, $const), $const);
        $node = $parser->parse(new TokenIterator($lexer->tokenize($raw)));

        // summary = first non-empty text node; description = the rest joined.
        $texts = [];
        foreach ($node->children as $child) {
            if ($child instanceof PhpDocTextNode) {
                $t = trim((string) $child);
                if ($t !== '') {
                    $texts[] = $t;
                }
            }
        }
        // phpdoc-parser packs summary + description into a single text node,
        // with paragraphs separated by a blank line; split that blob here
        // rather than assuming one PhpDocTextNode per paragraph.
        $blob = trim(implode("\n\n", $texts));
        $parts = preg_split('/\n\s*\n/', $blob, 2) ?: [];
        $summary = $parts[0] ?? '';
        $description = trim($parts[1] ?? '');

        $params = [];
        foreach ($node->getParamTagValues() as $p) {
            $params[] = ['type' => (string) $p->type, 'name' => $p->parameterName, 'desc' => trim($p->description)];
        }
        $returns = $node->getReturnTagValues();
        $return = $returns === [] ? null : ['type' => (string) $returns[0]->type, 'desc' => trim($returns[0]->description)];
        $throws = [];
        foreach ($node->getThrowsTagValues() as $t) {
            $throws[] = ['type' => (string) $t->type, 'desc' => trim($t->description)];
        }
        $since = null;
        foreach ($node->getTagsByName('@since') as $s) {
            $since = trim((string) $s->value) ?: null;
        }
        $deprecated = null;
        foreach ($node->getTagsByName('@deprecated') as $d) {
            $deprecated = trim((string) $d->value) ?: null;
        }

        return new self($summary, $description, $params, $return, $throws, $since, $deprecated);
    }
}
