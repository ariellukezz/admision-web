<?php

namespace App\Modules\Pruebas\Controllers;

use App\Modules\Pruebas\Requests\StorePruebaRequest;
use App\Modules\Pruebas\Requests\UpdatePruebaRequest;
use App\Modules\Pruebas\Services\PruebaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PruebaController extends BasePruebaController
{
    public function __construct(
        private readonly PruebaService $pruebaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $term = $request->get('term');

        return $this->successResponse(
            $this->pruebaService->index($term, $perPage)
        );
    }

    public function store(StorePruebaRequest $request): JsonResponse
    {
        try {
            $prueba = $this->pruebaService->store($request->validated());
            return $this->successResponse($prueba, 'Prueba creada correctamente', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear prueba: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $idPrueba): JsonResponse
    {
        try {
            return $this->successResponse($this->pruebaService->show($idPrueba));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(UpdatePruebaRequest $request, int $idPrueba): JsonResponse
    {
        try {
            $prueba = $this->pruebaService->update($idPrueba, $request->validated());
            return $this->successResponse($prueba, 'Prueba actualizada correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroy(int $idPrueba): JsonResponse
    {
        try {
            $this->pruebaService->destroy($idPrueba);
            return $this->successResponse(null, 'Prueba eliminada correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
