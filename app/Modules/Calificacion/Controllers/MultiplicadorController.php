<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Multiplicador;
use App\Modules\Calificacion\Requests\StoreMultiplicadorRequest;
use App\Modules\Calificacion\Requests\UpdateMultiplicadorRequest;
use App\Modules\Calificacion\Resources\MultiplicadorResource;
use Illuminate\Http\JsonResponse;

class MultiplicadorController extends BaseCalificacionController
{
    public function index(): JsonResponse
    {
        $multiplicadores = Multiplicador::orderBy('id', 'ASC')->get();
        return $this->successResponse(MultiplicadorResource::collection($multiplicadores));
    }

    public function store(StoreMultiplicadorRequest $request): JsonResponse
    {
        $multiplicador = Multiplicador::create([
            'nombre' => $request->nombre,
            'correcta' => $request->correcta,
            'incorrecta' => $request->incorrecta,
            'blanco' => $request->blanco,
            'estado' => $request->estado ?? true,
        ]);

        return $this->successResponse(new MultiplicadorResource($multiplicador), 'Multiplicador creado correctamente', 201);
    }

    public function update(UpdateMultiplicadorRequest $request, int $id): JsonResponse
    {
        $multiplicador = Multiplicador::find($id);

        if (!$multiplicador) {
            return $this->errorResponse('Multiplicador no encontrado', 404);
        }

        $multiplicador->update([
            'nombre' => $request->nombre,
            'correcta' => $request->correcta,
            'incorrecta' => $request->incorrecta,
            'blanco' => $request->blanco,
            'estado' => $request->has('estado') ? $request->estado : $multiplicador->estado,
        ]);

        return $this->successResponse(new MultiplicadorResource($multiplicador), 'Multiplicador actualizado correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $multiplicador = Multiplicador::find($id);

        if (!$multiplicador) {
            return $this->errorResponse('Multiplicador no encontrado', 404);
        }

        $multiplicador->delete();

        return $this->successResponse(null, 'Multiplicador eliminado correctamente');
    }
}
