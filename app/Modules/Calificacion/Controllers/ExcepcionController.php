<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Excepciones;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class ExcepcionController extends BaseCalificacionController
{
    public function index(Request $request): JsonResponse
    {
        $query = Excepciones::query();

        if ($request->has('id_proceso')) {
            $query->where('id_proceso', $request->id_proceso);
        }

        return $this->successResponse($query->orderBy('id', 'desc')->paginate(10));
    }

    public function byProceso(int $idProceso): JsonResponse
    {
        return $this->successResponse(
            Excepciones::where('id_proceso', $idProceso)->orderBy('nro_pregunta', 'asc')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nro_pregunta' => 'required|integer',
            'accion' => 'required|string|max:255',
            'cod_examen' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'claves_validas' => 'nullable|string',
            'puntaje' => 'required|numeric',
            'tipo' => 'required|string|max:50',
            'id_proceso' => 'required|integer',
        ]);

        return $this->successResponse(Excepciones::create($validated), 'Excepción creada exitosamente', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $excepcion = Excepciones::find($id);
        if (!$excepcion) {
            return $this->errorResponse('Excepción no encontrada', 404);
        }

        $excepcion->update($request->all());
        return $this->successResponse($excepcion, 'Excepción actualizada exitosamente');
    }

    public function destroy(int $id): JsonResponse
    {
        $excepcion = Excepciones::find($id);
        if (!$excepcion) {
            return $this->errorResponse('Excepción no encontrada', 404);
        }

        $excepcion->delete();
        return $this->successResponse(null, 'Excepción eliminada exitosamente');
    }

    public function search(Request $request): JsonResponse
    {
        $query = Excepciones::query();

        if ($request->has('id_proceso')) $query->where('id_proceso', $request->id_proceso);
        if ($request->has('cod_examen')) $query->where('cod_examen', 'like', '%' . $request->cod_examen . '%');
        if ($request->has('nro_pregunta')) $query->where('nro_pregunta', $request->nro_pregunta);
        if ($request->has('tipo')) $query->where('tipo', $request->tipo);

        return $this->successResponse($query->orderBy('id', 'desc')->get());
    }

    public function pdf(Request $request): Response
    {
        $query = Excepciones::query();

        $idProceso = $request->get('id_proceso');
        if ($idProceso) {
            $query->where('id_proceso', $idProceso);
        }

        $excepciones = $query->orderBy('nro_pregunta', 'ASC')->get();

        $html = View::make('calificacion::pdf.excepciones', [
            'excepciones' => $excepciones,
            'total' => $excepciones->count(),
            'idProceso' => $idProceso,
        ])->render();

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Reporte de Excepciones');
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'excepciones_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
