<?php

namespace App\Modules\Calificacion\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class PdfReportService
{
    private const TEST_TYPE_COLORS = [
        'P' => '#ff7875','Q' => '#73d13d','R' => '#40a9ff','S' => '#ff9c6e','T' => '#b37feb',
    ];

    public function generateClassroomPdf(int $filterGroupId)
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $classrooms = $this->getClassroomsWithAssignments($filterGroupId);
        if ($classrooms->isEmpty()) {
            throw new \DomainException('No hay distribución para exportar');
        }

        $conflictService = app(ConflictDetectionService::class);
        $conflicts = $conflictService->detectConflicts($filterGroupId);
        $conflictMap = $this->buildConflictMap($conflicts);

        $totalStudents = 0;
        foreach ($classrooms as $c) {
            $totalStudents += $c->assignments->count();
        }

        $html = View::make('calificacion::pdf.grid', [
            'filterGroupId' => $filterGroupId,
            'classrooms' => $classrooms,
            'conflictMap' => $conflictMap,
            'totalStudents' => $totalStudents,
        ])->render();

        return $this->renderPdf($html, 'Distribución de Salones - Grupo #' . $filterGroupId);
    }

    public function generateConflictsPdf(int $filterGroupId, array $toggles = [])
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $showTwins = $toggles['twins'] ?? true;
        $showSameCollege = $toggles['same_college'] ?? true;
        $showSameParents = $toggles['same_parents'] ?? true;

        $conflictService = app(ConflictDetectionService::class);
        $conflicts = $conflictService->detectConflicts($filterGroupId);

        if (!$showTwins) {
            $conflicts['twin_alerts'] = [];
            $conflicts['total_twins'] = 0;
        }
        if (!$showSameCollege) {
            $conflicts['same_college_groups'] = [];
            $conflicts['total_same_college'] = 0;
        }
        if (!$showSameParents) {
            $conflicts['same_parent_alerts'] = [];
            $conflicts['total_same_parents'] = 0;
        }

        if ($conflicts['total_twins'] === 0 && $conflicts['total_same_college'] === 0 && $conflicts['total_same_parents'] === 0) {
            throw new \DomainException('No hay conflictos para reportar con los filtros seleccionados');
        }

        $total = $conflicts['total_twins'] + $conflicts['total_same_college'] + $conflicts['total_same_parents'];

        $html = View::make('calificacion::pdf.conflicts', [
            'filterGroupId' => $filterGroupId,
            'conflicts' => $conflicts,
            'total' => $total,
        ])->render();

        return $this->renderPdf($html, 'Reporte de Conflictos - Grupo #' . $filterGroupId);
    }

    public function generateParametersPdf(int $filterGroupId)
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $stats = $this->getStatistics($filterGroupId);

        $html = View::make('calificacion::pdf.parameters', [
            'filterGroupId' => $filterGroupId,
            'stats' => $stats,
        ])->render();

        return $this->renderPdf($html, 'Parámetros y Estadísticas - Grupo #' . $filterGroupId);
    }

    public function generateAuditLogPdf(int $filterGroupId)
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $events = $this->getAuditLogData($filterGroupId);

        if (empty($events)) {
            throw new \DomainException('No hay historial de cambios para este grupo');
        }

        $totalSwaps = 0;
        foreach ($events as $e) {
            $totalSwaps += count($e['swaps']);
        }

        $html = View::make('calificacion::pdf.audit-log', [
            'filterGroupId' => $filterGroupId,
            'events' => $events,
            'totalSwaps' => $totalSwaps,
        ])->render();

        return $this->renderPdf($html, 'Historial de Cambios - Grupo #' . $filterGroupId);
    }

    public function generateTestTypesPdf(int $filterGroupId)
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $classrooms = $this->getClassroomsWithAssignments($filterGroupId);
        if ($classrooms->isEmpty()) {
            throw new \DomainException('No hay distribución para exportar');
        }

        $html = View::make('calificacion::pdf.test-types', [
            'filterGroupId' => $filterGroupId,
            'classrooms' => $classrooms,
        ])->render();

        return $this->renderPdf($html, 'Tipos de Examen - Grupo #' . $filterGroupId);
    }

    public function generatePadronPdf(int $filterGroupId)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        ini_set('pcre.backtrack_limit', '10000000');

        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $classrooms = $this->getClassroomsWithAssignments($filterGroupId);
        if ($classrooms->isEmpty()) {
            throw new \DomainException('No hay distribución para exportar');
        }

        $idProceso = $filterGroup->id_proceso;
        $fotosBase = public_path("documentos/{$idProceso}/inscripciones/fotos");
        $huellasBase = public_path("documentos/{$idProceso}/inscripciones/huellas");

        // Pre-generar miniaturas para reducir el uso de memoria de mPDF
        $stamp = time();
        $thumbDir = storage_path("app/temp/padron_{$filterGroupId}_{$stamp}");
        $thumbFotosBase = "{$thumbDir}/fotos";
        $thumbHuellasBase = "{$thumbDir}/huellas";
        @mkdir($thumbFotosBase, 0775, true);
        @mkdir($thumbHuellasBase, 0775, true);

        foreach ($classrooms as $c) {
            foreach ($c->assignments as $a) {
                $dni = $a->n_documento;

                $fotoSrc = "{$fotosBase}/{$dni}.jpg";
                $fotoDst = "{$thumbFotosBase}/{$dni}.jpg";
                if (file_exists($fotoSrc) && !file_exists($fotoDst)) {
                    $this->createThumbnail($fotoSrc, $fotoDst, 200, 85);
                }

                $huellaDerSrc = "{$huellasBase}/{$dni}.jpg";
                $huellaDerDst = "{$thumbHuellasBase}/{$dni}.jpg";
                if (file_exists($huellaDerSrc) && !file_exists($huellaDerDst)) {
                    $this->createThumbnail($huellaDerSrc, $huellaDerDst, 200, 85);
                }

                $huellaIzqSrc = "{$huellasBase}/{$dni}x.jpg";
                $huellaIzqDst = "{$thumbHuellasBase}/{$dni}x.jpg";
                if (file_exists($huellaIzqSrc) && !file_exists($huellaIzqDst)) {
                    $this->createThumbnail($huellaIzqSrc, $huellaIzqDst, 200, 85);
                }
            }
        }

        // Crear mPDF una sola vez
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
            'dpi' => 72,
            'img_dpi' => 72,
        ]);

        $mpdf->SetTitle('Padrón de Estudiantes - Grupo #' . $filterGroupId);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');

        // 1. Escribir el esqueleto HTML (styles, header, footer)
        $skeletonHtml = View::make('calificacion::pdf.padron', [
            'filterGroupId' => $filterGroupId,
        ])->render();
        $mpdf->WriteHTML($skeletonHtml);

        // 2. Renderizar cada aula por separado y liberar memoria
        foreach ($classrooms as $c) {
            $chunkHtml = View::make('calificacion::pdf.padron-classroom', [
                'c' => $c,
                'thumbFotosBase' => $thumbFotosBase,
                'thumbHuellasBase' => $thumbHuellasBase,
            ])->render();

            $mpdf->WriteHTML($chunkHtml);

            // Liberar memoria de variables temporales
            unset($chunkHtml);
            gc_collect_cycles();
        }

        // Limpiar miniaturas temporales
        $this->removeDirectory($thumbDir);

        $filename = 'padron_de_estudiantes_grupo_' . $filterGroupId . '_' . date('YmdHis') . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function createThumbnail(string $source, string $dest, int $maxWidth, int $quality): void
    {
        $info = @getimagesize($source);
        if (!$info) {
            copy($source, $dest);
            return;
        }

        [$width, $height] = $info;

        // Si la imagen ya es pequeña, copiar directamente
        if ($width <= $maxWidth) {
            copy($source, $dest);
            return;
        }

        $newHeight = (int) ($height * $maxWidth / $width);

        // Usar imagecreatefromstring para soportar cualquier formato (JPEG, PNG, etc.)
        $src = @imagecreatefromstring(file_get_contents($source));
        if (!$src) {
            copy($source, $dest);
            return;
        }

        $dst = imagecreatetruecolor($maxWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        imagejpeg($dst, $dest, $quality);
        imagedestroy($src);
        imagedestroy($dst);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->removeDirectory($file);
            } else {
                @unlink($file);
            }
        }
        @rmdir($dir);
    }

    public function generateListaPdf(int $filterGroupId, string $orderBy = 'asiento')
    {
        $filterGroup = DB::table('grupos_filtro')->where('id', $filterGroupId)->first();
        if (!$filterGroup) {
            throw new \DomainException('Grupo de filtrado no encontrado');
        }

        $classrooms = $this->getClassroomsWithAssignments($filterGroupId);
        if ($classrooms->isEmpty()) {
            throw new \DomainException('No hay distribución para exportar');
        }

        if ($orderBy === 'alfabetico') {
            foreach ($classrooms as $classroom) {
                $classroom->assignments = $classroom->assignments->sortBy(function ($a) {
                    return $a->paterno . ' ' . $a->materno . ' ' . $a->nombres;
                })->values();
            }
        }

        $html = View::make('calificacion::pdf.lista', [
            'filterGroupId' => $filterGroupId,
            'classrooms' => $classrooms,
            'orderBy' => $orderBy,
        ])->render();

        $ordenLabel = $orderBy === 'alfabetico' ? 'Alfabético' : 'Asiento';
        return $this->renderPdf($html, 'Lista de Estudiantes (' . $ordenLabel . ') - Grupo #' . $filterGroupId);
    }

    // ─── PDF RENDERER ───────────────────────────────────────────────

    private function renderPdf(string $html, string $title): \Illuminate\Http\Response
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
            'margin_top' => 15,
            'margin_bottom' => 15,
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

    // ─── DATA QUERIES ────────────────────────────────────────────────

    private function buildConflictMap(array $conflicts): array
    {
        $map = [];
        foreach ($conflicts['twin_alerts'] ?? [] as $alert) {
            foreach ($alert['students'] as $s) {
                $map[$s['id_postulante']] = 'twin';
            }
        }
        foreach ($conflicts['same_college_groups'] ?? [] as $group) {
            foreach ($group['students'] as $s) {
                if (isset($map[$s['id_postulante']])) continue;
                $map[$s['id_postulante']] = 'same_college';
            }
        }
        foreach ($conflicts['same_parent_alerts'] ?? [] as $alert) {
            foreach ($alert['students'] as $s) {
                if (isset($map[$s['id_postulante']])) continue;
                $map[$s['id_postulante']] = 'same_parent';
            }
        }
        return $map;
    }

    private function getClassroomsWithAssignments(int $filterGroupId)
    {
        $classrooms = DB::table('aulas')
            ->where('grupo_filtro_id', $filterGroupId)
            ->orderBy('nombre')
            ->get();

        foreach ($classrooms as $classroom) {
            $classroom->assignments = DB::table('asignaciones_aulas')
                ->where('aula_id', $classroom->id)
                ->join('postulante', 'asignaciones_aulas.id_postulante', '=', 'postulante.id')
                ->join('inscripciones', function ($join) use ($filterGroupId) {
                    $join->on('postulante.id', '=', 'inscripciones.id_postulante')
                        ->where('inscripciones.id_proceso', '=', function ($query) use ($filterGroupId) {
                            $query->select('id_proceso')->from('grupos_filtro')->where('id', $filterGroupId)->limit(1);
                        })
                        ->where('inscripciones.estado', 0);
                })
                ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
                ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
                ->select([
                    'asignaciones_aulas.posicion', 'asignaciones_aulas.codigo', 'asignaciones_aulas.tipo_examen',
                    'asignaciones_aulas.id_postulante',
                    'postulante.nro_doc as n_documento',
                    'postulante.primer_apellido as paterno',
                    'postulante.segundo_apellido as materno',
                    'postulante.nombres',
                    'programa.nombre as programa_estudios',
                    'areas.nombre as area',
                ])
                ->orderBy('asignaciones_aulas.posicion')
                ->get();
        }

        return $classrooms;
    }

    private function getAuditLogData(int $filterGroupId): array
    {
        $events = DB::table('eventos_redistribucion')
            ->where('grupo_filtro_id', $filterGroupId)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = [];
        foreach ($events as $event) {
            $swaps = DB::table('registros_intercambios')
                ->where('evento_id', $event->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($swaps as $swap) {
                $originPostulante = DB::table('postulante')->where('id', $swap->origen_id_postulante_anterior)->first();
                $destPostulante = DB::table('postulante')->where('id', $swap->destino_id_postulante_anterior)->first();

                $originClassroom = DB::table('aulas')->where('id', $swap->origen_aula_id)->first();
                $destClassroom = DB::table('aulas')->where('id', $swap->destino_aula_id)->first();

                $swap->origin_name = $originPostulante ? ($originPostulante->primer_apellido . ' ' . $originPostulante->segundo_apellido . ', ' . $originPostulante->nombres) : 'N/A';
                $swap->origin_dni = $originPostulante?->nro_doc ?? 'N/A';
                $swap->origin_classroom_name = $originClassroom?->nombre ?? 'N/A';
                $swap->dest_name = $destPostulante ? ($destPostulante->primer_apellido . ' ' . $destPostulante->segundo_apellido . ', ' . $destPostulante->nombres) : 'N/A';
                $swap->dest_dni = $destPostulante?->nro_doc ?? 'N/A';
                $swap->dest_classroom_name = $destClassroom?->nombre ?? 'N/A';
            }

            $result[] = [
                'event' => $event,
                'swaps' => $swaps,
            ];
        }

        return $result;
    }

    private function getStatistics(int $filterGroupId): array
    {
        $totalStudents = DB::table('asignaciones_aulas')
            ->join('aulas', 'asignaciones_aulas.aula_id', '=', 'aulas.id')
            ->where('aulas.grupo_filtro_id', $filterGroupId)
            ->count();

        $totalClassrooms = DB::table('aulas')->where('grupo_filtro_id', $filterGroupId)->count();

        $areaStats = DB::select("
            SELECT a.nombre AS area, COUNT(DISTINCT c.id) as num_classrooms, COUNT(ca.id) as num_students,
                MIN(c.capacidad) as min_capacity, MAX(c.capacidad) as max_capacity,
                ROUND(AVG(c.capacidad), 2) as avg_capacity
            FROM asignaciones_aulas ca
            JOIN aulas c ON ca.aula_id = c.id
            JOIN postulante p ON ca.id_postulante = p.id
            JOIN inscripciones i ON p.id = i.id_postulante
            JOIN programa pr ON i.id_programa = pr.id
            LEFT JOIN areas a ON pr.id_area = a.id
            WHERE c.grupo_filtro_id = ?
            AND i.id_proceso = (SELECT id_proceso FROM grupos_filtro WHERE id = ?)
            AND i.estado = 0
            GROUP BY a.nombre ORDER BY num_students DESC
        ", [$filterGroupId, $filterGroupId]);

        $occupancyStats = DB::select("
            SELECT c.nombre, c.capacidad, COUNT(ca.id) as current_count,
                ROUND(COUNT(ca.id) * 100.0 / c.capacidad, 2) as occupancy_percentage,
                CASE WHEN COUNT(ca.id) > c.capacidad THEN 'Sobrecapacidad'
                     WHEN COUNT(ca.id) = c.capacidad THEN 'Completo'
                     ELSE 'Disponible' END as status
            FROM aulas c
            LEFT JOIN asignaciones_aulas ca ON c.id = ca.aula_id
            WHERE c.grupo_filtro_id = ?
            GROUP BY c.id, c.nombre, c.capacidad ORDER BY c.nombre
        ", [$filterGroupId]);

        return [
            'total_students' => $totalStudents,
            'total_classrooms' => $totalClassrooms,
            'area_stats' => $areaStats,
            'occupancy_stats' => $occupancyStats,
        ];
    }
}
