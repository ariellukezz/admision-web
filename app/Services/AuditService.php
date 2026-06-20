<?php

namespace App\Services;

use App\Jobs\ProcessAuditLog;
use App\Models\AuditTrail;
use App\Models\Documento;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Despacha un log de auditoría a la cola para procesamiento en segundo plano.
     * No escribe en BD — el Job se encarga asíncronamente.
     */
    public static function log(
        string $action,
        string $modelType,
        int $modelId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $targetUserId = null,
        ?int $procesoId = null
    ): void {
        $user = auth()->user();

        ProcessAuditLog::dispatch([
            'user_id' => $user?->id,
            'is_revisor' => $user?->id_rol == 2,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? static::describeAction($action, class_basename($modelType), $modelId),
            'target_user_id' => $targetUserId,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'id_proceso' => $procesoId ?? $user?->id_proceso,
        ]);
    }

    /**
     * Registra una acción sobre un documento.
     * Resuelve automáticamente target_user_id desde el postulante dueño.
     */
    public static function logDocumentAction(
        string $action,
        int $documentoId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $procesoId = null
    ): void {
        $documento = Documento::find($documentoId);
        $targetUserId = $documento?->id_usuario;

        static::log(
            action: $action,
            modelType: Documento::class,
            modelId: $documentoId,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description ?? static::describeDocumentAction($action, $documentoId),
            targetUserId: $targetUserId,
            procesoId: $procesoId
        );
    }

    /**
     * Registra una acción sobre un modelo Eloquent cualquiera.
     */
    public static function logModel(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?int $targetUserId = null
    ): void {
        static::log(
            action: $action,
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $description ?? static::describeAction($action, get_class($model), $model->id),
            targetUserId: $targetUserId
        );
    }

    /**
     * Despacha un log desde datos crudos (usado por AuditMiddleware).
     */
    public static function logFromMiddleware(array $payload): void
    {
        ProcessAuditLog::dispatch($payload);
    }

    /**
     * Retorna el nombre visible del actor de un registro de auditoría.
     * Si tiene alias (revisor), muestra el alias. Si es admin, nombre real.
     */
    public static function resolveActorName(AuditTrail $audit): string
    {
        if ($audit->alias) {
            return $audit->alias;
        }

        if ($audit->user_id && $audit->user) {
            return $audit->user->getFullNameAttribute();
        }

        return 'Sistema';
    }

    private static function describeDocumentAction(string $action, int $docId): string
    {
        return match ($action) {
            'create' => "Documento #{$docId} subido",
            'update' => "Documento #{$docId} actualizado",
            'delete' => "Documento #{$docId} eliminado",
            'view' => "Documento #{$docId} visualizado",
            'download' => "Documento #{$docId} descargado",
            'verify' => "Documento #{$docId} verificado",
            default => "Acción '{$action}' sobre documento #{$docId}",
        };
    }

    private static function describeAction(string $action, string $modelShort, int $modelId): string
    {
        return match ($action) {
            'create' => "{$modelShort} #{$modelId} procesado",
            'update' => "{$modelShort} #{$modelId} procesado",
            'delete' => "{$modelShort} #{$modelId} eliminado",
            'view' => "{$modelShort} #{$modelId} visualizado",
            'download' => "{$modelShort} #{$modelId} descargado",
            'verify' => "{$modelShort} #{$modelId} verificado",
            'access' => "Acceso a {$modelShort}",
            'login' => "Inicio de sesión",
            default => "Acción '{$action}' sobre {$modelShort} #{$modelId}",
        };
    }
}
