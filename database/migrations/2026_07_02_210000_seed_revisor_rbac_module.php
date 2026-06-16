<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Módulo Revisor ──────────────────────────────────
        // Idempotente: usar updateOrInsert en vez de insertGetId
        DB::table('rbac_modules')->updateOrInsert(
            ['code' => 'revisor'],
            [
                'name'        => 'Revisor',
                'description' => 'Módulo del revisor de admisión',
                'status'      => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
        $moduleId = DB::table('rbac_modules')->where('code', 'revisor')->value('id');

        // ── Vistas del módulo ───────────────────────────────
        // La vista 'revisor' (código "revisor") con acción 'access'
        // genera el permiso "revisor.access" que usa el middleware
        $views = [
            'revisor'               => 'Acceso al Módulo',
            'revisor-dashboard'     => 'Dashboard',
            'revisor-notificaciones' => 'Notificaciones',
            'revisor-documentos'    => 'Revisión de Documentos',
            'revisor-postulantes'   => 'Postulantes',
            'revisor-pagos'         => 'Pagos y Comprobantes',
            'revisor-inscripcion'   => 'Inscripción',
            'revisor-biometrico'    => 'Fotos y Biométrico',
            'revisor-ingresantes'   => 'Ingresantes',
            'revisor-actividad'     => 'Mi Actividad',
        ];

        $viewIds = [];
        foreach ($views as $code => $name) {
            DB::table('rbac_views')->updateOrInsert(
                ['module_id' => $moduleId, 'code' => $code],
                [
                    'name'        => $name,
                    'description' => null,
                    'status'      => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
            $viewIds[$code] = DB::table('rbac_views')
                ->where('module_id', $moduleId)
                ->where('code', $code)
                ->value('id');
        }

        // ── Acciones disponibles ─────────────────────────────
        $actionIds = DB::table('rbac_actions')->pluck('id', 'code');

        // ── Permisos por vista ────────────────────────────────
        // access + read para todas las vistas
        $crudViews = [
            'revisor-documentos',
            'revisor-postulantes',
            'revisor-pagos',
            'revisor-inscripcion',
            'revisor-biometrico',
            'revisor-ingresantes',
        ];

        // La vista 'revisor' solo tiene access + read (permiso revisor.access)
        // ya está incluida en el loop de abajo

        foreach ($viewIds as $viewCode => $viewId) {
            // access + read siempre
            $this->createPermission($moduleId, $viewId, $actionIds['access']);
            $this->createPermission($moduleId, $viewId, $actionIds['read']);

            // CRUD para vistas de gestión
            if (in_array($viewCode, $crudViews)) {
                $this->createPermission($moduleId, $viewId, $actionIds['create']);
                $this->createPermission($moduleId, $viewId, $actionIds['update']);
                $this->createPermission($moduleId, $viewId, $actionIds['delete']);
            }

            // export para vistas con reportes
            if (in_array($viewCode, ['revisor-dashboard', 'revisor-actividad', 'revisor-postulantes'])) {
                $this->createPermission($moduleId, $viewId, $actionIds['export']);
            }

            // print para vistas que generan PDFs
            if (in_array($viewCode, ['revisor-inscripcion', 'revisor-biometrico', 'revisor-documentos'])) {
                $this->createPermission($moduleId, $viewId, $actionIds['print']);
            }
        }

        // ── Asignar todos los permisos del módulo al rol 2 (Revisor) ──
        $permisosRevisor = DB::table('rbac_permissions')
            ->where('module_id', $moduleId)
            ->pluck('id');

        foreach ($permisosRevisor as $permisoId) {
            DB::table('rbac_role_permissions')->updateOrInsert(
                ['role_id' => 2, 'permission_id' => $permisoId],
                [
                    'status'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // ── Asignar todos los permisos al rol 1 (Admin) ──
        foreach ($permisosRevisor as $permisoId) {
            DB::table('rbac_role_permissions')->updateOrInsert(
                ['role_id' => 1, 'permission_id' => $permisoId],
                [
                    'status'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function createPermission(int $moduleId, int $viewId, int $actionId): void
    {
        DB::table('rbac_permissions')->updateOrInsert(
            ['view_id' => $viewId, 'action_id' => $actionId],
            [
                'module_id'  => $moduleId,
                'status'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        $moduleId = DB::table('rbac_modules')->where('code', 'revisor')->value('id');

        if ($moduleId) {
            $viewIds = DB::table('rbac_views')->where('module_id', $moduleId)->pluck('id');

            DB::table('rbac_permissions')->whereIn('view_id', $viewIds)->delete();
            DB::table('rbac_role_permissions')
                ->whereIn('permission_id', DB::table('rbac_permissions')->where('module_id', $moduleId)->pluck('id'))
                ->delete();
            DB::table('rbac_views')->where('module_id', $moduleId)->delete();
            DB::table('rbac_modules')->where('code', 'revisor')->delete();
        }
    }
};
