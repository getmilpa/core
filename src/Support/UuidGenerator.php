<?php

declare(strict_types=1);

namespace Milpa\app\Support;

/**
 * Generates RFC-4122 v4 UUIDs with no external dependency — the single, framework-agnostic home
 * for the identifier utility that value objects and entities across plugins need.
 *
 * `use UuidGenerator;` then call `self::generateUuid()` in the constructor.
 */
trait UuidGenerator
{
    protected static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
