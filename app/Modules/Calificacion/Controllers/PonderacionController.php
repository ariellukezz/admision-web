<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Requests\StorePonderacionRequest;
use App\Modules\Calificacion\Requests\StorePonderacionDetalleRequest;
use App\Modules\Calificacion\Services\PonderacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PonderacionController extends BaseCalificacionController
{
    public function __construct(
        private readonly PonderacionService $ponderacionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->ponderacionService->getPonderaciones($request->term, $request->paginasize ?? 10)
        );
    }

    public function store(StorePonderacionRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->ponderacionService->save($request->all()),
            'Ponderación guardada correctamente'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->ponderacionService->delete($id);
            return $this->successResponse(null, 'Ponderación eliminada correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function duplicar(int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                $this->ponderacionService->duplicar($id),
                'Ponderación duplicada correctamente'
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function showDetalle(int $id): JsonResponse
    {
        return $this->successResponse($this->ponderacionService->getDetalle($id));
    }

    public function storeDetalle(StorePonderacionDetalleRequest $request): JsonResponse
    {
        try {
            $this->ponderacionService->saveDetalle($request->input('id_ponderacion'), $request->input('detalles'));
            return $this->successResponse(null, 'Detalle guardado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function select(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->ponderacionService->getSelect($request->term, $request->paginasize ?? 10)
        );
    }

    public function areas(): JsonResponse
    {
        return $this->successResponse($this->ponderacionService->getAreas());
    }

    public function pdf(int $id): Response
    {
        try {
            return $this->ponderacionService->generateDetallePdf($id);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
