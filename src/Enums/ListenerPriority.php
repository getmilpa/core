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

namespace Milpa\Enums;

/**
 * ListenerPriority - Prioridades estándar para event listeners.
 *
 * Define niveles de prioridad consistentes para suscriptores de eventos.
 * Mayor valor = ejecuta primero.
 *
 * @package Milpa\Enums
 */
enum ListenerPriority: int
{
    case CRITICAL = 500;    // Seguridad, autenticación
    case HIGHEST  = 200;    // Validación, pre-procesamiento
    case HIGH     = 100;    // Reglas de negocio
    case NORMAL   = 0;      // Lógica estándar
    case LOW      = -100;   // Post-procesamiento
    case LOWEST   = -200;   // Logging, cleanup
}
