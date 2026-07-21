<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Services\ConflictDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ConflictController extends BaseCalificacionController
{
    public function __construct(
        private readonly ConflictDetectionService $conflictService
    ) {}

    public function detect(int $filterGroupId): JsonResponse
    {
        try {
            return $this->successResponse($this->conflictService->detectConflicts($filterGroupId));
        } catch (\Exception $e) {
            Log::error('Error en detectConflicts', [
                'grupo_filtro_id' => $filterGroupId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al detectar conflictos: ' . $e->getMessage(), 500);
        }
    }
}
