<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Validator;
use Dedoc\Scramble\Attributes as OpenApi;

#[OpenApi\PathItem]
class PermissionController extends Controller
{
    #[OpenApi\Operation(tags: ['Permissions'], method: 'GET', summary: 'Listar permisos')]
    #[OpenApi\Parameters([
        new OpenApi\Parameter(name: 'buscar', in: 'query', required: false, schema: new OpenApi\Schema(type: 'string')),
        new OpenApi\Parameter(name: 'page', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'pageSize', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
    ])]
    #[OpenApi\Response(status: 200, description: 'Lista paginada de permisos')]
    public function index(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query('buscar');
            $pageSize = (int) $request->query('pageSize', 10);
            $page = (int) $request->query('page', 1);

            $query = Permission::query();

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'LIKE', "%{$buscar}%")
                      ->orWhere('description', 'LIKE', "%{$buscar}%");
                });
            }

            $permissions = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($permissions, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Permissions'], method: 'POST', summary: 'Crear un nuevo permiso')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['system_id', 'code', 'status'],
            properties: [
                'system_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del sistema relacionado'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único del permiso'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción del permiso', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado activo/inactivo'),
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Permiso creado')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'system_id'   => 'required|integer|exists:systems,id',
                'code'        => 'required|string|max:150|unique:permissions,code',
                'description' => 'nullable|string|max:255',
                'status'      => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $permission = Permission::create($validator->validated());

            return response()->json($permission, 201);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Permissions'], method: 'GET', summary: 'Obtener un permiso por ID')]
    #[OpenApi\Response(status: 200, description: 'Permiso encontrado')]
    #[OpenApi\Response(status: 404, description: 'Permiso no encontrado')]
    public function show($id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);
            return response()->json($permission, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Permissions'], method: 'PUT', summary: 'Actualizar un permiso')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['system_id', 'code', 'status'],
            properties: [
                'system_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del sistema relacionado'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único del permiso'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción del permiso', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado activo/inactivo'),
            ]
        )
    )]
    #[OpenApi\Response(status: 200, description: 'Permiso actualizado')]
    #[OpenApi\Response(status: 404, description: 'Permiso no encontrado')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'system_id'   => 'required|integer|exists:systems,id',
                'code'        => 'required|string|max:150|unique:permissions,code,' . $permission->id,
                'description' => 'nullable|string|max:255',
                'status'      => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $permission->update($validator->validated());

            return response()->json($permission, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Permissions'], method: 'DELETE', summary: 'Eliminar un permiso')]
    #[OpenApi\Response(status: 204, description: 'Permiso eliminado')]
    #[OpenApi\Response(status: 404, description: 'Permiso no encontrado')]
    public function destroy($id): JsonResponse
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Permiso no encontrado'], 404);
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
