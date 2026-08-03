<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\AulaGestion;
use App\Modules\Calificacion\Requests\StoreAulaGestionRequest;
use App\Modules\Calificacion\Requests\UpdateAulaGestionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AulaGestionController extends BaseCalificacionController
{
    public function index(Request $request): JsonResponse
    {
        $query = AulaGestion::with('ambiente:id,nombre,codigo');

        if ($request->has('id_ambiente')) {
            $query->where('id_ambiente', $request->integer('id_ambiente'));
        }

        $aulas = $query->orderBy('id_ambiente')->orderBy('piso')->orderBy('codigo')->get();

        return $this->successResponse($aulas);
    }

    public function store(StoreAulaGestionRequest $request): JsonResponse
    {
        $aula = AulaGestion::create($request->validated());
        $aula->load('ambiente:id,nombre,codigo');

        return $this->successResponse($aula, 'Aula creada correctamente', 201);
    }

    public function update(UpdateAulaGestionRequest $request, int $id): JsonResponse
    {
        $aula = AulaGestion::find($id);

        if (!$aula) {
            return $this->errorResponse('Aula no encontrada', 404);
        }

        $aula->update($request->validated());
        $aula->load('ambiente:id,nombre,codigo');

        return $this->successResponse($aula, 'Aula actualizada correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $aula = AulaGestion::find($id);

        if (!$aula) {
            return $this->errorResponse('Aula no encontrada', 404);
        }

        $aula->delete();

        return $this->successResponse(null, 'Aula eliminada correctamente');
    }
}
