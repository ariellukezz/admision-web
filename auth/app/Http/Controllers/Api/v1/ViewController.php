<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Throwable;
use Dedoc\Scramble\Attributes as OpenApi;

#[OpenApi\PathItem]
class ViewController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $buscar   = $request->query('buscar');
            $moduleId = $request->query('module_id');
            $pageSize = (int) $request->query('pageSize', 10);
            $page     = (int) $request->query('page', 1);

            $query = View::with('module');

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'LIKE', "%{$buscar}%")
                      ->orWhere('name', 'LIKE', "%{$buscar}%")
                      ->orWhere('description', 'LIKE', "%{$buscar}%");
                });
            }

            if ($moduleId) {
                $query->where('module_id', $moduleId);
            }

            $views = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($views, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Views'], method: 'POST', summary: 'Crear una vista')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['module_id', 'code', 'name'],
            properties: [
                'module_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del módulo relacionado'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único de la vista'),
                'name'        => new OpenApi\Schema(type: 'string', description: 'Nombre de la vista'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado activo/inactivo'),
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Vista creada')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'module_id'   => 'required|exists:modules,id',
                'code'        => 'required|string|max:150|unique:views,code',
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

            $view = View::create($validator->validated());

            return response()->json($view->load('module'), 201);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Views'], method: 'GET', summary: 'Obtener una vista por ID')]
    #[OpenApi\Response(status: 200, description: 'Vista encontrada')]
    #[OpenApi\Response(status: 404, description: 'Vista no encontrada')]
    public function show($id): JsonResponse
    {
        try {
            $view = View::with('module')->findOrFail($id);
            return response()->json($view, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vista no encontrada'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Views'], method: 'PUT', summary: 'Actualizar una vista')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['module_id', 'code', 'name'],
            properties: [
                'module_id'   => new OpenApi\Schema(type: 'integer', description: 'ID del módulo relacionado'),
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único de la vista'),
                'name'        => new OpenApi\Schema(type: 'string', description: 'Nombre de la vista'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción', nullable: true),
                'status'      => new OpenApi\Schema(type: 'boolean', description: 'Estado activo/inactivo'),
            ]
        )
    )]
    #[OpenApi\Response(status: 200, description: 'Vista actualizada')]
    #[OpenApi\Response(status: 404, description: 'Vista no encontrada')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $view = View::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'module_id'   => 'required|exists:modules,id',
                'code'        => 'required|string|max:150|unique:views,code,' . $view->id,
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

            $view->update($validator->validated());

            return response()->json($view->load('module'), 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vista no encontrada'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Views'], method: 'DELETE', summary: 'Eliminar una vista')]
    #[OpenApi\Response(status: 204, description: 'Vista eliminada')]
    #[OpenApi\Response(status: 404, description: 'Vista no encontrada')]
    public function destroy($id): JsonResponse
    {
        try {
            $view = View::findOrFail($id);
            $view->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Vista no encontrada'], 404);
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
