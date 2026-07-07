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

namespace Milpa\DTO;

/**
 * Result of dependency resolution for a plugin being installed.
 */
final readonly class DependencyResolution
{
    public function __construct(
        public bool $resolvable,
        /** @var array<string, string> Composer packages to install (package => constraint) */
        public array $composerPackages = [],
        /** @var array<string> Plugin names that must be installed first */
        public array $missingPlugins = [],
        /** @var array<string> Human-readable conflict descriptions */
        public array $conflicts = [],
        /** @var array<string> Contracts already satisfied by active plugins */
        public array $satisfiedContracts = []
    ) {
    }
}
