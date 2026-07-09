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

namespace Milpa\Events;

/**
 * Dispatched at the very end of {@see \Milpa\Runtime\Kernel::boot()}'s plugin boot sequence
 * ('kernel.booted') — regardless of whether any plugin was vetoed along the way.
 *
 * Readonly, POST, no slot — pure notification, the boot-is-complete signal for
 * audit/observability plugins.
 */
final class KernelBootedEvent
{
    /**
     * @param array<int, string> $bootedPluginNames Names of plugins whose boot() actually ran this
     *                                              boot. Excludes any vetoed via 'plugin.booting'.
     */
    public function __construct(
        public readonly array $bootedPluginNames,
    ) {
    }
}
