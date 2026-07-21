<?php

namespace App\Modules\Pruebas\Controllers;

use App\Modules\Pruebas\Requests\StoreExcepcionPruebaRequest;
use App\Modules\Pruebas\Models\PruebaExcepcion;
use App\Modules\Pruebas\Models\PruebaTipo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExcepcionPruebaController extends BasePruebaController
{
    public function index(int $idPrueba, int $idTipo): JsonResponse
    {
        $tipo = PruebaTipo::where('id', $idTipo)->where('id_prueba', $idPrueba)->first();
        if (!$tipo) {
            return $this->errorResponse('Tipo no encontrado', 404);
        }

        return $this->successResponse(
            PruebaExcepcion::where('id_prueba_tipo', $idTipo)
                ->orderBy('nro_pregunta', 'ASC')
                ->get()
        );
    }

    public function store(StoreExcepcionPruebaRequest $request, int $idPrueba, int $idTipo): JsonResponse
    {
        $tipo = PruebaTipo::where('id', $idTipo)->where('id_prueba', $idPrueba)->first();
        if (!$tipo) {
            return $this->errorResponse('Tipo no encontrado', 404);
        }

        $excepcion = PruebaExcepcion::updateOrCreate(
            [
                'id_prueba_tipo' => $idTipo,
                'nro_pregunta' => $request->input('nro_pregunta'),
            ],
            [
                'accion' => $request->input('accion'),
                'claves_validas' => $request->input('claves_validas'),
                'puntaje' => $request->input('puntaje', 0),
                'observacion' => $request->input('observacion'),
                'tipo' => $tipo->tipo,
            ]
        );

        return $this->successResponse($excepcion, 'Excepción guardada correctamente', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $excepcion = PruebaExcepcion::find($id);
        if (!$excepcion) {
            return $this->errorResponse('Excepción no encontrada', 404);
        }

        $excepcion->update($request->only([
            'nro_pregunta', 'accion', 'claves_validas', 'puntaje', 'observacion', 'tipo'
        ]));

        return $this->successResponse($excepcion, 'Excepción actualizada correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $excepcion = PruebaExcepcion::find($id);
        if (!$excepcion) {
            return $this->errorResponse('Excepción no encontrada', 404);
        }

        $excepcion->delete();
        return $this->successResponse(null, 'Excepción eliminada correctamente');
    }
}
