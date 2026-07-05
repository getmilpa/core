<?php

declare(strict_types=1);

namespace Milpa\Docs;

/**
 * Builds the tokenized `<code>` markup for `.mui-api__signature`, from a
 * `ReflectionMethod`. Mirrors the code cluster's `.tok-*` spans consumed by
 * `@milpa/design`'s `mui-api` component (see `milpa-api.contract.json`).
 */
final class Signature
{
    public static function of(\ReflectionMethod $m): string
    {
        $keywords = [];
        if ($m->isAbstract()) {
            $keywords[] = 'abstract';
        }
        if ($m->isPublic()) {
            $keywords[] = 'public';
        } elseif ($m->isProtected()) {
            $keywords[] = 'protected';
        } elseif ($m->isPrivate()) {
            $keywords[] = 'private';
        }
        if ($m->isStatic()) {
            $keywords[] = 'static';
        }
        $keywords[] = 'function';

        $kw = htmlspecialchars(implode(' ', $keywords), ENT_QUOTES);
        $name = htmlspecialchars($m->getName(), ENT_QUOTES);

        $params = array_map(
            static fn (\ReflectionParameter $p): string => self::renderParam($p),
            $m->getParameters()
        );

        $returnHtml = self::renderType($m->getReturnType());
        $tail = $returnHtml === '' ? '' : ' ' . $returnHtml;

        return '<span class="tok-kw">' . $kw . '</span> '
            . '<span class="tok-fn">' . $name . '</span>'
            . '<span class="tok-punc">(</span>'
            . implode('<span class="tok-punc">,</span> ', $params)
            . '<span class="tok-punc">):</span>' . $tail;
    }

    private static function renderParam(\ReflectionParameter $p): string
    {
        $typeHtml = self::renderType($p->getType());
        $prefix = $typeHtml === '' ? '' : $typeHtml . ' ';
        $variadic = $p->isVariadic() ? '...' : '';
        $varHtml = '<span class="tok-var">$' . htmlspecialchars($p->getName(), ENT_QUOTES) . '</span>';

        $default = '';
        if (!$p->isVariadic() && $p->isDefaultValueAvailable()) {
            $default = ' = ' . self::renderDefaultValue($p);
        }

        return $prefix . $variadic . $varHtml . $default;
    }

    private static function renderDefaultValue(\ReflectionParameter $p): string
    {
        if ($p->isDefaultValueConstant()) {
            return htmlspecialchars((string) $p->getDefaultValueConstantName(), ENT_QUOTES);
        }

        return htmlspecialchars(self::valueToString($p->getDefaultValue()), ENT_QUOTES);
    }

    private static function valueToString(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_string($value) => "'" . $value . "'",
            is_array($value) => '[]',
            default => (string) $value,
        };
    }

    private static function renderType(?\ReflectionType $type): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof \ReflectionUnionType) {
            return implode('|', array_map(
                static function (\ReflectionType $t): string {
                    $rendered = self::renderTypePart($t);
                    if ($t instanceof \ReflectionIntersectionType) {
                        // DNF grouping: (Foo&Bar)|null must keep its parens to stay
                        // reconstructable as PHP. The symmetric case — an
                        // intersection member that is itself a union — isn't
                        // expressible in PHP, so no guard is needed there.
                        return '<span class="tok-punc">(</span>' . $rendered . '<span class="tok-punc">)</span>';
                    }

                    return $rendered;
                },
                $type->getTypes()
            ));
        }

        if ($type instanceof \ReflectionIntersectionType) {
            return implode('&', array_map(
                static fn (\ReflectionType $t): string => self::renderTypePart($t),
                $type->getTypes()
            ));
        }

        /** @var \ReflectionNamedType $type */
        $rendered = self::renderTypePart($type);
        if ($type->allowsNull() && strtolower($type->getName()) !== 'null') {
            return '?' . $rendered;
        }

        return $rendered;
    }

    private static function renderTypePart(\ReflectionType $type): string
    {
        if ($type instanceof \ReflectionIntersectionType) {
            return implode('&', array_map(
                static fn (\ReflectionType $t): string => self::renderTypePart($t),
                $type->getTypes()
            ));
        }

        /** @var \ReflectionNamedType $type */
        $name = htmlspecialchars($type->getName(), ENT_QUOTES);

        return $type->isBuiltin() ? '<span class="tok-kw">' . $name . '</span>' : $name;
    }
}
