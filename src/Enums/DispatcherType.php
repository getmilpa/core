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

namespace Milpa\Enums;

/**
 * DispatcherType - Identifica el bus de eventos destino de un `#[Subscribe]`.
 *
 * Milpa opera con dos buses de eventos independientes (two-bus decision, F1):
 * - SYMFONY: el `EventDispatcherInterface` de Symfony, tipado por clase de evento.
 *   Es el bus canónico del framework para eventos del ciclo de vida HTTP/Kernel
 *   (Route matching, Request/Response, etc.).
 * - MILPA: el `MilpaEventDispatcherInterface`, pub/sub por nombre (string) con
 *   soporte de wildcards, pensado para eventos dinámicos y de agentes IA donde
 *   el nombre del evento no se conoce en tiempo de compilación.
 *
 * Reemplaza el `?string` original de `Subscribe::$dispatcher` para eliminar el
 * silent typo misrouting (p.ej. `dispatcher: 'symfoni'` no fallaba, simplemente
 * caía al auto-detect).
 *
 * @package Milpa\Enums
 */
enum DispatcherType: string
{
    case SYMFONY = 'symfony';
    case MILPA = 'milpa';
}
