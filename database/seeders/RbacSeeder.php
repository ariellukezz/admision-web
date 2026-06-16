<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // ── Acciones (sin timestamps) ─────────────────────────
        $actions = [
            ['code' => 'access',  'description' => 'Acceder al módulo'],
            ['code' => 'create',  'description' => 'Crear nuevo registro'],
            ['code' => 'read',    'description' => 'Ver / listar registros'],
            ['code' => 'update',  'description' => 'Editar registros'],
            ['code' => 'delete',  'description' => 'Eliminar registros'],
            ['code' => 'export',  'description' => 'Exportar datos'],
            ['code' => 'print',   'description' => 'Imprimir / generar PDF'],
        ];

        foreach ($actions as $action) {
            DB::table('rbac_actions')->updateOrInsert(
                ['code' => $action['code']],
                ['description' => $action['description']]
            );
        }

        $actionIds = DB::table('rbac_actions')->pluck('id', 'code');

        // ── Módulos y vistas ──────────────────────────────────
        $modules = [
            'dashboard' => [
                'name' => 'Dashboard',
                'views' => ['dashboard' => 'Dashboard'],
            ],
            'configuracion' => [
                'name' => 'Configuración',
                'views' => [
                    'vacantes'       => 'Vacantes',
                    'tarifas'        => 'Tarifas',
                    'observados'     => 'Observados',
                    'requisitos'     => 'Requisitos',
                    'tipos-documento' => 'Tipos de Documento',
                ],
            ],
            'gestion-admision' => [
                'name' => 'Gestión de Admisión',
                'views' => [
                    'preinscripciones'   => 'Preinscripciones',
                    'inscripciones'      => 'Inscripciones',
                    'control-biometrico' => 'Control Biométrico',
                    'fotos-huellas'      => 'Fotos y Huellas',
                    'resultados'         => 'Puntajes',
                    'observados-v2'      => 'Observados v2',
                    'estudiantes'        => 'Estudiantes',
                    'est-cepreuna'      => 'Est. Cepreuna',
                ],
            ],
            'mantenimiento' => [
                'name' => 'Mantenimiento',
                'views' => [
                    'filial'      => 'Sede',
                    'procesos'    => 'Procesos',
                    'programas'   => 'Programas',
                    'modalidades' => 'Modalidades',
                    'colegios'    => 'Colegios',
                    'ubigeo'      => 'Ubigeos',
                    'pagos'       => 'Pagos',
                    'reglamentos' => 'Reglamentos',
                    'anios'       => 'Años',
                ],
            ],
            'participantes' => [
                'name' => 'Gestión de Participantes',
                'views' => [
                    'docentes'       => 'Docentes',
                    'administrativos' => 'Administrativos',
                    'sorteo'         => 'Sorteo',
                    'participantes'  => 'Participantes',
                ],
            ],
            'usuarios-roles' => [
                'name' => 'Roles y Usuarios',
                'views' => [
                    'roles'   => 'Roles',
                    'usuarios' => 'Usuarios',
                ],
            ],
            'gestion-tecnica' => [
                'name' => 'Gestión Técnica',
                'views' => [
                    'apoderados'           => 'Apoderados',
                    'postulantes'          => 'Postulantes',
                    'consulta-reniec'      => 'Consulta Reniec',
                    'documentos'           => 'Documentos',
                    'carreras-previas'     => 'Estudios Anteriores',
                    'pagos-banco'          => 'Pagos BN',
                    'certificados-firma'   => 'Certificados Firma',
                    'respaldo-bd'          => 'Respaldo BD',
                    'trazabilidad'         => 'Trazabilidad',
                    'configuracion-citacion' => 'Configuración de Citación',
                ],
            ],
            'reportes' => [
                'name' => 'Reportes',
                'views' => [
                    'resumen-general'      => 'Resumen General',
                    'resumen-inscripciones' => 'Resumen Inscripciones',
                    'rep-programa-diario'  => 'Rep Programa Diario',
                    'rep-usuarios-diario'  => 'Rep Usuarios Diario',
                    'ratio'               => 'Ratio',
                    'res-biometrico'      => 'Res. Biométrico',
                    'rep-errores'         => 'Rep Errores',
                ],
            ],
            'ayuda' => [
                'name' => 'Centro de Ayuda',
                'views' => [
                    'soporte-tecnico'  => 'Soporte Técnico',
                    'manuales-guias'   => 'Manuales y Guías',
                ],
            ],
            'permisos' => [
                'name' => 'Gestión de Permisos (RBAC)',
                'views' => [
                    'modulos'  => 'Módulos y Vistas',
                    'permisos' => 'Permisos',
                ],
            ],
        ];

        foreach ($modules as $moduleCode => $moduleData) {
            $moduleId = DB::table('rbac_modules')->updateOrInsert(
                ['code' => $moduleCode],
                [
                    'name'        => $moduleData['name'],
                    'description' => null,
                    'status'      => true,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );

            // updateOrInsert returns bool, need to fetch the ID
            $moduleId = DB::table('rbac_modules')->where('code', $moduleCode)->value('id');

            foreach ($moduleData['views'] as $viewCode => $viewName) {
                DB::table('rbac_views')->updateOrInsert(
                    ['module_id' => $moduleId, 'code' => $viewCode],
                    [
                        'name'        => $viewName,
                        'description' => null,
                        'status'      => true,
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            }
        }

        // ── Permisos ──────────────────────────────────────────
        // Para cada vista, crear permisos de access + CRUD (donde aplique)
        $views = DB::table('rbac_views')->get();

        foreach ($views as $view) {
            // access + read para todas las vistas
            $this->createPermission($view->module_id, $view->id, $actionIds['access']);
            $this->createPermission($view->module_id, $view->id, $actionIds['read']);

            // CRUD para vistas que tienen operaciones de gestión
            $crudViews = [
                'vacantes', 'tarifas', 'requisitos', 'tipos-documento',
                'preinscripciones', 'inscripciones',
                'filial', 'procesos', 'programas', 'modalidades',
                'colegios', 'reglamentos', 'anios',
                'docentes', 'administrativos', 'sorteo',
                'roles', 'usuarios',
                'apoderados', 'postulantes', 'documentos',
                'carreras-previas', 'pagos-banco', 'certificados-firma',
                'configuracion-citacion',
                'observados', 'observados-v2',
                'modulos', 'permisos',
            ];

            if (in_array($view->code, $crudViews)) {
                $this->createPermission($view->module_id, $view->id, $actionIds['create']);
                $this->createPermission($view->module_id, $view->id, $actionIds['update']);
                $this->createPermission($view->module_id, $view->id, $actionIds['delete']);
            }

            // export para vistas de reportes y listados
            $exportViews = [
                'preinscripciones', 'inscripciones', 'observados', 'observados-v2',
                'postulantes', 'documentos', 'pagos-banco',
                'resumen-general', 'resumen-inscripciones', 'rep-programa-diario',
                'rep-usuarios-diario', 'ratio', 'res-biometrico', 'rep-errores',
                'docentes', 'administrativos',
            ];

            if (in_array($view->code, $exportViews)) {
                $this->createPermission($view->module_id, $view->id, $actionIds['export']);
            }

            // print para vistas que generan documentos
            $printViews = [
                'inscripciones', 'postulantes', 'documentos', 'certificados-firma',
                'tarifas', 'reglamentos',
            ];

            if (in_array($view->code, $printViews)) {
                $this->createPermission($view->module_id, $view->id, $actionIds['print']);
            }
        }

        $this->command->info('RBACSeeder: Acciones, módulos, vistas y permisos creados.');
        $this->command->info('  - Acciones: ' . DB::table('rbac_actions')->count());
        $this->command->info('  - Módulos:  ' . DB::table('rbac_modules')->count());
        $this->command->info('  - Vistas:   ' . DB::table('rbac_views')->count());
        $this->command->info('  - Permisos:  ' . DB::table('rbac_permissions')->count());
        $this->command->info('No se asignaron permisos a ningún rol.');
    }

    private function createPermission(int $moduleId, int $viewId, int $actionId): void
    {
        DB::table('rbac_permissions')->updateOrInsert(
            ['view_id' => $viewId, 'action_id' => $actionId],
            [
                'module_id'  => $moduleId,
                'status'     => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
