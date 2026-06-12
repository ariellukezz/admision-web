<?php

namespace App\Http\Controllers\Api\v1;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Action;
use App\Models\PermissionRole;
use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes as OpenApi;
use Illuminate\Support\Facades\Redis;
USE DB;

#[OpenApi\PathItem]
class RolePermissionController extends Controller
{

    public function index()
    {
        try {
            $results = DB::table('systems as s')
                ->select([
                    's.id as system_id',
                    's.name as system_name',
                    'm.id as module_id',
                    'm.name as module_name',
                    'v.id as view_id',
                    'v.name as view_name'
                ])
                ->leftJoin('modules as m', function ($join) {
                    $join->on('m.system_id', '=', 's.id')
                        ->where('m.status', '=', 1);
                })
                ->leftJoin('views as v', function ($join) {
                    $join->on('v.module_id', '=', 'm.id')
                        ->where('v.status', '=', 1);
                })
                ->orderBy('s.name')
                ->orderBy('m.name')
                ->orderBy('v.name')
                ->get();

            $permissions = DB::table('role_permissions as rp')
                ->select('per.system_id', 'per.module_id', 'per.view_id', 'act.code')
                ->join('permissions as per', 'rp.permission_id', '=', 'per.id')
                ->join('actions as act', 'per.action_id', '=', 'act.id')
                ->where('rp.role_id', 1)
                ->get();

            $structuredData = $results->groupBy('system_id')->map(function ($systemGroup, $systemId) {
                $firstSystem = $systemGroup->first();

                return [
                    'key' => 'system-' . $firstSystem->system_id,
                    'name' => $firstSystem->system_name,
                    'type' => 'system',
                    'children' => $systemGroup->whereNotNull('module_id')
                        ->groupBy('module_id')
                        ->map(function ($moduleGroup, $moduleId) {
                            $firstModule = $moduleGroup->first();

                            return [
                                'key' => 'module-' . $firstModule->module_id,
                                'name' => $firstModule->module_name,
                                'type' => 'module',
                                'children' => $moduleGroup->whereNotNull('view_id')
                                    ->map(function ($view) {
                                        return [
                                            'key' => 'view-' . $view->view_id,
                                            'name' => $view->view_name,
                                            'type' => 'view',
                                            'permissions' => []
                                        ];
                                    })->values()->toArray()
                            ];
                        })->values()->toArray()
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'data' => $structuredData
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving permissions structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $id_action = Action::where('code', $request->action)->first()->id;
        $permiso = Permission::create([
            'system_id' => $request->system_id,
            'module_id' => $request->module_id,
            'view_id' => $request->view_id,
            'action_id' => $id_action
        ]);

        return response()->json([
            'success' => true,
            'data' => $permiso
        ], 200);    
    }


    public function destroyPermisoRol(Request $request)
    {
        $id_action = Action::where('code', $request->action)->first()->id;
        $permiso = Permission::where('system_id', $request->system_id)
            ->where('module_id', $request->module_id)
            ->where('view_id', $request->view_id)
            ->where('action_id', $id_action)
            ->first();

        if ($permiso) {
            $permiso->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }
    }

    public function getPermisos(){
        $permissions = Permission::select('system_id', 'module_id', 'view_id', 'actions.code')
            ->join('actions', 'permissions.action_id', '=', 'actions.id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);

    }

    public function PermisosRol($id_rol){
        $permissions = DB::table('role_permissions as rp')
        ->select('per.system_id', 'per.module_id', 'per.view_id', 'act.code')
        ->join('permissions as per', 'rp.permission_id', '=', 'per.id')
        ->join('actions as act', 'per.action_id', '=', 'act.id')
        ->where('rp.role_id', $id_rol)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ], 200);

    }

    public function savePermisoRol(Request $request)
    {
        $id_action = Action::where('code', $request->action)->first()->id;
        $permiso_id = Permission::where('system_id',$request->system_id)
        ->where('module_id',$request->module_id)
        ->where('view_id',$request->view_id)
        ->where('action_id',$id_action)    
        ->first()->id;

        $rol_permiso = PermissionRole::create([
            'permission_id' => $permiso_id,
            'role_id' => $request->role_id,
            'status' => 1
        ]);

        $this->actualizarRolRedis($request->role_id); 
        return response()->json([ 'success' => true, 'data' => $rol_permiso], 200);    

    }


    public function removePermisoRol(Request $request)
    {
        $action = Action::where('code', $request->action)->first();
        $permiso = Permission::where('system_id', $request->system_id)
            ->where('module_id', $request->module_id)
            ->where('view_id', $request->view_id)
            ->where('action_id', $action->id)
            ->first();

        $permisoRol = PermissionRole::where('role_id', $request->role_id)
            ->where('permission_id', $permiso->id)
            ->first();

        if (!$permisoRol) {
            return response()->json(['success' => false, 'message' => 'Relación no encontrada'], 404);
        }

        $permisoRol->delete();
        $this->actualizarRolRedis($request->role_id);

        return response()->json(['success' => true], 200);
    }



    public function treeDataRoutes()
    {
        try {
            $results = DB::table('systems as s')
                ->select([
                    's.id as system_id',
                    's.name as system_name',
                    'm.id as module_id',
                    'm.name as module_name',
                    'v.id as view_id',
                    'v.name as view_name'
                ])
                ->leftJoin('modules as m', function ($join) {
                    $join->on('m.system_id', '=', 's.id')
                        ->where('m.status', '=', 1);
                })
                ->leftJoin('views as v', function ($join) {
                    $join->on('v.module_id', '=', 'm.id')
                        ->where('v.status', '=', 1);
                })
                ->orderBy('s.name')
                ->orderBy('m.name')
                ->orderBy('v.name')
                ->get();

            $routes = DB::table('routes as rut')
                ->select([
                    'rut.id as id',
                    'mo.system_id',
                    'vie.module_id',
                    'rut.view_id',
                    'rut.method', 
                    'rut.path', 
                    'rut.auth', 
                    'rut.service',
                    'rut.service_path', 
                    'rut.audit', 
                    'rut.status',
                    'rut.permission_id'
                ])
                ->join('views as vie', 'vie.id', '=', 'rut.view_id')
                ->join('modules as mo', 'mo.id', '=', 'vie.module_id')
                ->leftJoin('permissions as per', 'rut.permission_id', '=', 'per.id')
                ->get();

            $routesByView = $routes->groupBy('view_id')->map(function ($viewRoutes) {
                return $viewRoutes->map(function ($route) {
                    return [
                        'id' => $route->id,
                        'method' => $route->method,
                        'path' => $route->path,
                        'service_path' => $route->service_path,
                        'audit' => $route->audit,
                        'service' => $route->service,
                        'auth' => $route->auth,
                        'status' => $route->status,
                        'permission_id' => $route->permission_id
                    ];
                });
            });

            $structuredData = $results->groupBy('system_id')->map(function ($systemGroup, $systemId) use ($routesByView) {
                $firstSystem = $systemGroup->first();

                return [
                    'key' => 'system-' . $firstSystem->system_id,
                    'name' => $firstSystem->system_name,
                    'type' => 'system',
                    'children' => $systemGroup->whereNotNull('module_id')
                        ->groupBy('module_id')
                        ->map(function ($moduleGroup, $moduleId) use ($routesByView) {
                            $firstModule = $moduleGroup->first();

                            return [
                                'key' => 'module-' . $firstModule->module_id,
                                'name' => $firstModule->module_name,
                                'type' => 'module',
                                'children' => $moduleGroup->whereNotNull('view_id')
                                    ->map(function ($view) use ($routesByView) {
                                        $viewRoutes = $routesByView->get($view->view_id, []);
                                        
                                        return [
                                            'key' => 'view-' . $view->view_id,
                                            'name' => $view->view_name,
                                            'type' => 'view',
                                            'routes' => collect($viewRoutes)->values()->toArray()
                                        ];
                                    })->values()->toArray()
                            ];
                        })->values()->toArray()
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'data' => $structuredData
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving routes structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    private function actualizarRolRedis($id_rol){

        $permissions = PermissionRole::where('role_id', $id_rol)
        ->where('status', 1)
        ->pluck('permission_id')
        ->toArray();

        Redis::del("role:$id_rol:permissions");
        Redis::sadd("role:$id_rol:permissions", ...$permissions);

    } 
    

}
