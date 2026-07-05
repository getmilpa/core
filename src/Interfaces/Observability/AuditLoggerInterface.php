<?php

declare(strict_types=1);

namespace Milpa\app\Interfaces\Observability;

/**
 * AuditLoggerInterface - Framework-level contract for audit logging.
 *
 * Plugins that need audit trail capabilities can consume this interface
 * via tryGetService() for optional audit logging.
 */
interface AuditLoggerInterface
{
    /**
     * Log an audit event.
     *
     * @param string $entityId ID de la entidad afectada (opaque string/UUID identity).
     * @param string|null $actorUserId ID del actor que realizo la accion (opaque string/UUID identity).
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     * @param array<string, mixed>|null $metadata
     */
    public function log(
        string $entityType,
        string $entityId,
        string $action,
        ?string $actorUserId = null,
        ?string $actorRole = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null
    ): mixed;
}
