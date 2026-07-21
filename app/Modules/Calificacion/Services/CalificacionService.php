<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\Multiplicador;
use App\Modules\Calificacion\Models\Resp;
use App\Modules\Calificacion\Models\Ide;
use App\Modules\Calificacion\Exports\ResultadosExport;
use App\Modules\Calificacion\Exports\PostulantesCalificacionExport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalificacionService
{
    public function calificar(int $idCalificacion, int $idExamen, int $idPonderacion, int $idMultiplicador): array
    {
        $multiplicador = Multiplicador::find($idMultiplicador);
        if (!$multiplicador) {
            throw new \DomainException('Multiplicador no encontrado');
        }

        $valCorrecta = $multiplicador->correcta;
        $valIncorrecta = $multiplicador->incorrecta;
        $valBlanco = $multiplicador->blanco;

        $ponderaciones = DB::select(
            "SELECT numero, ponderacion, cantidad_preguntas FROM ponderacion WHERE id_ponderacion_simulacro = ? ORDER BY numero",
            [$idPonderacion]
        );

        // Expandir por cantidad_preguntas a las 60 preguntas reales
        // Cada fila tiene un numero (asignatura), ponderacion y cantidad_preguntas
        $pondMap = [];
        $qIndex = 1;
        foreach ($ponderaciones as $p) {
            $cantidad = (int)($p->cantidad_preguntas ?? 1);
            for ($j = 0; $j < $cantidad; $j++) {
                $pondMap[$qIndex] = (float)$p->ponderacion;
                $qIndex++;
            }
        }

        // Tipos de examen (PQRST) con respuestas clave, filtrados por el examen seleccionado
        $tipos = DB::table('examen_tipos')
            ->where('id_examen_simulacro', $idExamen)
            ->whereNotNull('respuestas')
            ->get()
            ->keyBy('tipo');

        // Área del examen para filtrar postulantes (id_area coincide con el id del examen)
        $examen = DB::table('examen_simulacro')->where('id', $idExamen)->first();
        $idArea = $examen?->id_area ?? $idExamen;

        // IDE y RES de la calificación, vinculados por litho, filtrados por área
        $areaFilter = '';
        $params = [$idCalificacion, $idCalificacion];

        if ($idArea) {
            $areaFilter = " AND i.dni IN (
                SELECT p.nro_doc
                FROM postulante p
                JOIN inscripciones ins ON ins.id_postulante = p.id
                JOIN programa pr ON ins.id_programa = pr.id
                WHERE pr.id_area = ?
            )";
            $params[] = $idArea;
        }

        $respuestas = DB::select(
            "SELECT re.id, re.litho, re.respuestas, i.tipo, i.dni, i.aula
            FROM res re
            JOIN archivo_lectura al ON al.id = re.id_archivo AND al.id_calificacion = ?
            JOIN ides i ON i.litho = re.litho
            JOIN archivo_lectura ali ON ali.id = i.id_archivo AND ali.id_calificacion = ?
            WHERE i.estado = 1
            {$areaFilter}
            ORDER BY re.id",
            $params
        );

        $comparaciones = [];
        $excCache = [];
        foreach ($respuestas as $line) {
            if (!$line->dni || !$line->tipo) continue;

            $tipoRow = $tipos->get($line->tipo);
            if (!$tipoRow) continue;

            $clave = $tipoRow->respuestas;
            $idExamenTipo = $tipoRow->id;

            $puntaje = $this->calcularPuntaje(
                $line, $clave, $pondMap, $excCache, $idExamenTipo,
                $valCorrecta, $valIncorrecta, $valBlanco
            );

            Resp::where('id', $line->id)->update([
                'puntaje' => round($puntaje, 3),
                'calificado' => 1,
            ]);

            $comparaciones[] = [
                'id' => $line->id,
                'tipo' => $line->tipo,
                'puntaje' => round($puntaje, 3),
            ];
        }

        // Asignar puestos, ingresantes y clasificados
        $this->asignarResultados($idCalificacion, $idExamen);

        return $comparaciones;
    }

    /**
     * Asigna puesto, puesto_general y apto (ING/CL) a los postulantes
     * basándose en las vacantes por programa y etapa.
     */
    public function asignarResultados(int $idCalificacion, int $idExamen): void
    {
        $calificacion = DB::table('calificaciones')->where('id', $idCalificacion)->first();
        if (!$calificacion) return;

        $idProceso = $calificacion->id_proceso;

        $examen = DB::table('examen_simulacro')->where('id', $idExamen)->first();
        $idArea = $examen?->id_area ?? $idExamen;

        // Vacantes del proceso agrupadas por programa
        $vacantes = DB::table('vacantes')
            ->where('id_proceso', $idProceso)
            ->select('id_programa', 'vacantes', 'etapa')
            ->get();

        $vacantesPorPrograma = [];
        foreach ($vacantes as $v) {
            $vacantesPorPrograma[$v->id_programa] = [
                'vacantes' => (int)$v->vacantes,
                'etapa' => $v->etapa,
            ];
        }

        // Postulantes calificados del área, con su programa y puntaje
        $postulantes = DB::table('grupos_filtro as gf')
            ->join('inscripciones as ins', 'ins.grupo_filtro_id', '=', 'gf.id')
            ->join('postulante as p', 'ins.id_postulante', '=', 'p.id')
            ->join('programa as pr', 'ins.id_programa', '=', 'pr.id')
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
            ->where('pr.id_area', $idArea)
            ->whereNotNull('r.puntaje')
            ->select(
                'p.nro_doc as dni',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.nombres',
                'ins.id_programa',
                'r.puntaje',
                'r.litho',
                'i.aula',
                'r.respuestas',
                'i.tipo',
                'r.n_lectura as numlectura',
            )
            ->distinct()
            ->orderBy('r.puntaje', 'DESC')
            ->get();

        // Agrupar por programa y asignar puestos
        $porPrograma = [];
        foreach ($postulantes as $post) {
            $idPrograma = $post->id_programa;
            if (!isset($porPrograma[$idPrograma])) {
                $porPrograma[$idPrograma] = [];
            }
            $porPrograma[$idPrograma][] = $post;
        }

        // Para puesto_general, flatten ordenado por puntaje
        $todosOrdenados = $postulantes->sortByDesc('puntaje')->values();

        foreach ($porPrograma as $idPrograma => $lista) {
            $vacInfo = $vacantesPorPrograma[$idPrograma] ?? null;
            $etapa = $vacInfo['etapa'] ?? null;
            $numVacantes = $vacInfo['vacantes'] ?? 0;

            $totalPrograma = count($lista);

            if ($etapa === '1' || $etapa === 1) {
                // Etapa 1: top N = vacantes → SI (Ingresante)
                $limiteIng = $numVacantes;
                foreach ($lista as $idx => $post) {
                    $puesto = $idx + 1;
                    $puestoGeneral = $todosOrdenados->search(fn($r) => $r->dni === $post->dni) + 1;
                    $apto = ($idx < $limiteIng) ? 'SI' : 'NO';

                    $this->upsertResultado($post, $idProceso, $puesto, $puestoGeneral, $apto);
                }
            } elseif ($etapa === '2' || $etapa === 2) {
                // Etapa 2: 20% en exceso del total → CL (Clasificado)
                $limiteCl = (int) ceil($totalPrograma * 0.20);
                foreach ($lista as $idx => $post) {
                    $puesto = $idx + 1;
                    $puestoGeneral = $todosOrdenados->search(fn($r) => $r->dni === $post->dni) + 1;
                    $apto = ($idx < $limiteCl) ? 'CL' : 'NO';

                    $this->upsertResultado($post, $idProceso, $puesto, $puestoGeneral, $apto);
                }
            } else {
                // Sin etapa definida: solo asignar puesto
                foreach ($lista as $idx => $post) {
                    $puesto = $idx + 1;
                    $puestoGeneral = $todosOrdenados->search(fn($r) => $r->dni === $post->dni) + 1;

                    $this->upsertResultado($post, $idProceso, $puesto, $puestoGeneral, 'NO');
                }
            }
        }
    }

    private function upsertResultado($post, int $idProceso, int $puesto, int $puestoGeneral, ?string $apto): void
    {
        DB::table('resultados')->updateOrInsert(
            ['dni_postulante' => $post->dni, 'id_proceso' => $idProceso],
            [
                'paterno' => $post->primer_apellido,
                'materno' => $post->segundo_apellido,
                'nombres' => $post->nombres,
                'puntaje' => min($post->puntaje, 3000),
                'apto' => $apto,
                'puesto' => $puesto,
                'puesto_general' => $puestoGeneral,
                'litho' => $post->litho,
                'aula' => $post->aula,
                'respuestas' => $post->respuestas,
                'tipo' => $post->tipo,
                'numlectura' => $post->numlectura,
                'calificado' => 1,
                'fecha' => date('Y-m-d'),
            ]
        );
    }

    public function getSelectPuestos(int $idProceso): array
    {
        $puestos = DB::table('participantes')
            ->select('puesto as label', 'puesto as value')
            ->where('id_proceso', $idProceso)
            ->distinct()
            ->orderBy('puesto')
            ->get();

        $codigos = DB::table('participantes')
            ->select('cod_puesto as label', 'cod_puesto as value')
            ->where('id_proceso', $idProceso)
            ->distinct()
            ->orderBy('cod_puesto')
            ->get();

        $unidades = DB::table('participantes')
            ->select('cod_examen as label', 'cod_examen as value')
            ->where('id_proceso', $idProceso)
            ->groupBy('cod_examen')
            ->orderBy('cod_examen')
            ->get();

        return [
            'puestos' => $puestos,
            'codigos_puesto' => $codigos,
            'codigos_examen' => $unidades,
        ];
    }

    public function getPostulantesCalificacion(int $idCalificacion, ?int $idExamen = null): array
    {
        // Resolver id_area desde el examen seleccionado (id_area coincide con el id del examen)
        $idArea = null;
        if ($idExamen) {
            $examen = DB::table('examen_simulacro')->where('id', $idExamen)->first();
            $idArea = $examen?->id_area ?? $idExamen;
        }

        $query = DB::table('grupos_filtro as gf')
            ->join('inscripciones', 'inscripciones.grupo_filtro_id', '=', 'gf.id')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->join('areas', 'programa.id_area', '=', 'areas.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->leftJoin('asignaciones_aulas as aa', function ($join) {
                $join->on('aa.id_postulante', '=', 'inscripciones.id_postulante')
                     ->on('aa.grupo_filtro_id', '=', 'gf.id');
            })
            ->leftJoin('aulas', 'aa.aula_id', '=', 'aulas.id')
            ->leftJoin('ides as i', function ($join) use ($idCalificacion) {
                $join->on('i.dni', '=', 'postulante.nro_doc')
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
            ->leftJoin('resultados as res', function ($join) {
                $join->on('res.dni_postulante', '=', 'postulante.nro_doc');
            })
            ->where('gf.id_calificacion', $idCalificacion);

        if ($idArea) {
            $query->where('programa.id_area', $idArea);
        }

        $postulantes = $query->select(
                'inscripciones.id as id_inscripcion',
                'postulante.nro_doc as dni',
                'postulante.primer_apellido',
                'postulante.segundo_apellido',
                'postulante.nombres',
                'programa.nombre as programa',
                'areas.nombre as area',
                'modalidad.nombre as modalidad',
                'i.litho',
                'aulas.nro as aula',
                DB::raw('LEAST(r.puntaje, 3000) as puntaje'),
                'res.puesto',
                'res.puesto_general',
                'res.apto',
                DB::raw('CASE WHEN i.id IS NOT NULL THEN 1 ELSE 0 END as tiene_ide'),
                DB::raw('CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END as tiene_res'),
                DB::raw('CASE WHEN r.calificado = 1 THEN 1 ELSE 0 END as calificado')
            )
            ->distinct()
            ->orderBy('postulante.primer_apellido')
            ->orderBy('postulante.segundo_apellido')
            ->get();

        return [
            'total' => $postulantes->count(),
            'calificados' => $postulantes->where('calificado', 1)->count(),
            'pendientes' => $postulantes->where('calificado', 0)->count(),
            'data' => $postulantes,
        ];
    }

    public function descargarExcelPostulantes(int $idCalificacion, ?int $idExamen = null): BinaryFileResponse
    {
        // Resolver id_area desde el examen seleccionado (id_area coincide con el id del examen)
        $idArea = null;
        if ($idExamen) {
            $examen = DB::table('examen_simulacro')->where('id', $idExamen)->first();
            $idArea = $examen?->id_area ?? $idExamen;
        }

        $idProceso = DB::table('calificaciones')->where('id', $idCalificacion)->value('id_proceso');

        $query = DB::table('grupos_filtro as gf')
            ->join('inscripciones', 'inscripciones.grupo_filtro_id', '=', 'gf.id')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->join('areas', 'programa.id_area', '=', 'areas.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->leftJoin('puntajes', function ($join) use ($idProceso) {
                $join->on('puntajes.dni', '=', 'postulante.nro_doc')
                     ->where('puntajes.id_proceso', '=', $idProceso);
            })
            ->leftJoin('resultados as res', function ($join) use ($idProceso) {
                $join->on('res.dni_postulante', '=', 'postulante.nro_doc')
                     ->where('res.id_proceso', '=', $idProceso);
            })
            ->leftJoin('ides as i', function ($join) use ($idCalificacion) {
                $join->on('i.dni', '=', 'postulante.nro_doc')
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
            ->where('gf.id_calificacion', $idCalificacion);

        if ($idArea) {
            $query->where('programa.id_area', $idArea);
        }

        $postulantes = $query->select(
                'postulante.nro_doc as dni',
                'postulante.primer_apellido as paterno',
                'postulante.segundo_apellido as materno',
                'postulante.nombres',
                DB::raw('LEAST(r.puntaje, 3000) as puntaje'),
                DB::raw('COALESCE(puntajes.puntaje_vocacional, 0) as vocacional'),
                DB::raw("COALESCE(res.apto, '') as apto"),
                DB::raw("COALESCE(res.observacion, '') as obs"),
                'programa.nombre as desprograma',
                'programa.id_area as idexamen',
                'i.litho',
                'r.n_lectura as numlectura',
                'i.tipo',
                DB::raw("CASE WHEN r.calificado = 1 THEN 'SI' ELSE 'NO' END as calificar"),
                'i.aula',
                'r.respuestas',
                DB::raw('COALESCE(res.puesto, 0) as puesto'),
                DB::raw('COALESCE(res.puesto_general, 0) as puesto_general')
            )
            ->distinct()
            ->orderBy('postulante.primer_apellido')
            ->orderBy('postulante.segundo_apellido')
            ->get();

        return Excel::download(new PostulantesCalificacionExport($postulantes), 'postulantes_calificacion.xlsx');
    }

    public function descargarExcel(int $idProceso): BinaryFileResponse
    {
        $data = DB::select(
            "SELECT DISTINCT participantes.*, r.litho_res, r.lectura_res, r.respuestas, r.puntaje
            FROM (
                SELECT par.dni, par.paterno, par.materno, par.nombres, par.cod_puesto,
                    par.puesto, par.unidad, ide.aula, ide.litho, ide.camp2 AS ide_lectura, par.cod_examen
                FROM participantes par
                LEFT JOIN ides ide ON ide.dni = par.dni
                WHERE par.id_proceso = ?
            ) participantes
            LEFT JOIN (
                SELECT litho, MAX(n_lectura) AS lectura_res, MAX(respuestas) AS respuestas,
                    MAX(IF(puntaje <= 0 OR puntaje IS NULL, 0, puntaje)) AS puntaje,
                    MAX(litho) AS litho_res
                FROM res GROUP BY litho
            ) r ON r.litho = participantes.litho",
            [$idProceso]
        );

        return Excel::download(new ResultadosExport($data), 'reporte.xlsx');
    }

    public function getResultados(int $idProceso): array
    {
        $proceso = DB::table('simulacro')->where('id', $idProceso)->first();

        $areas = DB::table('participantes')
            ->select('cod_examen')
            ->where('id_proceso', $idProceso)
            ->distinct()
            ->orderBy('cod_examen')
            ->pluck('cod_examen');

        $datos = [];
        foreach ($areas as $area) {
            $estudiantes = DB::select(
                "SELECT DISTINCT p.dni, p.paterno, p.materno, p.nombres,
                    COALESCE(c.nombre, '') as colegio,
                    COALESCE(r.puntaje, 0) as puntaje
                FROM participantes p
                LEFT JOIN ides i ON i.dni = p.dni
                LEFT JOIN res r ON r.litho = i.litho
                LEFT JOIN colegios c ON c.id_colegio = p.id_colegio
                WHERE p.id_proceso = ? AND p.cod_examen = ?
                ORDER BY puntaje DESC",
                [$idProceso, $area]
            );

            $datos[] = [
                'unidad' => $area,
                'data' => array_map(fn($e) => [
                    'dni' => $e->dni,
                    'paterno' => $e->paterno,
                    'materno' => $e->materno,
                    'nombres' => $e->nombres,
                    'colegio' => $e->colegio,
                    'puntaje' => $e->puntaje,
                ], $estudiantes),
            ];
        }

        return [
            'proceso' => $proceso,
            'datos' => $datos,
        ];
    }

    public function generateErroresPdf(int $idProceso): Response
    {
        $proceso = DB::table('simulacro')->where('id', $idProceso)->first();
        if (!$proceso) {
            throw new \DomainException('Proceso no encontrado');
        }

        $duplicadosDni = DB::select(
            "SELECT a.nombre as archivo, i.n_lectura as lectura, i.litho, i.dni
            FROM ides i
            JOIN archivos_simulacro a ON a.id = i.id_archivo
            WHERE a.id_simulacro = ?
            AND i.dni IN (
                SELECT dni FROM ides
                JOIN archivos_simulacro ON archivos_simulacro.id = ides.id_archivo
                WHERE archivos_simulacro.id_simulacro = ?
                GROUP BY dni HAVING COUNT(*) > 1
            )
            ORDER BY i.dni",
            [$idProceso, $idProceso]
        );

        $errores = DB::select(
            "SELECT a.nombre as archivo, i.n_lectura as lectura, i.litho, i.dni, i.tipo, i.aula,
                CASE WHEN i.aula IS NOT NULL AND i.aula REGEXP '^[0-9]+$' THEN 1 ELSE 0 END as vaula,
                CASE WHEN i.dni IS NOT NULL AND LENGTH(i.dni) = 8 AND i.dni REGEXP '^[0-9]+$' THEN 1 ELSE 0 END as vdni,
                LENGTH(i.dni) as len_doc,
                CASE WHEN i.litho IS NOT NULL AND LENGTH(i.litho) = 6 THEN 1 ELSE 0 END as vlitho,
                CASE WHEN i.dni IS NOT NULL THEN (SELECT 1 FROM participantes p WHERE p.dni = i.dni AND p.id_proceso = ? LIMIT 1) ELSE NULL END as dnip
            FROM ides i
            JOIN archivos_simulacro a ON a.id = i.id_archivo
            WHERE a.id_simulacro = ?
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
            [$idProceso, $idProceso, $idProceso]
        );

        $html = View::make('calificacion::errores', [
            'proceso' => $proceso,
            'duplicados_dni' => $duplicadosDni,
            'errores' => $errores,
        ])->render();

        return $this->renderPdf($html, 'Errores - ' . $proceso->nombre);
    }

    public function generateResultadosPdf(int $idProceso): Response
    {
        $resultados = $this->getResultados($idProceso);

        if (empty($resultados['datos'])) {
            throw new \DomainException('No hay resultados para mostrar');
        }

        $html = View::make('calificacion::resultados', [
            'convocatoria' => $resultados['proceso'],
            'datos' => $resultados['datos'],
        ])->render();

        return $this->renderPdf($html, 'Resultados - ' . ($resultados['proceso']->nombre ?? ''));
    }

    private function renderPdf(string $html, string $title): Response
    {
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

        $mpdf->SetTitle($title);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function calcularPuntaje(
        $line,
        string $clave,
        array $ponderaciones,
        array &$excCache,
        ?int $idExamenTipo,
        $valCorrecta,
        $valIncorrecta,
        $valBlanco
    ): float {
        $puntaje = 0;
        $nPreguntas = 60;

        if ($idExamenTipo) {
            if (!isset($excCache[$idExamenTipo])) {
                $excCache[$idExamenTipo] = DB::table('excepciones')
                    ->where('id_examen_tipo', $idExamenTipo)
                    ->get()
                    ->keyBy('nro_pregunta');
            }
            $excepciones = $excCache[$idExamenTipo];
        } else {
            $excepciones = collect();
        }

        for ($i = 0; $i < $nPreguntas; $i++) {
            if (!isset($line->respuestas[$i]) || !isset($clave[$i])) continue;

            $resp = $line->respuestas[$i];
            $pat = $clave[$i];
            $excepcion = $excepciones->get($i + 1);

            if ($excepcion) {
                $puntaje += match ($excepcion->accion) {
                    'todas_validas' => $excepcion->puntaje,
                    'multiples_validas' => in_array($resp, array_map('trim', explode(',', $excepcion->claves_validas ?? '')))
                        ? ($ponderaciones[$i + 1] ?? 0) * $valCorrecta
                        : ($ponderaciones[$i + 1] ?? 0) * $valIncorrecta,
                    'anulada' => 0,
                    'asignar_puntaje' => $excepcion->puntaje,
                    default => 0,
                };
            } else {
                if ($resp === ' ') {
                    $puntaje += ($ponderaciones[$i + 1] ?? 0) * $valBlanco;
                } elseif ($resp === $pat) {
                    $puntaje += ($ponderaciones[$i + 1] ?? 0) * $valCorrecta;
                } else {
                    $puntaje += ($ponderaciones[$i + 1] ?? 0) * $valIncorrecta;
                }
            }
        }

        return $puntaje;
    }
}
