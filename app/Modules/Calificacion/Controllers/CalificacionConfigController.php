<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Calificacion;
use App\Modules\Calificacion\Requests\StoreCalificacionRequest;
use App\Modules\Calificacion\Requests\UpdateCalificacionRequest;
use App\Modules\Calificacion\Resources\CalificacionResource;
use Illuminate\Http\JsonResponse;

class CalificacionConfigController extends BaseCalificacionController
{
    public function index(): JsonResponse
    {
        $calificaciones = Calificacion::with(['proceso', 'multiplicador', 'ponderacion'])
            ->orderBy('id', 'DESC')
            ->get();

        return $this->successResponse(CalificacionResource::collection($calificaciones));
    }

    public function store(StoreCalificacionRequest $request): JsonResponse
    {
        $calificacion = Calificacion::create([
            'id_proceso' => $request->id_proceso,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_multiplicador' => $request->id_multiplicador,
            'id_ponderacion' => $request->id_ponderacion,
            'estado' => $request->estado ?? true,
        ]);

        $calificacion->load(['proceso', 'multiplicador', 'ponderacion']);

        return $this->successResponse(new CalificacionResource($calificacion), 'Calificación creada correctamente', 201);
    }

    public function show(int $id): JsonResponse
    {
        $calificacion = Calificacion::with(['proceso', 'multiplicador', 'ponderacion'])->find($id);

        if (!$calificacion) {
            return $this->errorResponse('Calificación no encontrada', 404);
        }

        return $this->successResponse(new CalificacionResource($calificacion));
    }

    public function update(UpdateCalificacionRequest $request, int $id): JsonResponse
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return $this->errorResponse('Calificación no encontrada', 404);
        }

        $calificacion->update([
            'id_proceso' => $request->id_proceso,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_multiplicador' => $request->id_multiplicador,
            'id_ponderacion' => $request->id_ponderacion,
            'estado' => $request->has('estado') ? $request->estado : $calificacion->estado,
        ]);

        $calificacion->load(['proceso', 'multiplicador', 'ponderacion']);

        return $this->successResponse(new CalificacionResource($calificacion), 'Calificación actualizada correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return $this->errorResponse('Calificación no encontrada', 404);
        }

        $calificacion->delete();

        return $this->successResponse(null, 'Calificación eliminada correctamente');
    }
}
