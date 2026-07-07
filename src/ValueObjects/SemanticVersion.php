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

namespace Milpa\ValueObjects;

/**
 * Immutable value object for semantic versioning (semver.org).
 *
 * Supports:
 * - Parsing: SemanticVersion::parse('1.2.3-beta.1+build.42')
 * - Comparison: $v1->greaterThan($v2), $v1->equals($v2)
 * - Constraints: $v->satisfies('^2.0'), $v->satisfies('>=1.5 <3.0')
 * - Incrementing: $v->incrementMajor(), $v->incrementMinor(), $v->incrementPatch()
 */
final class SemanticVersion implements \Stringable
{
    public function __construct(
        public readonly int $major,
        public readonly int $minor,
        public readonly int $patch,
        public readonly ?string $preRelease = null,
        public readonly ?string $build = null
    ) {
    }

    /**
     * Parse a version string.
     *
     * Accepts: "1.2.3", "v1.2.3", "1.2.3-beta", "1.2.3-beta.1+build.42", "1.2", "1"
     *
     * @throws \InvalidArgumentException If the version string is not valid semver
     */
    public static function parse(string $version): self
    {
        $version = ltrim(trim($version), 'vV');

        $pattern = '/^(\d+)(?:\.(\d+))?(?:\.(\d+))?(?:-([a-zA-Z0-9.]+))?(?:\+([a-zA-Z0-9.]+))?$/';

        if (!preg_match($pattern, $version, $matches)) {
            throw new \InvalidArgumentException("Invalid semantic version: '{$version}'");
        }

        return new self(
            major: (int) $matches[1],
            minor: isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0,
            patch: isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : 0,
            preRelease: isset($matches[4]) && $matches[4] !== '' ? $matches[4] : null,
            build: isset($matches[5]) ? $matches[5] : null,
        );
    }

    /**
     * Try to parse a version string, return null on failure instead of throwing.
     */
    public static function tryParse(string $version): ?self
    {
        try {
            return self::parse($version);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Check if this version satisfies a constraint string.
     *
     * Supported constraint formats:
     *   "^1.2.3"      → >=1.2.3 <2.0.0 (caret — compatible with)
     *   "~1.2.3"      → >=1.2.3 <1.3.0 (tilde — patch-level changes)
     *   ">=1.0"       → greater or equal
     *   ">1.0"        → strictly greater
     *   "<=2.0"       → less or equal
     *   "<2.0"        → strictly less
     *   "1.2.3"       → exact match
     *   ">=1.0 <3.0"  → range (space-separated AND)
     *   "*"           → matches anything
     */
    public function satisfies(string $constraint): bool
    {
        $constraint = trim($constraint);

        if ($constraint === '*' || $constraint === '') {
            return true;
        }

        // Space-separated constraints are AND-ed
        $parts = preg_split('/\s+/', $constraint);
        if ($parts === false) {
            return false;
        }

        foreach ($parts as $part) {
            if (!$this->satisfiesSingle($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether this version sorts strictly after $other.
     */
    public function greaterThan(self $other): bool
    {
        return $this->compareTo($other) > 0;
    }

    /**
     * Whether this version sorts after or equal to $other.
     */
    public function greaterThanOrEqual(self $other): bool
    {
        return $this->compareTo($other) >= 0;
    }

    /**
     * Whether this version sorts strictly before $other.
     */
    public function lessThan(self $other): bool
    {
        return $this->compareTo($other) < 0;
    }

    /**
     * Whether this version sorts before or equal to $other.
     */
    public function lessThanOrEqual(self $other): bool
    {
        return $this->compareTo($other) <= 0;
    }

    /**
     * Whether this version has the same precedence as $other (build metadata is ignored, per semver).
     */
    public function equals(self $other): bool
    {
        return $this->compareTo($other) === 0;
    }

    /**
     * Compare two versions.
     *
     * @return int -1 if $this < $other, 0 if equal, 1 if $this > $other
     */
    public function compareTo(self $other): int
    {
        if ($this->major !== $other->major) {
            return $this->major <=> $other->major;
        }
        if ($this->minor !== $other->minor) {
            return $this->minor <=> $other->minor;
        }
        if ($this->patch !== $other->patch) {
            return $this->patch <=> $other->patch;
        }

        // Pre-release versions have lower precedence than the associated normal version
        // 1.0.0-alpha < 1.0.0
        if ($this->preRelease === null && $other->preRelease !== null) {
            return 1;
        }
        if ($this->preRelease !== null && $other->preRelease === null) {
            return -1;
        }
        if ($this->preRelease !== null) {
            return $this->comparePreRelease($this->preRelease, $other->preRelease);
        }

        return 0;
    }

    /**
     * Whether this is a stable release, i.e. it carries no pre-release identifier.
     */
    public function isStable(): bool
    {
        return $this->preRelease === null;
    }

    /**
     * Returns a new version with major incremented and minor/patch reset to 0 (pre-release/build dropped).
     */
    public function incrementMajor(): self
    {
        return new self($this->major + 1, 0, 0);
    }

    /**
     * Returns a new version with minor incremented, patch reset to 0, major unchanged (pre-release/build dropped).
     */
    public function incrementMinor(): self
    {
        return new self($this->major, $this->minor + 1, 0);
    }

    /**
     * Returns a new version with patch incremented, major/minor unchanged (pre-release/build dropped).
     */
    public function incrementPatch(): self
    {
        return new self($this->major, $this->minor, $this->patch + 1);
    }

    public function __toString(): string
    {
        $v = "{$this->major}.{$this->minor}.{$this->patch}";
        if ($this->preRelease !== null) {
            $v .= "-{$this->preRelease}";
        }
        if ($this->build !== null) {
            $v .= "+{$this->build}";
        }
        return $v;
    }

    /**
     * Evaluate a single constraint part.
     */
    private function satisfiesSingle(string $constraint): bool
    {
        // Caret: ^1.2.3 → >=1.2.3 <2.0.0
        if (str_starts_with($constraint, '^')) {
            return $this->satisfiesCaret(substr($constraint, 1));
        }

        // Tilde: ~1.2.3 → >=1.2.3 <1.3.0
        if (str_starts_with($constraint, '~')) {
            return $this->satisfiesTilde(substr($constraint, 1));
        }

        // >=, >, <=, <, =
        if (str_starts_with($constraint, '>=')) {
            $target = self::parse(substr($constraint, 2));
            return $this->greaterThanOrEqual($target);
        }
        if (str_starts_with($constraint, '>')) {
            $target = self::parse(substr($constraint, 1));
            return $this->greaterThan($target);
        }
        if (str_starts_with($constraint, '<=')) {
            $target = self::parse(substr($constraint, 2));
            return $this->lessThanOrEqual($target);
        }
        if (str_starts_with($constraint, '<')) {
            $target = self::parse(substr($constraint, 1));
            return $this->lessThan($target);
        }
        if (str_starts_with($constraint, '=')) {
            $target = self::parse(substr($constraint, 1));
            return $this->equals($target);
        }

        // Exact match
        $target = self::parse($constraint);
        return $this->equals($target);
    }

    /**
     * Caret constraint: ^1.2.3 means >=1.2.3 <2.0.0
     * ^0.2.3 means >=0.2.3 <0.3.0 (special case for 0.x)
     * ^0.0.3 means >=0.0.3 <0.0.4 (special case for 0.0.x)
     */
    private function satisfiesCaret(string $versionStr): bool
    {
        $min = self::parse($versionStr);

        if (!$this->greaterThanOrEqual($min)) {
            return false;
        }

        if ($min->major > 0) {
            $ceiling = new self($min->major + 1, 0, 0);
        } elseif ($min->minor > 0) {
            $ceiling = new self(0, $min->minor + 1, 0);
        } else {
            $ceiling = new self(0, 0, $min->patch + 1);
        }

        return $this->lessThan($ceiling);
    }

    /**
     * Tilde constraint: ~1.2.3 means >=1.2.3 <1.3.0
     * ~1.2 means >=1.2.0 <1.3.0
     */
    private function satisfiesTilde(string $versionStr): bool
    {
        $min = self::parse($versionStr);

        if (!$this->greaterThanOrEqual($min)) {
            return false;
        }

        $ceiling = new self($min->major, $min->minor + 1, 0);
        return $this->lessThan($ceiling);
    }

    /**
     * Compare pre-release identifiers per semver spec.
     * Identifiers are compared numerically if both are numeric, lexically otherwise.
     */
    private function comparePreRelease(string $a, string $b): int
    {
        $aParts = explode('.', $a);
        $bParts = explode('.', $b);

        $count = max(count($aParts), count($bParts));

        for ($i = 0; $i < $count; $i++) {
            if (!isset($aParts[$i]) && isset($bParts[$i])) {
                return -1;
            }
            if (isset($aParts[$i]) && !isset($bParts[$i])) {
                return 1;
            }

            $aIsNumeric = ctype_digit($aParts[$i]);
            $bIsNumeric = ctype_digit($bParts[$i]);

            if ($aIsNumeric && $bIsNumeric) {
                $cmp = (int) $aParts[$i] <=> (int) $bParts[$i];
            } elseif ($aIsNumeric) {
                $cmp = -1; // numeric < string
            } elseif ($bIsNumeric) {
                $cmp = 1;
            } else {
                $cmp = strcmp($aParts[$i], $bParts[$i]);
            }

            if ($cmp !== 0) {
                return $cmp;
            }
        }

        return 0;
    }
}
