<?php

namespace App\Observers;

use App\Jobs\ProcessAuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    /**
     * No registrar si la auditoría está desactivada.
     */
    private function isEnabled(): bool
    {
        return config('audit.enabled', false);
    }

    /**
     * Filtrar atributos sensibles.
     */
    private function filterAttributes(array $attributes): array
    {
        $hidden = config('audit.hidden_attributes', []);
        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }
        return $attributes;
    }

    public function created(Model $model): void
    {
        if (!$this->isEnabled()) return;

        $user = auth()->user();

        ProcessAuditLog::dispatch([
            'user_id' => $user?->id,
            'is_revisor' => $user?->id_rol == 2,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => 'create',
            'old_values' => null,
            'new_values' => $this->filterAttributes($model->getAttributes()),
            'description' => class_basename($model) . " #{$model->id} procesado",
            'target_user_id' => $model->id_usuario ?? $model->id_postulante ?? null,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'id_proceso' => $user?->id_proceso,
        ]);
    }

    public function updated(Model $model): void
    {
        if (!$this->isEnabled()) return;

        $user = auth()->user();
        $changes = $model->getChanges();
        $original = [];

        foreach ($changes as $key => $value) {
            $original[$key] = $model->getOriginal($key);
        }

        // No registrar si solo cambian timestamps
        $relevantChanges = array_filter(array_keys($changes), fn($k) => !in_array($k, ['updated_at']));
        if (empty($relevantChanges)) return;

        ProcessAuditLog::dispatch([
            'user_id' => $user?->id,
            'is_revisor' => $user?->id_rol == 2,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => 'update',
            'old_values' => $this->filterAttributes($original),
            'new_values' => $this->filterAttributes($changes),
            'description' => class_basename($model) . " #{$model->id} procesado",
            'target_user_id' => $model->id_usuario ?? $model->id_postulante ?? null,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'id_proceso' => $user?->id_proceso,
        ]);
    }

    public function deleted(Model $model): void
    {
        if (!$this->isEnabled()) return;

        $user = auth()->user();

        ProcessAuditLog::dispatch([
            'user_id' => $user?->id,
            'is_revisor' => $user?->id_rol == 2,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => 'delete',
            'old_values' => $this->filterAttributes($model->getAttributes()),
            'new_values' => null,
            'description' => class_basename($model) . " #{$model->id} eliminado",
            'target_user_id' => $model->id_usuario ?? $model->id_postulante ?? null,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'id_proceso' => $user?->id_proceso,
        ]);
    }
}
