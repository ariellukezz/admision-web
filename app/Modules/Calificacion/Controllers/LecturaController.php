<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Services\FileParsingService;
use App\Modules\Calificacion\Services\CalificacionService;
use App\Modules\Calificacion\Models\ArchivoLectura;
use App\Modules\Calificacion\Models\Calificacion;
use App\Modules\Calificacion\Models\Ide;
use App\Modules\Calificacion\Models\Resp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class LecturaController extends BaseCalificacionController
{
    public function __construct(
        private readonly FileParsingService $fileParsingService,
        private readonly CalificacionService $calificacionService
    ) {}

    public function cargaIde(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:txt,dat',
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'area' => 'required|string',
        ]);

        try {
            $result = $this->fileParsingService->cargarArchivoIde(
                $request->file('file'),
                $request->input('id_calificacion'),
                $request->input('area'),
            );
            return $this->successResponse($result, 'Archivo IDE cargado correctamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cargar archivo: ' . $e->getMessage(), 500);
        }
    }

    public function cargaRes(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:txt,dat',
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'area' => 'nullable|string',
        ]);

        try {
            $result = $this->fileParsingService->cargarArchivoRes(
                $request->file('file'),
                $request->input('id_calificacion'),
                $request->input('area'),
            );
            return $this->successResponse($result, 'Archivo RES cargado correctamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cargar archivo: ' . $e->getMessage(), 500);
        }
    }

    public function cargaPat(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:txt,dat',
            'proceso' => 'required|integer',
            'cod_examen' => 'nullable|string',
        ]);

        try {
            $result = $this->fileParsingService->cargarArchivoPat(
                $request->file('file'),
                $request->input('proceso'),
                $request->input('cod_examen'),
            );
            return $this->successResponse($result, 'Archivo PAT cargado correctamente', 201);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cargar archivo: ' . $e->getMessage(), 500);
        }
    }

    public function actualizarIde(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer|exists:ides,id',
            'dni' => 'required|string',
            'tipo' => 'nullable|string',
            'aula' => 'nullable|string',
            'estado' => 'nullable|integer',
        ]);

        try {
            $result = $this->fileParsingService->actualizarIde($request->all());
            return $this->successResponse($result, 'IDE actualizado correctamente');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function archivos(Request $request): JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'tipo' => 'nullable|string|in:ide,res',
        ]);

        $query = ArchivoLectura::where('id_calificacion', $request->input('id_calificacion'))
            ->select('id', 'nombre', 'tipo', 'area', 'url', 'estado', 'created_at');

        if ($request->has('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        $archivos = $query->orderBy('id', 'DESC')->get()->map(function ($a) {
            $count = $a->tipo === 'ide'
                ? Ide::where('id_archivo', $a->id)->count()
                : Resp::where('id_archivo', $a->id)->count();

            return [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'tipo' => $a->tipo,
                'area' => $a->area,
                'fecha' => $a->created_at?->format('Y-m-d'),
                'registros' => $count,
                'estado' => $a->estado,
            ];
        });

        return $this->successResponse($archivos);
    }

    public function ides(Request $request, int $idArchivo): JsonResponse
    {
        $archivo = ArchivoLectura::find($idArchivo);
        if (!$archivo) {
            return $this->errorResponse('Archivo no encontrado', 404);
        }

        $ides = Ide::where('id_archivo', $idArchivo)
            ->select('id', 'camp2', 'dni', 'aula', 'tipo', 'litho', 'estado')
            ->orderBy('id')
            ->get()
            ->map(function ($ide) {
                $observaciones = [];
                if (!$ide->dni) $observaciones[] = 'Sin DNI';
                if ($ide->aula === '' || $ide->aula === null) $observaciones[] = 'Sin aula';
                if ($ide->dni && strlen($ide->dni) !== 8) $observaciones[] = 'DNI erroneo';
                if ($ide->tipo === '' || $ide->tipo === null) $observaciones[] = 'Sin tipo';
                if ($ide->estado !== 1) $observaciones[] = 'No se calificará';

                return [
                    'id' => $ide->id,
                    'camp2' => $ide->camp2,
                    'dni' => $ide->dni,
                    'aula' => $ide->aula,
                    'tipo' => $ide->tipo,
                    'litho' => $ide->litho,
                    'estado' => $ide->estado,
                    'observaciones' => $observaciones,
                ];
            });

        return $this->successResponse($ides);
    }

    public function respuestas(Request $request, int $idArchivo): JsonResponse
    {
        $archivo = ArchivoLectura::find($idArchivo);
        if (!$archivo) {
            return $this->errorResponse('Archivo no encontrado', 404);
        }

        $respuestas = Resp::where('id_archivo', $idArchivo)
            ->select('id', 'n_lectura', 'litho', 'tipo', 'respuestas')
            ->orderBy('id')
            ->get();

        return $this->successResponse($respuestas);
    }

    public function destroyArchivo(int $id): JsonResponse
    {
        $archivo = ArchivoLectura::find($id);
        if (!$archivo) {
            return $this->errorResponse('Archivo no encontrado', 404);
        }

        try {
            if ($archivo->tipo === 'ide') {
                Ide::where('id_archivo', $id)->delete();
            } else {
                Resp::where('id_archivo', $id)->delete();
            }

            $ruta = storage_path('app/' . $archivo->url);
            if (file_exists($ruta)) {
                @unlink($ruta);
            }

            $archivo->delete();

            return $this->successResponse(null, 'Archivo eliminado correctamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al eliminar: ' . $e->getMessage(), 500);
        }
    }

    public function pdfErrores(Request $request): Response|JsonResponse
    {
        $request->validate(['id_calificacion' => 'required|integer|exists:calificaciones,id']);

        $calificacion = Calificacion::with('proceso')->find($request->input('id_calificacion'));
        if (!$calificacion) {
            return $this->errorResponse('Calificación no encontrada', 404);
        }

        $idCalificacion = $calificacion->id;
        $idProceso = $calificacion->id_proceso;

        $duplicadosDni = DB::select(
            "SELECT a.nombre as archivo, i.camp2 as lectura, i.litho, i.dni
            FROM ides i
            JOIN archivo_lectura a ON a.id = i.id_archivo
            WHERE a.id_calificacion = ?
            AND i.dni IN (
                SELECT dni FROM ides
                JOIN archivo_lectura ON archivo_lectura.id = ides.id_archivo
                WHERE archivo_lectura.id_calificacion = ?
                GROUP BY dni HAVING COUNT(*) > 1
            )
            ORDER BY i.dni",
            [$idCalificacion, $idCalificacion]
        );

        $errores = DB::select(
            "SELECT a.nombre as archivo, i.camp2 as lectura, i.litho, i.dni, i.tipo, i.aula,
                CASE WHEN i.aula IS NOT NULL AND i.aula REGEXP '^[0-9]+$' THEN 1 ELSE 0 END as vaula,
                CASE WHEN i.dni IS NOT NULL AND LENGTH(i.dni) = 8 AND i.dni REGEXP '^[0-9]+$' THEN 1 ELSE 0 END as vdni,
                LENGTH(i.dni) as len_doc,
                CASE WHEN i.litho IS NOT NULL AND LENGTH(i.litho) = 6 THEN 1 ELSE 0 END as vlitho,
                CASE WHEN i.dni IS NOT NULL THEN (SELECT 1 FROM participantes p WHERE p.dni = i.dni AND p.id_proceso = ? LIMIT 1) ELSE NULL END as dnip
            FROM ides i
            JOIN archivo_lectura a ON a.id = i.id_archivo
            WHERE a.id_calificacion = ?
            AND (
                i.tipo IS NULL
                OR i.aula IS NULL
                OR (i.aula IS NOT NULL AND i.aula REGEXP '^[0-9]+$' = 0)
                OR (i.dni IS NOT NULL AND LENGTH(i.dni) != 8)
                OR (i.dni IS NOT NULL AND i.dni REGEXP '^[0-9]+$' = 0)
                OR i.litho IS NULL
                OR LENGTH(i.litho) != 6
                OR i.dni IS NULL
                OR (i.dni IS NOT NULL AND LENGTH(i.dni) = 8 AND i.dni REGEXP '^[0-9]+$'
                    AND NOT EXISTS (SELECT 1 FROM participantes p WHERE p.dni = i.dni AND p.id_proceso = ?))
            )
            ORDER BY i.id",
            [$idProceso, $idCalificacion, $idProceso]
        );

        $html = View::make('calificacion::errores', [
            'proceso' => $calificacion->proceso ?? $calificacion,
            'duplicados_dni' => $duplicadosDni,
            'errores' => $errores,
        ])->render();

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 30,
            'margin_bottom' => 20,
            'tempDir' => $tempDir,
        ]);

        $titulo = 'Errores - ' . ($calificacion->proceso->nombre ?? $calificacion->nombre);
        $mpdf->SetTitle($titulo);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'errores_lecturas_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function fichaPdf(Request $request): Response|JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'tipo' => 'required|string|in:ide,res',
        ]);

        $id = $request->input('id');
        $tipo = $request->input('tipo');

        $dni = null;
        $aula = null;
        $tipoExamen = null;
        $litho = null;
        $respuestas = '';

        if ($tipo === 'ide') {
            $ide = Ide::find($id);
            if (!$ide) {
                return $this->errorResponse('IDE no encontrado', 404);
            }
            $dni = $ide->dni;
            $aula = $ide->aula;
            $tipoExamen = $ide->tipo;
            $litho = $ide->litho;

            $archivo = ArchivoLectura::find($ide->id_archivo);
            if ($archivo) {
                $resp = Resp::where('litho', $ide->litho)
                    ->whereHas('archivo', fn($q) => $q->where('id_calificacion', $archivo->id_calificacion))
                    ->first();
                if ($resp) {
                    $respuestas = $resp->respuestas ?? '';
                }
            }
        } else {
            $resp = Resp::find($id);
            if (!$resp) {
                return $this->errorResponse('Respuesta no encontrada', 404);
            }
            $litho = $resp->litho;
            $tipoExamen = $resp->tipo;
            $respuestas = $resp->respuestas ?? '';

            $archivo = ArchivoLectura::find($resp->id_archivo);
            if ($archivo) {
                $ide = Ide::where('litho', $resp->litho)
                    ->whereHas('archivo', fn($q) => $q->where('id_calificacion', $archivo->id_calificacion))
                    ->first();
                if ($ide) {
                    $dni = $ide->dni;
                    $aula = $ide->aula;
                    $tipoExamen = $ide->tipo ?? $tipoExamen;
                }
            }
        }

        $html = View::make('calificacion::ficha', [
            'dni' => $dni,
            'aula' => $aula,
            'tipo' => $tipoExamen,
            'litho' => $litho,
            'respuestas' => $respuestas,
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
            'margin_top' => 20,
            'margin_bottom' => 20,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Ficha — ' . ($litho ?? $id));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'ficha_' . ($litho ?? $id) . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
