<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Jobs\ProcessAuditLog;

class AuditMiddleware
{
    /**
     * Datos capturados en handle() para usar en terminate().
     */
    private array $auditContext = [];

    public function handle(Request $request, Closure $next)
    {
        if (!config('audit.enabled')) {
            return $next($request);
        }

        // Snapshot del modelo ANTES del cambio (solo para updates/deletes)
        if ($this->shouldAudit($request)) {
            $this->captureOldValues($request);
        }

        return $next($request);
    }

    /**
     * Se ejecuta DESPUÉS de enviar la respuesta al cliente.
     * Despacha el log a la cola — cero impacto en tiempo de respuesta.
     */
    public function terminate(Request $request, $response): void
    {
        if (!config('audit.enabled')) {
            return;
        }

        try {
            if (!$this->shouldAudit($request)) {
                return;
            }

            $user = $request->user();
            if (!$user) {
                return;
            }

            $action = $this->resolveAction($request);
            $modelType = $this->resolveModelType($request);
            $modelId = $this->resolveModelId($request);

            ProcessAuditLog::dispatch([
                'user_id' => $user->id,
                'is_revisor' => $user->id_rol == 2,
                'model_type' => $modelType ? 'App\\Models\\' . $modelType : 'Route',
                'model_id' => $modelId,
                'action' => $action,
                'old_values' => $this->auditContext['old_values'] ?? null,
                'new_values' => $this->captureNewValues($request, $action),
                'description' => $this->buildDescription($action, $modelType, $request),
                'target_user_id' => null,
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'id_proceso' => $user->id_proceso,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('AuditMiddleware error: ' . $e->getMessage());
        }
    }

    // ─── Lógica de decisión ─────────────────────────────────────

    private function shouldAudit(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        // Verificar exclusiones por nombre exacto
        if ($routeName) {
            foreach (config('audit.excluded_routes', []) as $excluded) {
                if (fnmatch($excluded, $routeName)) {
                    return false;
                }
            }

            // Verificar exclusión por prefijo
            foreach (config('audit.excluded_prefixes', []) as $prefix) {
                if (str_starts_with($routeName, $prefix)) {
                    return false;
                }
            }
        }

        $method = $request->method();

        // POST/PUT/PATCH/DELETE siempre se auditan (si no están excluidos)
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        // GET solo se audita si tiene un parámetro ID en la ruta
        // (ej: /descargar-documento/5, /eliminar-documento/3, /preview-documento/8)
        if ($method === 'GET' && $this->routeHasIdParameter($request)) {
            return true;
        }

        return false;
    }

    private function routeHasIdParameter(Request $request): bool
    {
        $params = $request->route()?->parameters();
        if (!$params) {
            return false;
        }

        foreach ($params as $value) {
            if (is_numeric($value)) {
                return true;
            }
            if (is_object($value) && isset($value->id)) {
                return true;
            }
        }

        return false;
    }

    // ─── Captura de valores ─────────────────────────────────────

    private function captureOldValues(Request $request): void
    {
        $modelType = $this->resolveModelType($request);
        $modelId = $this->resolveModelId($request);

        if (!$modelType || !$modelId) {
            return;
        }

        $modelClass = 'App\\Models\\' . $modelType;

        if (!class_exists($modelClass)) {
            return;
        }

        try {
            $model = $modelClass::find($modelId);
            if ($model) {
                $this->auditContext['old_values'] = $this->filterAttributes($model->getAttributes());
            }
        } catch (\Throwable $e) {
            // Si no se puede cargar, continuamos sin old_values
        }
    }

    private function captureNewValues(Request $request, string $action): ?array
    {
        // Para deletes, no hay new_values
        if ($action === 'delete') {
            return null;
        }

        // Para creates/updates, capturar del input del request
        $input = $request->except(array_merge(
            ['_token', '_method'],
            config('audit.hidden_attributes', [])
        ));

        return empty($input) ? null : $this->filterAttributes($input);
    }

    private function filterAttributes(array $attributes): array
    {
        $hidden = config('audit.hidden_attributes', []);

        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    // ─── Resolución de acción y modelo ──────────────────────────

    private function resolveAction(Request $request): string
    {
        $path = $request->path();
        $routeName = $request->route()?->getName() ?? '';

        if (str_contains($routeName, 'eliminar') || str_contains($path, 'eliminar') ||
            str_contains($routeName, 'delete') || str_contains($path, 'delete')) {
            return 'delete';
        }
        if (str_contains($routeName, 'verif') || str_contains($path, 'verif')) {
            return 'verify';
        }
        if (str_contains($routeName, 'download') || str_contains($path, 'download') ||
            str_contains($routeName, 'descargar') || str_contains($path, 'descargar')) {
            return 'download';
        }
        if (str_contains($routeName, 'preview') || str_contains($path, 'preview')) {
            return 'view';
        }
        if (str_contains($routeName, 'update') || str_contains($path, 'update') ||
            str_contains($routeName, 'actualizar') || str_contains($path, 'actualizar') ||
            str_contains($routeName, 'guardar') || str_contains($path, 'guardar') ||
            str_contains($routeName, 'cambiar') || str_contains($path, 'cambiar')) {
            return 'update';
        }
        if ($request->method() === 'DELETE') {
            return 'delete';
        }
        if ($request->method() === 'GET' && $this->routeHasIdParameter($request)) {
            return 'view';
        }

        return 'create';
    }

    private function resolveModelType(Request $request): ?string
    {
        $path = $request->path();

        $map = [
            'documento' => 'Documento',
            'vacante' => 'Vacante',
            'postulante' => 'Postulante',
            'inscripcion' => 'Inscripcion',
            'preinscripcion' => 'Preinscripcion',
            'usuario' => 'User',
            'requisito' => 'RequisitoDocumento',
            'tipo-documento' => 'TipoDocumento',
            'programa' => 'Programa',
            'modalidad' => 'Modalidad',
            'pago' => 'Pago',
            'observado' => 'Observado',
            'apoderado' => 'Apoderado',
            'colegio' => 'Colegio',
            'paso' => 'Paso',
            'perfil' => 'User',
        ];

        foreach ($map as $segment => $model) {
            if (str_contains($path, $segment)) {
                return $model;
            }
        }

        return null;
    }

    private function resolveModelId(Request $request): int
    {
        $params = $request->route()?->parameters();
        if ($params) {
            foreach ($params as $value) {
                if (is_numeric($value)) {
                    return (int) $value;
                }
                if (is_object($value) && isset($value->id)) {
                    return (int) $value->id;
                }
            }
        }

        return (int) ($request->input('id') ?? $request->input('id_documento') ?? $request->input('id_vacante') ?? $request->input('id_postulante') ?? 0);
    }

    private function buildDescription(string $action, ?string $modelType, Request $request): string
    {
        $label = $modelType ?? 'Recurso';
        $routeName = $request->route()?->getName() ?? $request->path();

        return match ($action) {
            'create' => "{$label} procesado ({$routeName})",
            'update' => "{$label} procesado ({$routeName})",
            'delete' => "{$label} eliminado ({$routeName})",
            'view' => "{$label} visualizado ({$routeName})",
            'download' => "{$label} descargado ({$routeName})",
            'verify' => "{$label} verificado ({$routeName})",
            'login' => "Inicio de sesión ({$routeName})",
            default => "Acceso a {$label} ({$routeName})",
        };
    }
}
