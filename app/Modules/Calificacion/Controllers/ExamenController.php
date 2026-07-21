<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Requests\StoreExamenRequest;
use App\Modules\Calificacion\Requests\UpdateExamenRequest;
use App\Modules\Calificacion\Requests\StoreExamenTipoRequest;
use App\Modules\Calificacion\Services\ExamenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamenController extends BaseCalificacionController
{
    public function __construct(
        private readonly ExamenService $examenService
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(
            $this->examenService->index(
                $request->term,
                $request->paginasize ?? 10,
                $request->id_area ? (int) $request->id_area : null
            )
        );
    }

    public function store(StoreExamenRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->examenService->store($request->all()),
            'Examen creado correctamente',
            201
        );
    }

    public function update(UpdateExamenRequest $request, int $id): JsonResponse
    {
        try {
            return $this->successResponse(
                $this->examenService->update($id, $request->all()),
                'Examen actualizado correctamente'
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->examenService->destroy($id);
            return $this->successResponse(null, 'Examen eliminado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function tipos(int $idExamen): JsonResponse
    {
        return $this->successResponse($this->examenService->getTipos($idExamen));
    }

    public function storeTipo(StoreExamenTipoRequest $request): JsonResponse
    {
        $tipo = $this->examenService->saveTipo($request->all());
        return $this->successResponse($tipo, $request->id ? 'Tipo actualizado' : 'Tipo creado');
    }

    public function updateTipo(StoreExamenTipoRequest $request, int $id): JsonResponse
    {
        $data = array_merge($request->all(), ['id' => $id]);
        $tipo = $this->examenService->saveTipo($data);
        return $this->successResponse($tipo, 'Tipo actualizado');
    }

    public function destroyTipo(int $id): JsonResponse
    {
        try {
            $this->examenService->deleteTipo($id);
            return $this->successResponse(null, 'Tipo eliminado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function uploadTiposArchivo(Request $request, int $idExamen): JsonResponse
    {
        $request->validate(['file' => 'required|file']);

        try {
            $count = $this->examenService->uploadTiposArchivo($idExamen, $request->file('file'));
            return $this->successResponse(null, "Tipos creados desde archivo: {$count} tipo(s)");
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function archivo(int $idArchivo): JsonResponse
    {
        try {
            return $this->successResponse($this->examenService->verArchivo($idArchivo));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function res(int $idTipo): JsonResponse
    {
        return $this->successResponse($this->examenService->getRes($idTipo));
    }

    public function storeRes(Request $request, int $idTipo): JsonResponse
    {
        $request->validate(['file' => 'required|file']);

        try {
            $result = $this->examenService->storeRes($idTipo, $request->file('file'));
            return $this->successResponse($result, 'Archivo RES cargado correctamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroyRes(int $idTipo): JsonResponse
    {
        try {
            $this->examenService->destroyRes($idTipo);
            return $this->successResponse(null, 'Respuestas eliminadas correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function excepciones(int $idTipo): JsonResponse
    {
        return $this->successResponse($this->examenService->getExcepciones($idTipo));
    }

    public function storeExcepcion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_examen_tipo' => 'required|integer',
            'nro_pregunta' => 'required|integer',
            'accion' => 'required|string|in:todas_validas,multiples_validas,anulada,asignar_puntaje',
            'claves_validas' => 'nullable|string',
            'puntaje' => 'nullable|numeric',
            'observacion' => 'nullable|string',
            'tipo' => 'nullable|string|max:50'
        ]);

        try {
            return $this->successResponse(
                $this->examenService->storeExcepcion($validated),
                'Excepción creada correctamente',
                201
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroyExcepcion(int $id): JsonResponse
    {
        try {
            $this->examenService->destroyExcepcion($id);
            return $this->successResponse(null, 'Excepción eliminada correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
