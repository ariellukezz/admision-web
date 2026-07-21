<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Pabellon;
use App\Modules\Calificacion\Requests\StorePabellonRequest;
use App\Modules\Calificacion\Requests\UpdatePabellonRequest;
use Illuminate\Http\JsonResponse;

class PabellonController extends BaseCalificacionController
{
    public function index(): JsonResponse
    {
        $pabellones = Pabellon::with(['programas:id,nombre'])
            ->withCount('aulas')
            ->orderBy('nombre')
            ->get();

        return $this->successResponse($pabellones);
    }

    public function store(StorePabellonRequest $request): JsonResponse
    {
        $data = $request->validated();
        $programas = $data['programas'] ?? [];
        unset($data['programas']);

        $pabellon = Pabellon::create($data);

        if (!empty($programas)) {
            $pabellon->programas()->sync($programas);
        }

        $pabellon->load(['programas:id,nombre']);

        return $this->successResponse($pabellon, 'Pabellón creado correctamente', 201);
    }

    public function show(int $id): JsonResponse
    {
        $pabellon = Pabellon::with(['programas:id,nombre', 'aulas'])->find($id);

        if (!$pabellon) {
            return $this->errorResponse('Pabellón no encontrado', 404);
        }

        return $this->successResponse($pabellon);
    }

    public function update(UpdatePabellonRequest $request, int $id): JsonResponse
    {
        $pabellon = Pabellon::find($id);

        if (!$pabellon) {
            return $this->errorResponse('Pabellón no encontrado', 404);
        }

        $data = $request->validated();
        $programas = $data['programas'] ?? null;
        unset($data['programas']);

        $pabellon->update($data);

        if ($programas !== null) {
            $pabellon->programas()->sync($programas);
        }

        $pabellon->load(['programas:id,nombre']);

        return $this->successResponse($pabellon, 'Pabellón actualizado correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $pabellon = Pabellon::find($id);

        if (!$pabellon) {
            return $this->errorResponse('Pabellón no encontrado', 404);
        }

        $pabellon->delete();

        return $this->successResponse(null, 'Pabellón eliminado correctamente');
    }

    public function aulas(int $id): JsonResponse
    {
        $pabellon = Pabellon::find($id);

        if (!$pabellon) {
            return $this->errorResponse('Pabellón no encontrado', 404);
        }

        return $this->successResponse($pabellon->aulas()->orderBy('piso')->orderBy('codigo')->get());
    }
}
