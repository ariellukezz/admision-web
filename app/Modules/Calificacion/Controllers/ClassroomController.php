<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Requests\GenerateClassroomsRequest;
use App\Modules\Calificacion\Services\ClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ClassroomController extends BaseCalificacionController
{
    public function __construct(
        private readonly ClassroomService $classroomService
    ) {}

    public function generate(GenerateClassroomsRequest $request): JsonResponse
    {
        try {
            $result = $this->classroomService->generateDistribution(
                $request->input('filter_group_id'),
                $request->input('capacity_per_classroom', 40),
                $request->input('capacity_exceptions', []),
            );

            return $this->successResponse($result, 'Distribución generada correctamente (agrupada por área)', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error en generateDistribution', [
                'grupo_filtro_id' => $request->input('filter_group_id'),
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al generar distribución: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $filterGroupId): JsonResponse
    {
        try {
            return $this->successResponse($this->classroomService->getDistribution($filterGroupId));
        } catch (\Exception $e) {
            Log::error('Error en getDistribution', [
                'grupo_filtro_id' => $filterGroupId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al obtener distribución: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(int $filterGroupId): JsonResponse
    {
        try {
            $result = $this->classroomService->deleteDistribution($filterGroupId);

            return $this->successResponse($result, 'Distribución eliminada correctamente');
        } catch (\Exception $e) {
            Log::error('Error en deleteDistribution', [
                'grupo_filtro_id' => $filterGroupId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }
}
