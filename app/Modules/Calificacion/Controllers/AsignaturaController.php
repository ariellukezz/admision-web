<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Asignatura;
use App\Modules\Calificacion\Requests\StoreAsignaturaRequest;
use App\Modules\Calificacion\Requests\UpdateAsignaturaRequest;
use App\Modules\Calificacion\Resources\AsignaturaResource;
use Illuminate\Http\JsonResponse;

class AsignaturaController extends BaseCalificacionController
{
    public function index(): JsonResponse
    {
        $asignaturas = Asignatura::orderBy('orden', 'ASC')->get();
        return $this->successResponse(AsignaturaResource::collection($asignaturas));
    }

    public function store(StoreAsignaturaRequest $request): JsonResponse
    {
        $asignatura = Asignatura::create([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? (Asignatura::max('orden') + 1),
            'estado' => $request->estado ?? true,
        ]);

        return $this->successResponse(new AsignaturaResource($asignatura), 'Asignatura creada correctamente', 201);
    }

    public function update(UpdateAsignaturaRequest $request, int $id): JsonResponse
    {
        $asignatura = Asignatura::find($id);

        if (!$asignatura) {
            return $this->errorResponse('Asignatura no encontrada', 404);
        }

        $asignatura->update([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? $asignatura->orden,
            'estado' => $request->estado ?? $asignatura->estado,
        ]);

        return $this->successResponse(new AsignaturaResource($asignatura), 'Asignatura actualizada correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $asignatura = Asignatura::find($id);

        if (!$asignatura) {
            return $this->errorResponse('Asignatura no encontrada', 404);
        }

        $asignatura->delete();

        return $this->successResponse(null, 'Asignatura eliminada correctamente');
    }
}
