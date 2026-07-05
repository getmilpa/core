<?php

declare(strict_types=1);

namespace Milpa\app\Enums;

/**
 * ListenerPriority - Prioridades estándar para event listeners.
 *
 * Define niveles de prioridad consistentes para suscriptores de eventos.
 * Mayor valor = ejecuta primero.
 *
 * @package Milpa\app\Enums
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
