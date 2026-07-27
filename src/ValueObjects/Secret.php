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

namespace Milpa\ValueObjects;

use Milpa\Exceptions\SecretMissingException;

/**
 * An opaque wrapper around a raw secret (a database/redis password, an API key, …) plus its
 * non-secret {@see self::$name}. Mirrors the family secret-bearing idiom (see Milpa\Auth\Credential):
 * the value is a `private readonly` property marked `#[\SensitiveParameter]` (redacted from
 * stack-trace argument dumps), {@see self::__debugInfo()} redacts it, and {@see self::__serialize()}
 * and {@see self::__clone()} refuse outright. There is deliberately NO `__toString()`. The one
 * deliberate exit is {@see self::value()}, called only at an audited plaintext boundary.
 *
 * Secret-bearing objects redact the common logging paths. `var_export`/`(array)` remain able to reach
 * the private value — they are prohibited introspection/persistence antipatterns, not defended here.
 *
 * `$name` is a stable, non-secret identifier (e.g. 'MYSQL_PASSWORD'). It MUST NEVER contain the value,
 * a fragment of it, a private host, or any sensitive material.
 */
final class Secret
{
    public function __construct(
        private readonly string $name,
        #[\SensitiveParameter]
        private readonly string $value,
    ) {
    }

    /**
     * A required secret. Absence — null OR blank-after-trim — fails closed. The stored value is
     * NEVER trimmed; `trim()` is used only to detect absence, so a password with intentional
     * leading/trailing spaces survives verbatim.
     */
    public static function required(string $name, #[\SensitiveParameter] ?string $raw): self
    {
        if ($raw === null || trim($raw) === '') {
            throw SecretMissingException::required($name);
        }

        return new self($name, $raw);
    }

    /**
     * An optional secret (e.g. a Redis password on an unauthenticated instance). Absent → null.
     */
    public static function optional(string $name, #[\SensitiveParameter] ?string $raw): ?self
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return new self($name, $raw);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    public function __debugInfo(): array
    {
        return ['name' => $this->name, 'value' => '[redacted]'];
    }

    /**
     * @return array<string, never>
     *
     * @throws \LogicException always
     */
    public function __serialize(): array
    {
        throw new \LogicException('Secret must not be serialized — it carries a secret.');
    }

    public function __clone(): void
    {
        throw new \LogicException('Secret must not be cloned — it carries a secret.');
    }
}
