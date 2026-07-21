<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Calificacion;
use App\Modules\Calificacion\Requests\CalificarExamenRequest;
use App\Modules\Calificacion\Services\CalificacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class CalificacionController extends BaseCalificacionController
{
    public function __construct(
        private readonly CalificacionService $calificacionService
    ) {}

    public function calificar(CalificarExamenRequest $request): JsonResponse
    {
        try {
            $comparaciones = $this->calificacionService->calificar(
                $request->input('id_calificacion'),
                $request->input('id_examen'),
                $request->input('id_ponderacion'),
                $request->input('id_multiplicador'),
            );

            return $this->successResponse($comparaciones, 'Calificación completada');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al calificar: ' . $e->getMessage(), 500);
        }
    }

    public function asignar(Request $request): JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'id_examen' => 'required|integer|exists:examen_simulacro,id',
        ]);

        try {
            $this->calificacionService->asignarResultados(
                $request->input('id_calificacion'),
                $request->input('id_examen'),
            );

            return $this->successResponse(null, 'Asignación completada: puestos, ingresantes y clasificados');
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error en asignación: ' . $e->getMessage(), 500);
        }
    }

    public function resultados(Request $request): JsonResponse
    {
        $request->validate(['id_proceso' => 'required|integer']);

        try {
            return $this->successResponse(
                $this->calificacionService->getResultados($request->input('id_proceso'))
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function postulantes(Request $request): JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer',
            'id_examen' => 'nullable|integer|exists:examen_simulacro,id',
        ]);

        try {
            return $this->successResponse(
                $this->calificacionService->getPostulantesCalificacion(
                    $request->input('id_calificacion'),
                    $request->input('id_examen')
                )
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener postulantes: ' . $e->getMessage(), 500);
        }
    }

    public function descargarExcelPostulantes(Request $request)
    {
        $request->validate([
            'id_calificacion' => 'required|integer',
            'id_examen' => 'nullable|integer',
        ]);

        try {
            return $this->calificacionService->descargarExcelPostulantes(
                $request->input('id_calificacion'),
                $request->input('id_examen')
            );
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al exportar Excel: ' . $e->getMessage(), 500);
        }
    }

    public function pdfErrores(Request $request)
    {
        $request->validate(['id_proceso' => 'required|integer']);

        try {
            return $this->calificacionService->generateErroresPdf($request->input('id_proceso'));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function resultadosPdf(Request $request)
    {
        $request->validate(['id_proceso' => 'required|integer']);

        try {
            return $this->calificacionService->generateResultadosPdf($request->input('id_proceso'));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function pdfVacantes(Request $request): Response|JsonResponse
    {
        $request->validate(['id_calificacion' => 'required|integer|exists:calificaciones,id']);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        if (!$calificacion) {
            return $this->errorResponse('Calificación no encontrada', 404);
        }

        $idProceso = $calificacion->id_proceso;

        // Total de postulantes del proceso
        $totalPostulantes = DB::table('inscripciones')
            ->where('id_proceso', $idProceso)
            ->count();

        // 20% del total de postulantes (exceso para segunda etapa)
        $veintePorciento = (int) ceil($totalPostulantes * 0.20);

        // Vacantes con programa y etapa
        $vacantes = DB::table('vacantes as v')
            ->join('programa as p', 'v.id_programa', '=', 'p.id')
            ->where('v.id_proceso', $idProceso)
            ->select(
                'v.id_programa',
                'p.nombre as programa',
                'v.vacantes',
                'v.etapa',
            )
            ->orderBy('v.etapa')
            ->orderBy('p.nombre')
            ->get();

        // Postulantes por programa
        $postulantesPorPrograma = DB::table('inscripciones as i')
            ->where('i.id_proceso', $idProceso)
            ->where('i.estado', 0)
            ->select('i.id_programa', DB::raw('COUNT(*) as total'))
            ->groupBy('i.id_programa')
            ->pluck('total', 'id_programa');

        // Separar por etapa
        $etapa1 = $vacantes->where('etapa', '1')->values();
        $etapa2 = $vacantes->where('etapa', '2')->values();

        $html = View::make('calificacion::vacantes', [
            'calificacion' => $calificacion,
            'totalPostulantes' => $totalPostulantes,
            'veintePorciento' => $veintePorciento,
            'etapa1' => $etapa1,
            'etapa2' => $etapa2,
            'postulantesPorPrograma' => $postulantesPorPrograma,
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
            'margin_top' => 25,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Vacantes — ' . ($calificacion->nombre ?? ''));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'vacantes_' . $calificacion->id . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function generarIngresantesTxt(Request $request): Response|JsonResponse
    {
        $request->validate(['id_calificacion' => 'required|integer|exists:calificaciones,id']);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        $idProceso = $calificacion->id_proceso;
        $idCalificacion = $calificacion->id;

        // Vacantes de etapa 1 únicamente (se ignora etapa 2)
        $vacantes = DB::table('vacantes as v')
            ->join('programa as p', 'v.id_programa', '=', 'p.id')
            ->where('v.id_proceso', $idProceso)
            ->where('v.etapa', '1')
            ->where('v.estado', 1)
            ->select('v.id_programa', 'p.nombre as programa', 'v.vacantes')
            ->orderBy('p.nombre')
            ->get();

        if ($vacantes->isEmpty()) {
            return $this->errorResponse('No hay vacantes de etapa 1 configuradas', 404);
        }

        // Postulantes con puntaje en la calificación
        $postulantes = DB::table('grupos_filtro as gf')
            ->join('inscripciones as ins', 'ins.grupo_filtro_id', '=', 'gf.id')
            ->join('postulante as p', 'ins.id_postulante', '=', 'p.id')
            ->leftJoin('ides as i', function ($join) use ($idCalificacion) {
                $join->on('i.dni', '=', 'p.nro_doc')
                     ->whereIn('i.id_archivo', function ($q) use ($idCalificacion) {
                         $q->select('id')->from('archivo_lectura')
                           ->where('id_calificacion', $idCalificacion);
                     });
            })
            ->leftJoin('res as r', function ($join) use ($idCalificacion) {
                $join->on('r.litho', '=', 'i.litho')
                     ->whereIn('r.id_archivo', function ($q) use ($idCalificacion) {
                         $q->select('id')->from('archivo_lectura')
                           ->where('id_calificacion', $idCalificacion);
                     });
            })
            ->where('gf.id_calificacion', $idCalificacion)
            ->whereNotNull('r.puntaje')
            ->select(
                'ins.id_programa',
                'p.nro_doc as dni',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nombres',
                'r.puntaje'
            )
            ->distinct()
            ->get();

        $padName = fn (string $nombre): string => $nombre . str_repeat(' ', max(0, 52 - mb_strlen($nombre, 'UTF-8')));
        $sep = str_repeat('=', 88);
        $now = now()->format('Y/m/d h:i:s A');
        $center = fn (string $t): string => str_pad($t, (int) ((88 + mb_strlen($t, 'UTF-8')) / 2), ' ', STR_PAD_LEFT);

        $out = '';

        foreach ($vacantes as $v) {
            $progPostulantes = $postulantes
                ->where('id_programa', $v->id_programa)
                ->sortByDesc('puntaje')
                ->take($v->vacantes)
                ->values();

            $out .= "\n";
            $out .= $center('UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO') . "\n";
            $out .= $center('DIRECCIÓN DE ADMISIÓN') . "\n";
            $out .= $center('EXAMEN DE ADMISIÓN') . "\n";
            $out .= ' ' . $sep . "\n\n";
            $out .= $center('RELACIÓN DE INGRESANTES') . "\n\n";
            $out .= $center("[ {$v->vacantes} ]   {$v->programa}") . "\n\n \n";
            $out .= '   PUESTO   DNI            APELLIDOS Y NOMBRES                         PUNTAJE' . "\n";
            $out .= ' ' . $sep . "\n";

            foreach ($progPostulantes as $idx => $post) {
                $puesto = $idx + 1;
                $apellidos = trim($post->primer_apellido . ' ' . $post->segundo_apellido);
                $nombre = $apellidos . ($apellidos !== '' ? ', ' : '') . ($post->nombres ?? '');
                $puntaje = number_format((float) $post->puntaje, 3, '.', '');
                $out .= sprintf("%6d     %-8s     %s%12s\n", $puesto, $post->dni, $padName($nombre), $puntaje);
            }

            $out .= ' ' . $sep . "\n";
            $out .= ' ' . $now . "\n\n\n\n";
        }

        $filename = 'ingresantes_' . $calificacion->id . '_' . date('YmdHis') . '.txt';

        return response($out, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function generarClasificadosTxt(Request $request): Response|JsonResponse
    {
        $request->validate(['id_calificacion' => 'required|integer|exists:calificaciones,id']);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        $idProceso = $calificacion->id_proceso;
        $idCalificacion = $calificacion->id;

        // Solo programas que tienen vacantes de etapa 2 (clasificados)
        $programas = DB::table('vacantes as v')
            ->join('programa as p', 'v.id_programa', '=', 'p.id')
            ->where('v.id_proceso', $idProceso)
            ->where('v.etapa', '2')
            ->where('v.estado', 1)
            ->select('v.id_programa', 'p.nombre as programa')
            ->orderBy('p.nombre')
            ->get();

        if ($programas->isEmpty()) {
            return $this->errorResponse('No hay programas configurados', 404);
        }

        // Total de postulantes por programa en el proceso
        $totalesPorPrograma = DB::table('inscripciones')
            ->where('id_proceso', $idProceso)
            ->select('id_programa', DB::raw('COUNT(*) as total'))
            ->groupBy('id_programa')
            ->pluck('total', 'id_programa');

        // Postulantes con puntaje en la calificación
        $postulantes = DB::table('grupos_filtro as gf')
            ->join('inscripciones as ins', 'ins.grupo_filtro_id', '=', 'gf.id')
            ->join('postulante as p', 'ins.id_postulante', '=', 'p.id')
            ->leftJoin('ides as i', function ($join) use ($idCalificacion) {
                $join->on('i.dni', '=', 'p.nro_doc')
                     ->whereIn('i.id_archivo', function ($q) use ($idCalificacion) {
                         $q->select('id')->from('archivo_lectura')
                           ->where('id_calificacion', $idCalificacion);
                     });
            })
            ->leftJoin('res as r', function ($join) use ($idCalificacion) {
                $join->on('r.litho', '=', 'i.litho')
                     ->whereIn('r.id_archivo', function ($q) use ($idCalificacion) {
                         $q->select('id')->from('archivo_lectura')
                           ->where('id_calificacion', $idCalificacion);
                     });
            })
            ->where('gf.id_calificacion', $idCalificacion)
            ->whereNotNull('r.puntaje')
            ->select(
                'ins.id_programa',
                'p.nro_doc as dni',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nombres',
                'r.puntaje'
            )
            ->distinct()
            ->get();

        $padName = fn (string $nombre): string => $nombre . str_repeat(' ', max(0, 52 - mb_strlen($nombre, 'UTF-8')));
        $sep = str_repeat('=', 88);
        $now = now()->format('Y/m/d h:i:s A');
        $center = fn (string $t): string => str_pad($t, (int) ((88 + mb_strlen($t, 'UTF-8')) / 2), ' ', STR_PAD_LEFT);

        $out = '';

        foreach ($programas as $prog) {
            // 20% excedente del total de postulantes del programa (ceil: 21.1 → 22)
            $totalPrograma = $totalesPorPrograma->get($prog->id_programa, 0);
            $nClasificados = (int) ceil($totalPrograma * 0.20);
            if ($nClasificados < 1) continue;

            $progPostulantes = $postulantes
                ->where('id_programa', $prog->id_programa)
                ->sortByDesc('puntaje')
                ->take($nClasificados)
                ->values();

            $out .= "\n";
            $out .= $center('UNIVERSIDAD NACIONAL DEL ALTIPLANO DE PUNO') . "\n";
            $out .= $center('DIRECCIÓN DE ADMISIÓN') . "\n";
            $out .= $center('EXAMEN DE ADMISIÓN') . "\n";
            $out .= ' ' . $sep . "\n\n";
            $out .= $center('RELACIÓN DE CLASIFICADOS') . "\n\n";
            $out .= $center("[ {$nClasificados} ]   {$prog->programa}") . "\n\n \n";
            $out .= '   PUESTO   DNI            APELLIDOS Y NOMBRES                         PUNTAJE' . "\n";
            $out .= ' ' . $sep . "\n";

            foreach ($progPostulantes as $idx => $post) {
                $puesto = $idx + 1;
                $apellidos = trim($post->primer_apellido . ' ' . $post->segundo_apellido);
                $nombre = $apellidos . ($apellidos !== '' ? ', ' : '') . ($post->nombres ?? '');
                $puntaje = number_format((float) $post->puntaje, 3, '.', '');
                $out .= sprintf("%6d     %-8s     %s%12s\n", $puesto, $post->dni, $padName($nombre), $puntaje);
            }

            $out .= ' ' . $sep . "\n";
            $out .= ' ' . $now . "\n\n\n\n";
        }

        $filename = 'clasificados_' . $calificacion->id . '_' . date('YmdHis') . '.txt';

        return response($out, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function generarRankingPdf(Request $request): Response|JsonResponse
    {
        $request->validate(['id_calificacion' => 'required|integer|exists:calificaciones,id']);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        $idProceso = $calificacion->id_proceso;
        $idCalificacion = $calificacion->id;

        // Vacantes con programa y etapa
        $vacantes = DB::table('vacantes as v')
            ->join('programa as p', 'v.id_programa', '=', 'p.id')
            ->where('v.id_proceso', $idProceso)
            ->where('v.estado', 1)
            ->select('v.id_programa', 'p.nombre as programa', 'v.vacantes', 'v.etapa')
            ->orderBy('v.etapa')
            ->orderBy('p.nombre')
            ->get();

        if ($vacantes->isEmpty()) {
            return $this->errorResponse('No hay vacantes configuradas', 404);
        }

        // Resultados desde la tabla resultados (asignados por asignarResultados)
        $puntajes = DB::table('resultados as r')
            ->join('inscripciones as i', function ($join) use ($idProceso) {
                $join->on('i.id_postulante', '=', DB::raw('(SELECT id FROM postulante WHERE nro_doc = r.dni_postulante)'))
                     ->where('i.id_proceso', $idProceso);
            })
            ->leftJoin('puntajes as pu', function ($join) use ($idProceso) {
                $join->on('pu.dni', '=', 'r.dni_postulante')
                     ->where('pu.id_proceso', $idProceso);
            })
            ->where('r.id_proceso', $idProceso)
            ->whereNotNull('r.puntaje')
            ->select(
                'r.dni_postulante as dni',
                'r.paterno',
                'r.materno',
                'r.nombres',
                'r.puntaje',
                DB::raw('COALESCE(pu.puntaje_vocacional, 0) as puntaje_vocacional'),
                'i.id_programa as programa',
                'r.puesto',
                'r.apto',
                'r.tipo as examen',
                'r.observacion as obs',
            )
            ->get();

        // Agrupar por programa usando las vacantes
        $programasData = [];
        foreach ($vacantes as $v) {
            $progPostulantes = $puntajes
                ->filter(fn ($p) => (string) $p->programa === (string) $v->id_programa)
                ->sortByDesc(fn ($p) => $p->puntaje + $p->puntaje_vocacional)
                ->values();

            if ($progPostulantes->isEmpty()) continue;

            $postulantes = $progPostulantes->map(function ($post) use ($puntajes, $v) {
                $total = $post->puntaje + $post->puntaje_vocacional;

                // Obs: otros programas donde aparece el mismo DNI
                $otros = $puntajes
                    ->filter(fn ($p) => $p->dni === $post->dni && (string) $p->programa !== (string) $v->id_programa)
                    ->map(fn ($p) => "{$p->puesto}: " . number_format($p->puntaje + $p->puntaje_vocacional, 3))
                    ->implode('  ');

                $post->vocacional = $post->puntaje_vocacional ?? 0;
                $post->obs = $otros ?: ($post->obs ?? '');
                return $post;
            });

            $programasData[] = [
                'nombre' => $v->programa,
                'vacantes' => $v->vacantes,
                'etapa' => $v->etapa,
                'postulantes' => $postulantes,
            ];
        }

        $html = View::make('calificacion::ranking', [
            'calificacion' => $calificacion,
            'programas' => $programasData,
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
            'margin_top' => 38,
            'margin_bottom' => 18,
            'margin_header' => 8,
            'margin_footer' => 8,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Ranking General — ' . ($calificacion->nombre ?? ''));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'ranking_' . $calificacion->id . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function generarFichaPdf(Request $request): Response|JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'dni' => 'required|string',
        ]);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        $idCalificacion = $calificacion->id;

        $postulante = DB::table('postulante')
            ->where('nro_doc', $request->input('dni'))
            ->select('id', 'nro_doc', 'primer_apellido', 'segundo_apellido', 'nombres')
            ->first();

        if (!$postulante) {
            return $this->errorResponse('Postulante no encontrado', 404);
        }

        $html = $this->buildFichaHtml($calificacion, $postulante, $idCalificacion);

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => $tempDir,
        ]);

        $nombreCompleto = trim($postulante->primer_apellido . ' ' . $postulante->segundo_apellido . ' ' . $postulante->nombres);
        $mpdf->SetTitle('Ficha — ' . $nombreCompleto);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = 'ficha_' . $postulante->nro_doc . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function generarFichaPdfMasivo(Request $request): Response|JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'dnis' => 'required|array|min:1|max:500',
            'dnis.*' => 'required|string',
        ]);

        $calificacion = Calificacion::find($request->input('id_calificacion'));
        $idCalificacion = $calificacion->id;
        $dnis = $request->input('dnis');

        $postulantes = DB::table('postulante')
            ->whereIn('nro_doc', $dnis)
            ->select('id', 'nro_doc', 'primer_apellido', 'segundo_apellido', 'nombres')
            ->get()
            ->keyBy('nro_doc');

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 6,
            'margin_right' => 6,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle('Fichas de Calificación — Lote');
        $mpdf->SetAuthor('Sistema de Admisión UNAP');

        $procesados = 0;
        foreach ($dnis as $dni) {
            $postulante = $postulantes->get($dni);
            if (!$postulante) continue;

            $html = $this->buildFichaHtml($calificacion, $postulante, $idCalificacion);

            if ($procesados > 0) {
                $mpdf->AddPage();
            }

            $mpdf->WriteHTML($html);
            $procesados++;
        }

        if ($procesados === 0) {
            return $this->errorResponse('No se encontraron postulantes con IDE', 404);
        }

        $filename = 'fichas_masivo_' . $calificacion->id . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function buildFichaHtml($calificacion, $postulante, int $idCalificacion): string
    {
        // IDE del postulante en esta calificación
        $ide = DB::table('ides as i')
            ->join('archivo_lectura as al', 'al.id', '=', 'i.id_archivo')
            ->where('al.id_calificacion', $idCalificacion)
            ->where('i.dni', $postulante->nro_doc)
            ->where('i.estado', 1)
            ->select('i.litho', 'i.tipo', 'i.aula')
            ->first();

        if (!$ide) {
            return '<p style="text-align:center;padding:40px;">No se encontró IDE para el postulante ' . $postulante->nro_doc . '</p>';
        }

        // RES del postulante
        $res = DB::table('res as r')
            ->join('archivo_lectura as al', 'al.id', '=', 'r.id_archivo')
            ->where('al.id_calificacion', $idCalificacion)
            ->where('r.litho', $ide->litho)
            ->select('r.respuestas', 'r.puntaje')
            ->first();

        // Tipo de examen (clave de respuestas)
        $examenTipo = DB::table('examen_tipos')
            ->where('tipo', $ide->tipo)
            ->whereNotNull('respuestas')
            ->first();

        // Obtener datos de calificación (ponderación y multiplicador usados)
        $calificacionData = DB::table('calificaciones')->where('id', $idCalificacion)->first();
        $idPonderacion = $calificacionData->id_ponderacion ?? null;
        $idMultiplicador = $calificacionData->id_multiplicador ?? null;

        // Ponderaciones expandidas a 60 preguntas
        $pondMap = [];
        if ($idPonderacion) {
            $ponderaciones = DB::select(
                "SELECT numero, ponderacion, cantidad_preguntas FROM ponderacion WHERE id_ponderacion_simulacro = ? ORDER BY numero",
                [$idPonderacion]
            );
            $qIndex = 1;
            foreach ($ponderaciones as $p) {
                $cantidad = (int)($p->cantidad_preguntas ?? 1);
                for ($j = 0; $j < $cantidad; $j++) {
                    $pondMap[$qIndex] = (float)$p->ponderacion;
                    $qIndex++;
                }
            }
        }

        // Multiplicador
        $multiplicador = $idMultiplicador ? DB::table('multiplicadores')->find($idMultiplicador) : null;
        $valCorrecta = $multiplicador?->correcta ?? 10;
        $valIncorrecta = $multiplicador?->incorrecta ?? 0;
        $valBlanco = $multiplicador?->blanco ?? 2;

        // Excepciones
        $excepciones = collect();
        if ($examenTipo) {
            $excepciones = DB::table('excepciones')
                ->where('id_examen_tipo', $examenTipo->id)
                ->get()
                ->keyBy('nro_pregunta');
        }

        // Procesar cada pregunta
        $clave = $examenTipo?->respuestas ?? '';
        $respuestas = $res?->respuestas ?? '';
        $nPreguntas = 60;

        $detalle = [];
        $correctas = 0;
        $incorrectas = 0;
        $blancos = 0;
        $puntajeTotal = 0;

        for ($i = 0; $i < $nPreguntas; $i++) {
            $num = $i + 1;
            $resp = $respuestas[$i] ?? ' ';
            $pat = $clave[$i] ?? '';
            $pond = $pondMap[$num] ?? 0;
            $exc = $excepciones->get($num);

            $estado = '';
            $puntos = 0;

            if ($exc) {
                $puntos = match ($exc->accion) {
                    'todas_validas' => $exc->puntaje,
                    'multiples_validas' => in_array($resp, array_map('trim', explode(',', $exc->claves_validas ?? '')))
                        ? $pond * $valCorrecta
                        : $pond * $valIncorrecta,
                    'anulada' => 0,
                    'asignar_puntaje' => $exc->puntaje,
                    default => 0,
                };
                $estado = 'EXC: ' . $exc->accion;
            } else {
                if ($resp === ' ') {
                    $puntos = $pond * $valBlanco;
                    $blancos++;
                    $estado = 'Blanco';
                } elseif ($resp === $pat) {
                    $puntos = $pond * $valCorrecta;
                    $correctas++;
                    $estado = 'Correcto';
                } else {
                    $puntos = $pond * $valIncorrecta;
                    $incorrectas++;
                    $estado = 'Incorrecto';
                }
            }

            $puntajeTotal += $puntos;
            $detalle[] = [
                'num' => $num,
                'resp' => $resp,
                'clave' => $pat,
                'pond' => $pond,
                'estado' => $estado,
                'puntos' => round($puntos, 7),
            ];
        }

        // Programa del postulante
        $programa = DB::table('inscripciones as ins')
            ->join('programa as p', 'p.id', '=', 'ins.id_programa')
            ->where('ins.id_postulante', $postulante->id)
            ->select('p.nombre as programa')
            ->first();

        return View::make('calificacion::ficha_calificacion', [
            'postulante' => $postulante,
            'programa' => $programa?->programa ?? '',
            'ide' => $ide,
            'res' => $res,
            'examenTipo' => $examenTipo,
            'multiplicador' => $multiplicador,
            'valCorrecta' => $valCorrecta,
            'valIncorrecta' => $valIncorrecta,
            'valBlanco' => $valBlanco,
            'detalle' => $detalle,
            'correctas' => $correctas,
            'incorrectas' => $incorrectas,
            'blancos' => $blancos,
            'puntajeTotal' => min(round($puntajeTotal, 7), 3000),
            'calificacion' => $calificacion,
        ])->render();
    }
}
