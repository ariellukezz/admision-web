<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
use Validator;
use Dedoc\Scramble\Attributes as OpenApi;

#[OpenApi\PathItem]
class ActionController extends Controller
{
    #[OpenApi\Operation(tags: ['Actions'], method: 'GET', summary: 'Listar acciones')]
    #[OpenApi\Parameters([
        new OpenApi\Parameter(name: 'buscar', in: 'query', required: false, schema: new OpenApi\Schema(type: 'string')),
        new OpenApi\Parameter(name: 'page', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
        new OpenApi\Parameter(name: 'pageSize', in: 'query', required: false, schema: new OpenApi\Schema(type: 'integer')),
    ])]
    #[OpenApi\Response(status: 200, description: 'Lista paginada de acciones')]
    public function index(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query('buscar');
            $pageSize = (int) $request->query('pageSize', 10);
            $page = (int) $request->query('page', 1);

            $query = Action::query();

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('code', 'LIKE', "%{$buscar}%")
                      ->orWhere('description', 'LIKE', "%{$buscar}%");
                });
            }

            $actions = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($actions, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Actions'], method: 'POST', summary: 'Crear una nueva acción')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['code'],
            properties: [
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único de la acción'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción de la acción', nullable: true),
            ]
        )
    )]
    #[OpenApi\Response(status: 201, description: 'Acción creada')]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code'        => 'required|string|max:150|unique:actions,code',
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $action = Action::create($validator->validated());

            return response()->json($action, 201);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Actions'], method: 'GET', summary: 'Obtener una acción por ID')]
    #[OpenApi\Response(status: 200, description: 'Acción encontrada')]
    #[OpenApi\Response(status: 404, description: 'Acción no encontrada')]
    public function show($id): JsonResponse
    {
        try {
            $action = Action::findOrFail($id);
            return response()->json($action, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Actions'], method: 'PUT', summary: 'Actualizar una acción')]
    #[OpenApi\RequestBody(
        content: new OpenApi\JsonContent(
            required: ['code'],
            properties: [
                'code'        => new OpenApi\Schema(type: 'string', description: 'Código único de la acción'),
                'description' => new OpenApi\Schema(type: 'string', description: 'Descripción de la acción', nullable: true),
            ]
        )
    )]
    #[OpenApi\Response(status: 200, description: 'Acción actualizada')]
    #[OpenApi\Response(status: 404, description: 'Acción no encontrada')]
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $action = Action::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code'        => 'required|string|max:150|unique:actions,code,' . $action->id,
                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $action->update($validator->validated());

            return response()->json($action, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    #[OpenApi\Operation(tags: ['Actions'], method: 'DELETE', summary: 'Eliminar una acción')]
    #[OpenApi\Response(status: 204, description: 'Acción eliminada')]
    #[OpenApi\Response(status: 404, description: 'Acción no encontrada')]
    public function destroy($id): JsonResponse
    {
        try {
            $action = Action::findOrFail($id);
            $action->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Acción no encontrada'], 404);
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
