<?php

namespace App\Modules\Pruebas\Controllers;

use App\Modules\Pruebas\Services\FichaPruebaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FichaPruebaController extends BasePruebaController
{
    public function __construct(
        private readonly FichaPruebaService $fichaService
    ) {}

    public function individual(Request $request, int $idPrueba): Response|JsonResponse
    {
        $request->validate([
            'dni' => 'required|string',
        ]);

        try {
            return $this->fichaService->fichaPdf($idPrueba, $request->input('dni'));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function masivo(Request $request, int $idPrueba): Response|JsonResponse
    {
        $request->validate([
            'dnis' => 'required|array|min:1|max:500',
            'dnis.*' => 'required|string',
        ]);

        try {
            return $this->fichaService->fichaPdfMasivo($idPrueba, $request->input('dnis'));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function ranking(Request $request, int $idPrueba): Response|JsonResponse
    {
        $conPostulante = (bool) $request->get('con_postulante', false);

        try {
            return $this->fichaService->rankingPdf($idPrueba, $conPostulante);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
