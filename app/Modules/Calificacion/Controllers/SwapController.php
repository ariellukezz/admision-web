<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Requests\StoreSwapRequest;
use App\Modules\Calificacion\Services\SwapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SwapController extends BaseCalificacionController
{
    public function __construct(
        private readonly SwapService $swapService
    ) {}

    public function store(StoreSwapRequest $request, int $filterGroupId): JsonResponse
    {
        try {
            $result = $this->swapService->performSwaps(
                $filterGroupId,
                $request->input('swaps'),
                $request->input('reason'),
                auth()->id(),
            );

            return $this->successResponse($result, 'Swaps realizados correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('Error en performSwaps', [
                'grupo_filtro_id' => $filterGroupId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al realizar swaps: ' . $e->getMessage(), 500);
        }
    }

    public function auditLog(int $filterGroupId): JsonResponse
    {
        try {
            return $this->successResponse($this->swapService->getAuditLog($filterGroupId));
        } catch (\Exception $e) {
            Log::error('Error en getAuditLog', [
                'grupo_filtro_id' => $filterGroupId,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }
}
