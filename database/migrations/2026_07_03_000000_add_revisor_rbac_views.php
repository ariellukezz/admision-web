<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $moduleId = DB::table('rbac_modules')->where('code', 'revisor')->value('id');

        if (!$moduleId) {
            return;
        }

        $actionIds = DB::table('rbac_actions')->pluck('id', 'code');

        $newViews = [
            'revisor-validacion'  => 'Validación de Certificados',
            'revisor-solicitudes' => 'Solicitudes de Revisión',
        ];

        foreach ($newViews as $code => $name) {
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

            $viewId = DB::table('rbac_views')
                ->where('module_id', $moduleId)
                ->where('code', $code)
                ->value('id');

            foreach (['access', 'read'] as $actionCode) {
                DB::table('rbac_permissions')->updateOrInsert(
                    ['view_id' => $viewId, 'action_id' => $actionIds[$actionCode]],
                    [
                        'module_id'  => $moduleId,
                        'status'     => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $newPermissionIds = DB::table('rbac_permissions')
            ->where('module_id', $moduleId)
            ->whereIn('view_id', function ($q) use ($moduleId, $newViews) {
                $q->select('id')->from('rbac_views')
                    ->where('module_id', $moduleId)
                    ->whereIn('code', array_keys($newViews));
            })
            ->pluck('id');

        foreach ([1, 2] as $roleId) {
            foreach ($newPermissionIds as $permissionId) {
                DB::table('rbac_role_permissions')->updateOrInsert(
                    ['role_id' => $roleId, 'permission_id' => $permissionId],
                    [
                        'status'     => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $moduleId = DB::table('rbac_modules')->where('code', 'revisor')->value('id');

        if (!$moduleId) {
            return;
        }

        $viewIds = DB::table('rbac_views')
            ->where('module_id', $moduleId)
            ->whereIn('code', ['revisor-validacion', 'revisor-solicitudes'])
            ->pluck('id');

        $permissionIds = DB::table('rbac_permissions')->whereIn('view_id', $viewIds)->pluck('id');

        DB::table('rbac_role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('rbac_permissions')->whereIn('view_id', $viewIds)->delete();
        DB::table('rbac_views')->whereIn('id', $viewIds)->delete();
    }
};
