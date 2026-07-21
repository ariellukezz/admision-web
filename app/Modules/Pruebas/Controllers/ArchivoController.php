<?php

namespace App\Modules\Pruebas\Controllers;

use App\Modules\Pruebas\Requests\CargaArchivoRequest;
use App\Modules\Pruebas\Services\ArchivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArchivoController extends BasePruebaController
{
    public function __construct(
        private readonly ArchivoService $archivoService
    ) {}

    public function cargar(CargaArchivoRequest $request, int $idPrueba): JsonResponse
    {
        try {
            $result = $this->archivoService->cargarArchivo(
                $idPrueba,
                $request->file('file'),
                $request->input('tipo')
            );
            return $this->successResponse($result, 'Archivo cargado correctamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cargar archivo: ' . $e->getMessage(), 500);
        }
    }

    public function index(Request $request, int $idPrueba): JsonResponse
    {
        return $this->successResponse(
            $this->archivoService->getArchivos($idPrueba, $request->get('tipo'))
        );
    }

    public function registros(int $idArchivo): JsonResponse
    {
        try {
            return $this->successResponse($this->archivoService->getRegistros($idArchivo));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function tipos(int $idPrueba): JsonResponse
    {
        return $this->successResponse(
            $this->archivoService->getTiposByPrueba($idPrueba)
        );
    }

    public function destroy(int $idArchivo): JsonResponse
    {
        try {
            $this->archivoService->destroyArchivo($idArchivo);
            return $this->successResponse(null, 'Archivo eliminado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function actualizarIde(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:prueba_ides,id',
            'dni' => 'required|string',
            'tipo' => 'nullable|string',
            'aula' => 'nullable|string',
            'estado' => 'nullable|integer',
        ]);

        try {
            $result = $this->archivoService->actualizarIde($request->all());
            return $this->successResponse($result, 'IDE actualizado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
