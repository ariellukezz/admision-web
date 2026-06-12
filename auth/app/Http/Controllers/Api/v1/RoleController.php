<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Vyuldashev\LaravelOpenApi\Attributes as OpenApi;

#[OpenApi\PathItem]
class RoleController extends Controller
{
    #[OpenApi\Operation(tags: ['Roles'], method: 'GET', summary: 'Listar roles')]
    #[OpenApi\Parameters([
        new OpenApi\Parameter(name: 'buscar', in: 'query', required: false, schema: new OpenApi\Schema(type: 'string')),
        new OpenApi\Parameter(name: 'page', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'pageSize', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
    ])]
    #[OpenApi\Response(status: 200, description: 'Lista paginada de roles')]
    public function index(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query('buscar');
            $pageSize = (int) $request->query('pageSize', 10);
            $page = (int) $request->query('page', 1);

            $query = Role::query();

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'LIKE', "%{$buscar}%")
                      ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
                });
            }

            $roles = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($roles, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Roles'], method: 'POST', summary: 'Crear un nuevo rol')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['nombre'],
            properties: [
                'nombre' => new OpenApi\Schema(type: 'string', description: 'Nombre del rol'),
                'descripcion' => new OpenApi\Schema(type: 'string', description: 'Descripción del rol', nullable: true)
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Rol creado')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|unique:roles,nombre',
                'descripcion' => 'nullable|string',
                'estado' => 'boolean',
            ]);

            $role = Role::create($validated);

            return response()->json($role, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Roles'], method: 'GET', summary: 'Obtener un rol por ID')]
    #[OpenApi\Response(status: 200, description: 'Rol encontrado')]
    #[OpenApi\Response(status: 404, description: 'Rol no encontrado')]
    public function show($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            return response()->json($role, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Roles'], method: 'PUT', summary: 'Actualizar un rol')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['nombre'],
            properties: [
                'nombre' => new OpenApi\Schema(type: 'string', description: 'Nombre del rol'),
                'descripcion' => new OpenApi\Schema(type: 'string', description: 'Descripción del rol', nullable: true)
            ]
        )
    )]
    #[OpenApi\Response(status: 200, description: 'Rol actualizado')]
    #[OpenApi\Response(status: 404, description: 'Rol no encontrado')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|unique:roles,nombre,' . $role->id,
                'descripcion' => 'nullable|string',
            ]);

            $role->update($validated);

            return response()->json($role, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Roles'], method: 'DELETE', summary: 'Eliminar un rol')]
    #[OpenApi\Response(status: 204, description: 'Rol eliminado')]
    #[OpenApi\Response(status: 404, description: 'Rol no encontrado')]
    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
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
