<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Services\DistributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FilterController extends BaseCalificacionController
{
    public function __construct(
        private readonly DistributionService $distributionService
    ) {}

    public function statsByModalities(Request $request): JsonResponse
    {
        $request->validate([
            'id_modalidades' => 'required|array|min:1',
            'id_modalidades.*' => 'integer',
            'id_proceso' => 'required|integer',
            'filter_type' => 'nullable|string|in:area,programa',
            'filter_ids' => 'nullable|array',
        ]);

        try {
            return $this->successResponse($this->distributionService->getStatsByModalities(
                $request->input('id_modalidades'),
                $request->input('id_proceso'),
                $request->input('filter_type'),
                $request->input('filter_ids', []),
            ));
        } catch (\Exception $e) {
            Log::error('Error en statsByModalities: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    public function availablePostulantes(Request $request): JsonResponse
    {
        $request->validate([
            'id_modalidades' => 'required|array|min:1',
            'id_modalidades.*' => 'integer',
            'id_proceso' => 'required|integer',
            'filter_type' => 'nullable|string|in:area,programa',
            'filter_ids' => 'nullable|array',
            'limit' => 'nullable|integer|min:1|max:1000',
            'offset' => 'nullable|integer|min:0',
        ]);

        try {
            $result = $this->distributionService->getAvailablePostulantes(
                $request->input('id_modalidades'),
                $request->input('id_proceso'),
                $request->input('filter_type'),
                $request->input('filter_ids', []),
                (int) $request->input('limit', 50),
                (int) $request->input('offset', 0),
            );

            return $this->successResponse($result['data'], '', 200, $result['meta']);
        } catch (\Exception $e) {
            Log::error('Error en availablePostulantes: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener postulantes: ' . $e->getMessage(), 500);
        }

    }

    public function generateAndSave(Request $request): JsonResponse
    {
        $request->validate([
            'table_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'id_modalidades' => 'required|array|min:1',
            'id_modalidades.*' => 'integer',
            'id_proceso' => 'required|integer',
            'filter_type' => 'nullable|string|in:area,programa',
            'filter_ids' => 'nullable|array',
            'processing_order' => 'nullable|integer|min:1|max:999',
            'conditions' => 'required|array|min:1',
            'conditions.*.column' => 'required|string',
            'conditions.*.condition' => 'required|string|in:last_n,first_n,nth_digit,middle_m_n,reverse_n_m',
            'conditions.*.params' => 'required|array',
        ]);

        try {
            $result = $this->distributionService->generateAndSaveCodes($request->all());

            return $this->successResponse($result, 'Códigos generados y guardados exitosamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Error en generateAndSaveCodes', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar códigos: ' . $e->getMessage(), 500);
        }
    }


    public function preview(int $id): JsonResponse
    {
        try {
            return $this->successResponse($this->distributionService->getPreview($id));
        } catch (\Exception $e) {
            Log::error('Error en getPreview: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener preview: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(int $id): JsonResponse
    {
        try {
            $this->distributionService->deleteFilterGroup($id);
            return $this->successResponse(null, 'Grupo de filtrado eliminado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en deleteFilterGroup: ' . $e->getMessage());
            return $this->errorResponse('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }


    public function groups(): JsonResponse
    {
        try {
            return $this->successResponse($this->distributionService->getFilterGroups());
        } catch (\Exception $e) {
            Log::error('Error en getFilterGroups: ' . $e->getMessage());
            return $this->errorResponse('Error al obtener grupos: ' . $e->getMessage(), 500);
        }
    }


    public function processingStats(Request $request): JsonResponse
    {
        $request->validate([
            'id_modalidades' => 'required|array|min:1',
            'id_proceso' => 'required|integer',
        ]);

        try {
            return $this->successResponse($this->distributionService->getStatsByModalities(
                $request->input('id_modalidades'),
                $request->input('id_proceso'),
                $request->input('filter_type'),
                $request->input('filter_ids', []),
            ));
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
    

    public function postulantesForCodes(Request $request): JsonResponse
    {
        return $this->availablePostulantes($request);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->generateAndSave($request);
    }
}
