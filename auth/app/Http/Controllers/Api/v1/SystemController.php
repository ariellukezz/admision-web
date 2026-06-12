<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\System;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Throwable;

class SystemController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query('buscar');
            $pageSize = (int) $request->query('pageSize', 10);
            $page = (int) $request->query('page', 1);

            $query = System::query();

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'LIKE', "%{$buscar}%")
                      ->orWhere('description', 'LIKE', "%{$buscar}%");
                });
            }

            $systems = $query->paginate($pageSize, ['*'], 'page', $page);

            return response()->json($systems, 200);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|unique:systems,name',
                'description' => 'nullable|string',
            ]);

            $system = System::create($validated);
            $rawToken = Str::random(40);
            $token = $system->id . '|' . $rawToken;
            $system->token = $token;
            $system->save();

            return response()->json([
                'message'   => 'Sistema creado exitosamente',
                'system'    => $system,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }


    public function show($id): JsonResponse
    {
        try {
            $system = System::findOrFail($id);
            return response()->json($system, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sistema no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }


    public function update(Request $request, $id): JsonResponse
    {
        try {
            $system = System::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|unique:systems,name,' . $system->id,
                'description' => 'nullable|string',
            ]);

            $system->update($validated);

            return response()->json($system, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sistema no encontrado'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }


    public function destroy($id): JsonResponse
    {
        try {
            $system = System::findOrFail($id);
            $system->delete();

            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Sistema no encontrado'], 404);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Respuesta JSON genérica para errores inesperados
     */
    protected function errorResponse(Throwable $e, int $status = 500): JsonResponse
    {
        $response = [
            'message' => 'Error interno del servidor',
        ];

        if (config('app.debug')) {
            $response['error'] = $e->getMessage();
            $response['trace'] = $e->getTrace();
        }

        return response()->json($response, $status);
    }
}
