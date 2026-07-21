<?php

namespace App\Modules\Pruebas\Controllers;

use App\Modules\Pruebas\Requests\CalificarPruebaRequest;
use App\Modules\Pruebas\Services\CalificacionPruebaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalificacionPruebaController extends BasePruebaController
{
    public function __construct(
        private readonly CalificacionPruebaService $calificacionService
    ) {}

    public function calificar(CalificarPruebaRequest $request, int $idPrueba): JsonResponse
    {
        try {
            $result = $this->calificacionService->calificar(
                $idPrueba,
                $request->input('id_ponderacion'),
                $request->input('id_multiplicador')
            );
            return $this->successResponse($result, 'Calificación completada');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al calificar: ' . $e->getMessage(), 500);
        }
    }

    public function resultados(Request $request, int $idPrueba): JsonResponse
    {
        $conPostulante = (bool) $request->get('con_postulante', false);

        try {
            return $this->successResponse(
                $this->calificacionService->getResultados($idPrueba, $conPostulante)
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function excel(Request $request, int $idPrueba): BinaryFileResponse
    {
        $conPostulante = (bool) $request->get('con_postulante', false);

        try {
            return $this->calificacionService->exportExcel($idPrueba, $conPostulante);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
