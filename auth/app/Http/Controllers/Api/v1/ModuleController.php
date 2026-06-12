<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Validator;
use Dedoc\Scramble\Attributes as OpenApi;

#[OpenApi\PathItem]
class ModuleController extends Controller
{
    #[OpenApi\Operation(tags: ['Modules'], method: 'GET', summary: 'Listar módulos')]
    #[OpenApi\Parameters([
        new OpenApi\Parameter(name: 'buscar', in: 'query', required: false, schema: new OpenApi\Schema(type: 'string')),
        new OpenApi\Parameter(name: 'system_id', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'page', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'pageSize', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
    ])]
    #[OpenApi\Response(status: 200, description: 'Lista paginada de módulos')]
    public function index(Request $request): JsonResponse
    {
        try {
            $buscar   = $request->query('term');
            $systemId = $request->query('system_id');
            $pageSize = (int) $request->query('pageSize', 10);
            $page     = (int) $request->query('page', 1);

            $query = Module::with('system');

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'LIKE', "%{$buscar}%")
                      ->orWhere('name', 'LIKE', "%{$buscar}%")
                      ->orWhere('description', 'LIKE', "%{$buscar}%");
                });
            }

            if ($systemId) {
                $query->where('system_id', $systemId);
            }

            $modules = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($modules, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Modules'], method: 'POST', summary: 'Crear un módulo')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['system_id', 'code', 'name'],
            properties: [
                'system_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del sistema'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único del módulo'),
                'name'        => new OpenApi\Schema(type: 'string', description: 'Nombre del módulo'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado del módulo (activo/inactivo)'),
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Módulo creado')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'system_id'   => 'required|exists:systems,id',
                'code'        => 'required|string|max:150|unique:modules,code',
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'status'      => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $module = Module::create($validator->validated());

            return response()->json($module->load('system'), 201);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Modules'], method: 'GET', summary: 'Obtener un módulo por ID')]
    #[OpenApi\Response(status: 200, description: 'Módulo encontrado')]
    #[OpenApi\Response(status: 404, description: 'Módulo no encontrado')]
    public function show($id): JsonResponse
    {
        try {
            $module = Module::with('system')->findOrFail($id);
            return response()->json($module, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Módulo no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Modules'], method: 'PUT', summary: 'Actualizar un módulo')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['system_id', 'code', 'name'],
            properties: [
                'system_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del sistema'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único del módulo'),
                'name'        => new OpenApi\Schema(type: 'string', description: 'Nombre del módulo'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado del módulo'),
            ]
        )
    )]
    #[OpenApi\Response(status: 200, description: 'Módulo actualizado')]
    #[OpenApi\Response(status: 404, description: 'Módulo no encontrado')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $module = Module::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'system_id'   => 'required|exists:systems,id',
                'code'        => 'required|string|max:150|unique:modules,code,' . $module->id,
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'status'      => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $module->update($validator->validated());

            return response()->json($module->load('system'), 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Módulo no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Modules'], method: 'DELETE', summary: 'Eliminar un módulo')]
    #[OpenApi\Response(status: 204, description: 'Módulo eliminado')]
    #[OpenApi\Response(status: 404, description: 'Módulo no encontrado')]
    public function destroy($id): JsonResponse
    {
        try {
            $module = Module::findOrFail($id);
            $module->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Módulo no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    protected function errorResponse(Throwable $e, int $status = 500): JsonResponse
    {
        $response = ['message' => 'Error interno del servidor'];

        if (config('app.debug')) {
            $response['error'] = $e->getMessage();
            $response['trace'] = $e->getTrace();
        }

        return response()->json($response, $status);
    }
}
