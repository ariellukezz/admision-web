<?php

namespace App\Http\Controllers\Api\v1;
use App\Models\Route;
use App\Models\SystemApiToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function index()
    {
        return response()->json(Route::with('permission')->get());
    }

    public function show($id)
    {
        $route = Route::with('permission')->find($id);
        if (!$route) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }
        return response()->json($route);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'permission_id' => 'nullable|integer',
                'view_id'       => 'nullable|integer',
                'method'        => 'required|string|max:10',
                'path'          => 'required|string|max:255',
                'auth'          => 'nullable|integer|',
                'service'       => 'nullable|integer|',
                'service_path'  => 'required|string|max:255',
                'audit'         => 'nullable|boolean',
                'status'        => 'nullable|boolean',
            ]);

            $route = Route::create([
                'permission_id' => $validated['permission_id'] ?? null,
                'view_id'       => $validated['view_id'] ?? null,
                'method'        => strtoupper($validated['method']),
                'path'          => $validated['path'],
                'auth'          => $validated['auth'],
                'service'       => $validated['service'],
                'service_path'  => $validated['service_path'],
                'audit'         => $validated['audit'] ?? 0,
                'status'        => $validated['status'] ?? 1,
            ]);

            $this->crearRutaRedis($route, $request);

            return response()->json([
                'message' => 'Ruta registrada correctamente',
                'data'    => $route,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $route = Route::find($id);
        if (!$route) {
            return response()->json(['message' => 'Ruta no encontrada'], 404);
        }

        $oldMethod = $route->method;
        $oldPath = $route->path;

        $validated = $request->validate([
            'permission_id' => 'nullable|integer|exists:permissions,id',
            'view_id' => 'nullable|integer|exists:views,id',
            'module_id' => 'nullable|integer|exists:modules,id',
            'system_id' => 'nullable|integer|exists:systems,id',
            'method' => 'required|string|max:10',
            'path' => 'required|string|max:255',
            'auth' => 'nullable|integer',
            'service' => 'nullable|integer',    
            'service_path' => 'required|string|max:255',
            'audit' => 'boolean',
            'status' => 'boolean'
        ]);

        if (!$request->has('permission_id')) {
            $validated['permission_id'] = null;
        }

        $route->update($validated);
        $this->actualizarRutaRedis($route, $oldMethod, $oldPath);
        
        return response()->json([
            'message' => 'Ruta actualizada correctamente',
            'data' => $route
        ]);
    }

    public function destroy($id)
    {
        try {
            $route = Route::find($id);
            if (!$route) {
                return response()->json(['message' => 'Ruta no encontrada'], 404);
            }

            $this->eliminarRutaRedis($route);
            $route->delete();

            return response()->json([
                'message' => 'Ruta eliminada correctamente',
                'deleted_id' => $id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error eliminando la ruta: ' . $e->getMessage()
            ], 500);
        }
    }


    private function crearRutaRedis($route)
    {
        $key = "route:{$route->method}:{$route->path}";
        Redis::del($key);

        Redis::hMSet($key, [
            'service_path'  => $route->service_path,
            'permission_id' => $route->permission_id ?? '',
            'audit'         => $route->audit ?? 0,
            'service'       => $route->service ?? 0,
            'auth'          => $route->auth ?? 0,
            'status'        => $route->status ?? 1,
        ]);
    }

    private function actualizarRutaRedis($route, $oldMethod = null, $oldPath = null)
    {
        if ($oldMethod && $oldPath) {
            $oldKey = "route:{$oldMethod}:{$oldPath}";
            Redis::del($oldKey);
        }

        $newKey = "route:{$route->method}:{$route->path}";
        
        Redis::hMSet($newKey, [
            'service_path'  => $route->service_path,
            'permission_id' => $route->permission_id ?? '',
            'audit'         => $route->audit ?? 0,
            'service'       => $route->service ?? 0,
            'auth'          => $route->auth ?? 0,
            'status'        => $route->status ?? 1,
        ]);
    }

    private function eliminarRutaRedis($route)
    {
        $key = "route:{$route->method}:{$route->path}";
        Redis::del($key);            
    }

    public function actualizarRutasRedis()
    {
        $routes = Route::all();
        
        foreach ($routes as $route) {
            $key = "route:{$route->method}:{$route->path}";
            
            Redis::hMSet($key, [
                'service_path'  => $route->service_path,
                'permission_id' => $route->permission_id ?? '',
                'audit'         => $route->audit ?? 0,
                'service'       => $route->service ?? 0,
                'auth'          => $route->auth ?? 0,
                'status'        => $route->status ?? 1,
            ]);
        }
        
        return response()->json([
            'message' => "{$routes->count()} rutas sincronizadas con Redis"
        ]);
    }

    public function verRutasRedis()
    {
        $redisKeys = Redis::keys('route:*');
        $rutas = [];
        
        foreach ($redisKeys as $key) {
            $rutas[] = [
                'key' => $key,
                'data' => Redis::hGetAll($key)
            ];
        }
        
        return response()->json([
            'total_rutas_redis' => count($rutas),
            'rutas' => $rutas
        ]);
    }

    public function rutasServicio($systemId)
    {
        $res = Route::select([
            'routes.id as id_ruta',
            'systems.name',
            'routes.method', 
            'routes.path',
            'systems.id as system_id',
            'system_api_tokens.status',
            'system_api_tokens.api_token'
        ])
        ->join('views', 'views.id', '=', 'routes.view_id')
        ->join('modules', 'modules.id', '=', 'views.module_id')
        ->join('systems', 'systems.id', '=', 'modules.system_id')
        ->leftJoin('system_api_tokens', function($join) use($systemId) {
            $join->on('system_api_tokens.route_id', '=', 'routes.id')
                ->where('system_api_tokens.system_id', '=', $systemId); 
        })
        ->where('routes.service', 1)
        ->get();

        return response()->json([
            'estado' => true,
            'datos' => $res
        ]);
    }


public function generarApiToken(Request $request)
{
    $validated = $request->validate([
        'route_id' => 'required|integer|exists:routes,id',
        'system_id' => 'required|integer|exists:systems,id',
    ]);

    $existingToken = SystemApiToken::where([
        'route_id' => $validated['route_id'],
        'system_id' => $validated['system_id'],
    ])->first();

    if ($existingToken) {
        Redis::del("api_token:{$existingToken->api_token}");
    }

    $res = SystemApiToken::updateOrCreate(
        [
            'route_id' => $validated['route_id'],
            'system_id' => $validated['system_id'],
        ],
        [
            'api_token' => bin2hex(random_bytes(30)),
            'status' => 1,
        ]
    );

    $key = "api_token:{$res->api_token}";
    
    $data = [
        'route_id' => $res->route_id,
        'system_id' => $res->system_id,
        'status' => $res->status,
        'created_at' => now()->toISOString(),
        'updated_at' => now()->toISOString()
    ];
    
    Redis::set($key, json_encode($data));

    return response()->json([
        'estado' => true,
        'datos' => $res
    ]);
}

    public function sincronizarRutasConTokens()
    {
        try {
            $existingKeys = Redis::keys('api_token:*');
            if (!empty($existingKeys)) {
                Redis::del($existingKeys);
            }
            
            $rutasConTokens = DB::table('routes')
            ->select([
                'routes.id as route_id',
                'routes.method',
                'routes.path',
                'routes.status',
                'systems.id as system_id',
                'systems.name as system_name',
                'system_api_tokens.api_token',
                'system_api_tokens.status',
                'system_api_tokens.created_at',
                'system_api_tokens.updated_at'
            ])
            ->join('system_api_tokens', 'system_api_tokens.route_id', '=', 'routes.id')
            ->join('systems', 'systems.id', '=', 'system_api_tokens.system_id')
            ->where('routes.service', 1)
            ->where('system_api_tokens.status', 1)
            ->get();

            $contador = 0;

            foreach ($rutasConTokens as $ruta) {
                
                if( empty($ruta->api_token)) {
                    continue;
                }

                $key = "api_token:{$ruta->api_token}";
                
                $data = [
                    'route_id' => $ruta->route_id,
                    'system_id' => $ruta->system_id,
                    'system_name' => $ruta->system_name,
                    'method' => $ruta->method,
                    'path' => $ruta->path,
                    'status' => $ruta->status,
                    'created_at' => $ruta->created_at,
                    'updated_at' => $ruta->updated_at,
                    'synced_at' => now()->toISOString()
                ];
                Redis::set($key, json_encode($data));
                $contador++;
            }

            return response()->json([
                'estado' => true,
                'mensaje' => "Sincronización completada",
                'datos' => [
                    'total_sincronizado' => $contador,
                    'fecha_sincronizacion' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Error en la sincronización: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verDatosSincronizados()
    {
        try {
            $patron = "api_token:*";
            $claves = Redis::keys($patron);
            
            $datosSincronizados = [];
            $contador = 0;

            foreach ($claves as $clave) {
                $datos = Redis::get($clave);
                
                if ($datos) {
                    $datosDecodificados = json_decode($datos, true);
                    $datosDecodificados['redis_key'] = $clave;
                    $datosSincronizados[] = $datosDecodificados;
                    $contador++;
                }
            }

            return response()->json([
                'estado' => true,
                'mensaje' => "Datos encontrados en Redis",
                'datos' => [
                    'total_registros' => $contador,
                    'rutas_sincronizadas' => $datosSincronizados
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Error al obtener datos de Redis: ' . $e->getMessage()
            ], 500);
        }
    }

    
}
