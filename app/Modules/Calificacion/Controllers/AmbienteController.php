<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Ambiente;
use App\Modules\Calificacion\Requests\StoreAmbienteRequest;
use App\Modules\Calificacion\Requests\UpdateAmbienteRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmbienteController extends BaseCalificacionController
{
    public function index(Request $request): JsonResponse
    {
        $query = Ambiente::with(['programas:id,nombre'])
            ->withCount('aulas')
            ->orderBy('nombre');

        if ($request->boolean('with_aulas')) {
            $query->with(['aulas' => function ($q) {
                $q->orderBy('piso')->orderBy('codigo');
            }]);
        }

        $ambientes = $query->get();

        return $this->successResponse($ambientes);
    }

    public function store(StoreAmbienteRequest $request): JsonResponse
    {
        $data = $request->validated();
        $programas = $data['programas'] ?? [];
        unset($data['programas']);

        $ambiente = Ambiente::create($data);

        if (!empty($programas)) {
            $ambiente->programas()->sync($programas);
        }

        $ambiente->load(['programas:id,nombre']);

        return $this->successResponse($ambiente, 'Ambiente creado correctamente', 201);
    }

    public function show(int $id): JsonResponse
    {
        $ambiente = Ambiente::with(['programas:id,nombre', 'aulas'])->find($id);

        if (!$ambiente) {
            return $this->errorResponse('Ambiente no encontrado', 404);
        }

        return $this->successResponse($ambiente);
    }

    public function update(UpdateAmbienteRequest $request, int $id): JsonResponse
    {
        $ambiente = Ambiente::find($id);

        if (!$ambiente) {
            return $this->errorResponse('Ambiente no encontrado', 404);
        }

        $data = $request->validated();
        $programas = $data['programas'] ?? null;
        unset($data['programas']);

        $ambiente->update($data);

        if ($programas !== null) {
            $ambiente->programas()->sync($programas);
        }

        $ambiente->load(['programas:id,nombre']);

        return $this->successResponse($ambiente, 'Ambiente actualizado correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $ambiente = Ambiente::find($id);

        if (!$ambiente) {
            return $this->errorResponse('Ambiente no encontrado', 404);
        }

        $ambiente->delete();

        return $this->successResponse(null, 'Ambiente eliminado correctamente');
    }

    public function aulas(int $id): JsonResponse
    {
        $ambiente = Ambiente::find($id);

        if (!$ambiente) {
            return $this->errorResponse('Ambiente no encontrado', 404);
        }

        return $this->successResponse($ambiente->aulas()->orderBy('piso')->orderBy('codigo')->get());
    }
}
