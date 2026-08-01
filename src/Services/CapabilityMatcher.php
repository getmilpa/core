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

namespace Milpa\Services;

use Milpa\ValueObjects\SemanticVersion;

/**
 * The ONE criterion for "does this `provides` satisfy this `requires`".
 *
 * It exists because the family had four, and they disagreed. Measured in
 * `docs/library/settlement-q-p17.md`:
 *
 * | comparador                  | identidad          | `oneOf` | `contractVersion` | `\` inicial |
 * |-----------------------------|--------------------|---------|-------------------|-------------|
 * | `GraphResolver` (motor)     | `id`               | sí      | **sí**            | no          |
 * | `CapabilityGraphChecker`    | `id` + `interface` | sí      | no                | no          |
 * | `CapabilityGraphValidator`  | **sólo** interface | **no**  | no                | sí          |
 * | `PluginInspection`          | `id` + `interface` | **no**  | no                | no          |
 *
 * The inspector was strictly weaker than the engine — not a second opinion, a
 * worse one — and it was the surface offered as a diagnostic tool. Two
 * components that decide whether an app boots cannot have two laws.
 *
 * ## Identity
 *
 * `php:` is an explicit, strippable annotation, NOT something inferred from
 * how a string looks. Deducing the scheme from syntax (a `\` means a class)
 * would turn spelling into meaning, and a capability id like
 * `crm.oauth.google.v1` has no backslash and is not a class. So:
 *
 * - `php:Acme\Thing` and `Acme\Thing` are the SAME identity — that is the
 *   migration alias between the canonical form and the legacy bare FQCN it
 *   replaces, declared here on purpose rather than guessed per call site;
 * - a leading `\` never distinguishes (`\Acme\Thing` ≡ `Acme\Thing`);
 * - `env:DATABASE_URL` lives in its own namespace and never collides with a
 *   PHP identity;
 * - anything else is an OPAQUE id, kept verbatim. No claim is made about what
 *   it names.
 *
 * A `provides` record offers TWO identities — its `id` and its `interface` —
 * because {@see \Milpa\ValueObjects\Capability\CapabilityProvision} carries
 * both and the family declares requirements against either.
 *
 * ## Contract compatibility, and the unknown version
 *
 * Identity and compatibility are separate questions, and this class keeps them
 * separate: {@see identitiesOffered()} answers the first, {@see satisfies()}
 * answers both.
 *
 * An absent `contractVersion` means UNKNOWN — never `0.0.0`. The legacy wrapper
 * {@see \Milpa\ValueObjects\Capability\CapabilityProvision::fromInterface()}
 * pins `0.0.0` so the record can be constructed, but `0.0.0` is a real version
 * that a `^1.0` constraint rejects *for being too old*, which is a different
 * statement from *nobody said*. Here an unknown version satisfies `*` and
 * nothing else — same verdict the engine already produced, an honest reason.
 */
final class CapabilityMatcher
{
    /**
     * Whether a `provides` entry NAMES the thing a `requires` entry asks for,
     * saying nothing about versions.
     *
     * This is the pre-boot question — "does *some* plugin provide this at all"
     * — and keeping it separate from {@see satisfies()} is deliberate: the
     * layering between an identity check before boot and a range check in the
     * resolver was never the defect. The defect was that each layer used a
     * DIFFERENT identity law, so one accepted what the other rejected. Both
     * layers now ask this one.
     *
     * @param string|array<string, mixed> $provision
     * @param string|array<string, mixed> $requirement
     */
    public function identityMatches(string|array $provision, string|array $requirement): bool
    {
        $offered = $this->identitiesOffered($provision);
        if ($offered === []) {
            return false;
        }

        return array_intersect($offered, $this->identitiesAccepted($requirement)) !== [];
    }

    /**
     * Whether a `provides` entry satisfies a `requires` entry: identities must
     * match AND the declared contract version must satisfy the constraint.
     *
     * This is the resolver's question. A consumer that only needs to know
     * whether the capability exists at all wants {@see identityMatches()}.
     *
     * @param string|array<string, mixed> $provision
     * @param string|array<string, mixed> $requirement
     */
    public function satisfies(string|array $provision, string|array $requirement): bool
    {
        if (!$this->identityMatches($provision, $requirement)) {
            return false;
        }

        return $this->contractIsCompatible(
            $this->contractVersionOf($provision),
            $this->constraintOf($requirement),
        );
    }

    /**
     * The canonical identities a `provides` entry offers: the bare string
     * itself, or a record's `id` and `interface`.
     *
     * @param string|array<string, mixed> $entry
     *
     * @return list<string>
     */
    public function identitiesOffered(string|array $entry): array
    {
        if (is_string($entry)) {
            $identity = $this->canonicalize($this->stripConstraint($entry)[0]);

            return $identity === '' ? [] : [$identity];
        }

        $identities = [];
        foreach (['id', 'interface'] as $key) {
            $value = $entry[$key] ?? null;
            if (!is_string($value)) {
                continue;
            }
            $identity = $this->canonicalize($this->stripConstraint($value)[0]);
            if ($identity !== '' && !in_array($identity, $identities, true)) {
                $identities[] = $identity;
            }
        }

        return $identities;
    }

    /**
     * Every canonical identity that satisfies one `requires` entry: its own,
     * plus any `oneOf` alternatives.
     *
     * `oneOf` is the reason the inspector and the validator used to report
     * violations the engine never had: a requirement the engine resolves
     * through an alternative must not fail anywhere else.
     *
     * @param string|array<string, mixed> $entry
     *
     * @return list<string>
     */
    public function identitiesAccepted(string|array $entry): array
    {
        $accepted = $this->identitiesOffered($entry);

        if (is_array($entry)) {
            /** @var mixed $oneOf */
            $oneOf = $entry['oneOf'] ?? [];
            foreach (is_array($oneOf) ? $oneOf : [] as $candidate) {
                if (!is_string($candidate)) {
                    continue;
                }
                $identity = $this->canonicalize($this->stripConstraint($candidate)[0]);
                if ($identity !== '' && !in_array($identity, $accepted, true)) {
                    $accepted[] = $identity;
                }
            }
        }

        return $accepted;
    }

    /**
     * The semver constraint a `requires` entry imposes: its `constraint` field,
     * or the range packed into the identity itself (`some.capability@^1.0` —
     * the third syntax, which host profiles use), or `*`.
     *
     * @param string|array<string, mixed> $entry
     */
    public function constraintOf(string|array $entry): string
    {
        if (is_array($entry)) {
            $declared = $entry['constraint'] ?? null;
            if (is_string($declared) && trim($declared) !== '') {
                return trim($declared);
            }

            $packedFrom = $entry['id'] ?? $entry['interface'] ?? null;

            return is_string($packedFrom) ? ($this->stripConstraint($packedFrom)[1] ?? '*') : '*';
        }

        return $this->stripConstraint($entry)[1] ?? '*';
    }

    /**
     * The contract version a `provides` entry declares, or NULL when nobody
     * said. Null is not `0.0.0`: see the class docblock.
     *
     * @param string|array<string, mixed> $entry
     */
    public function contractVersionOf(string|array $entry): ?string
    {
        if (!is_array($entry)) {
            return null;
        }

        $declared = $entry['contractVersion'] ?? null;

        return is_string($declared) && trim($declared) !== '' ? trim($declared) : null;
    }

    /**
     * Whether a declared contract version satisfies a constraint. An unknown
     * version (null) satisfies only the unconstrained `*`.
     */
    public function contractIsCompatible(?string $contractVersion, string $constraint): bool
    {
        if ($constraint === '' || $constraint === '*') {
            return true;
        }

        if ($contractVersion === null) {
            return false;
        }

        try {
            return SemanticVersion::parse($contractVersion)->satisfies($constraint);
        } catch (\InvalidArgumentException) {
            // An unparseable version is not a compatible one. Teaching that the
            // declaration is malformed belongs to the ingestion layer, which
            // validates records; here it can only mean "not proven compatible".
            return false;
        }
    }

    /**
     * Splits `identity@constraint` into its two halves. A string without `@`
     * yields the identity and a null constraint.
     *
     * @return array{0: string, 1: string|null}
     */
    private function stripConstraint(string $value): array
    {
        $value = trim($value);
        $at = strpos($value, '@');

        if ($at === false) {
            return [$value, null];
        }

        $constraint = trim(substr($value, $at + 1));

        return [trim(substr($value, 0, $at)), $constraint === '' ? null : $constraint];
    }

    /**
     * The canonical form of one identity: `php:` stripped as the annotation it
     * is, a leading `\` removed, everything else kept verbatim.
     */
    private function canonicalize(string $identity): string
    {
        $identity = ltrim(trim($identity), '\\');

        if (str_starts_with($identity, 'php:')) {
            return ltrim(substr($identity, 4), '\\');
        }

        return $identity;
    }
}
