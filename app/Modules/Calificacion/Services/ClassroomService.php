<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\Classroom;
use App\Modules\Calificacion\Models\ClassroomAssignment;
use App\Modules\Calificacion\Models\RedistributionEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassroomService
{
    private const TEST_PATTERN = [
        ['P', 'S', 'Q', 'T', 'R'],
        ['Q', 'T', 'R', 'P', 'S'],
        ['R', 'P', 'S', 'Q', 'T'],
        ['S', 'Q', 'T', 'R', 'P'],
        ['T', 'R', 'P', 'S', 'Q'],
        ['P', 'S', 'Q', 'T', 'R'],
        ['Q', 'T', 'R', 'P', 'S'],
        ['R', 'P', 'S', 'Q', 'T'],
    ];

    public function generateDistribution(int $filterGroupId, int $defaultCapacity = 40, array $capacityExceptions = []): array
    {
        $existingClassrooms = Classroom::where('grupo_filtro_id', $filterGroupId)->count();

        if ($existingClassrooms > 0) {
            throw new \DomainException('Ya existe una distribución para este grupo. Use redistribuir para crear una nueva.');
        }

        $codesByArea = $this->getCodesByArea($filterGroupId);

        if ($codesByArea->isEmpty()) {
            throw new \DomainException('No se encontraron códigos para este grupo de filtrado');
        }

        DB::beginTransaction();
        $startTime = microtime(true);

        try {
            [$totalStudents, $areaStats] = $this->calculateAreaStats($codesByArea);

            $event = RedistributionEvent::create([
                'grupo_filtro_id' => $filterGroupId,
                'usuario_id' => null,
                'tipo' => 'initial',
                'postulantes_count' => $totalStudents,
                'aulas_count' => 0,
                'capacidad_por_aula' => $defaultCapacity,
                'descripcion' => "Distribución inicial agrupada por área: $totalStudents estudiantes",
                'metadata' => [
                    'algorithm' => 'grouped_by_area',
                    'capacity_exceptions' => $capacityExceptions,
                    'area_stats' => $areaStats,
                ],
            ]);

            [$totalClassroomsCreated, $insertAssignments] = $this->processAreas(
                $codesByArea,
                $filterGroupId,
                $defaultCapacity,
                $capacityExceptions
            );

            foreach (array_chunk($insertAssignments, 500) as $chunk) {
                ClassroomAssignment::insert($chunk);
            }

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            $event->update([
                'aulas_count' => $totalClassroomsCreated,
                'metadata' => array_merge($event->metadata ?? [], [
                    'execution_time_ms' => $executionTime,
                    'total_classrooms' => $totalClassroomsCreated,
                    'area_distribution' => $areaStats,
                ]),
            ]);

            DB::commit();

            return [
                'evento_id' => $event->id,
                'num_classrooms' => $totalClassroomsCreated,
                'num_assignments' => count($insertAssignments),
                'execution_time_ms' => $executionTime,
                'area_stats' => $areaStats,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getDistribution(int $filterGroupId): array
    {
        $classrooms = Classroom::where('grupo_filtro_id', $filterGroupId)
            ->with(['assignments' => fn($q) => $q->orderBy('posicion')])
            ->orderBy('nombre')
            ->get();

        if ($classrooms->isEmpty()) {
            return ['classrooms' => []];
        }

        $distribution = [];

        foreach ($classrooms as $classroom) {
            $assignments = $this->getClassroomAssignmentsWithDetails($classroom->id, $filterGroupId);
            $grid = $this->buildGrid($assignments);

            $distribution[] = [
                'id' => $classroom->id,
                'nombre' => $classroom->nombre,
                'capacidad' => $classroom->capacidad,
                'contador_actual' => count($assignments),
                'assignments' => $assignments,
                'grid' => $grid,
            ];
        }

        return ['classrooms' => $distribution];
    }

    public function deleteDistribution(int $filterGroupId): array
    {
        DB::beginTransaction();

        $event = RedistributionEvent::create([
            'grupo_filtro_id' => $filterGroupId,
            'usuario_id' => null,
            'tipo' => 'aleatoria',
            'descripcion' => 'Eliminación de distribución existente',
            'postulantes_count' => 0,
            'aulas_count' => 0,
        ]);

        $classroomIds = Classroom::where('grupo_filtro_id', $filterGroupId)->pluck('id')->toArray();

        if (!empty($classroomIds)) {
            ClassroomAssignment::whereIn('aula_id', $classroomIds)->delete();
            Classroom::where('grupo_filtro_id', $filterGroupId)->delete();
        }

        DB::commit();

        return ['evento_id' => $event->id];
    }

    private function getCodesByArea(int $filterGroupId)
    {
        return DB::table('inscripciones')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->where('inscripciones.grupo_filtro_id', $filterGroupId)
            ->where('inscripciones.estado', 0)
            ->whereNotNull('inscripciones.codigo_distribucion')
            ->select([
                'inscripciones.id_postulante',
                'inscripciones.codigo_distribucion',
                'inscripciones.id_modalidad',
                'areas.id as id_area',
                'areas.nombre as area',
                'areas.codigo as area_codigo',
                'areas.numero_base as area_numero_base',
            ])
            ->get()
            ->groupBy('id_area');
    }

    private function calculateAreaStats($codesByArea): array
    {
        $totalStudents = 0;
        $areaStats = [];
        $defaultCapacity = 40;

        foreach ($codesByArea as $areaId => $codes) {
            $count = $codes->count();
            $totalStudents += $count;
            $areaStats[] = [
                'id_area' => $areaId,
                'area' => $codes->first()->area ?? 'Sin área',
                'count' => $count,
                'classrooms' => ceil($count / $defaultCapacity),
            ];
        }

        return [$totalStudents, $areaStats];
    }

    private function processAreas($codesByArea, int $filterGroupId, int $defaultCapacity, array $capacityExceptions): array
    {
        $insertAssignments = [];
        $globalClassroomNum = 1;
        $totalClassroomsCreated = 0;

        foreach ($codesByArea as $areaId => $codes) {
            $areaName = $codes->first()->area ?? 'Sin área';
            $areaCodigo = $codes->first()->area_codigo;
            $areaNumeroBase = $codes->first()->area_numero_base;

            // Determinar prefijo y número inicial para el área
            if ($areaCodigo) {
                $prefix = ucfirst(strtolower($areaCodigo)); // BIO → Bio, ING → Ing, SOC → Soc
                $startNumber = $this->getNextClassroomNumber($prefix, $areaNumeroBase ?? 1);
            } else {
                $prefix = 'Gen';
                $startNumber = $this->getNextClassroomNumber($prefix, 1);
            }

            $shuffledCodes = $codes->shuffle()->values();
            $numStudents = $shuffledCodes->count();
            $numClassroomsForArea = ceil($numStudents / $defaultCapacity);

            Log::info("Procesando área: $areaName", [
                'estudiantes' => $numStudents,
                'salones_necesarios' => $numClassroomsForArea,
                'prefijo' => $prefix,
                'numero_inicio' => $startNumber,
            ]);

            $studentIndex = 0;
            $areaClassroomNumber = $startNumber;

            for ($localClassroomNum = 1; $localClassroomNum <= $numClassroomsForArea; $localClassroomNum++) {
                $capacity = $defaultCapacity;
                foreach ($capacityExceptions as $exception) {
                    if ($exception['classroom_num'] == $globalClassroomNum) {
                        $capacity = $exception['capacity'];
                        break;
                    }
                }

                $classroomName = "{$prefix}_{$areaClassroomNumber}";

                $classroom = Classroom::create([
                    'grupo_filtro_id' => $filterGroupId,
                    'nombre' => $classroomName,
                    'capacidad' => $capacity,
                    'contador_actual' => 0,
                ]);

                $assignmentsInThisClassroom = 0;

                for ($col = 0; $col < 5; $col++) {
                    for ($row = 0; $row < 8; $row++) {
                        if ($studentIndex >= $numStudents) break 2;

                        $position = ($col * 8) + ($row + 1);

                        if ($position > $capacity) break;

                        $student = $shuffledCodes[$studentIndex];

                        $insertAssignments[] = [
                            'aula_id' => $classroom->id,
                            'grupo_filtro_id' => $filterGroupId,
                            'id_postulante' => $student->id_postulante,
                            'codigo' => $student->codigo_distribucion,
                            'posicion' => $position,
                            'tipo_examen' => self::TEST_PATTERN[$row][$col],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $assignmentsInThisClassroom++;
                        $studentIndex++;
                    }
                }

                $classroom->update(['contador_actual' => $assignmentsInThisClassroom]);
                $globalClassroomNum++;
                $areaClassroomNumber++;
                $totalClassroomsCreated++;
            }
        }

        return [$totalClassroomsCreated, $insertAssignments];
    }

    /**
     * Obtiene el siguiente número disponible para un aula basado en el prefijo del área.
     * Busca todas las aulas existentes con el mismo prefijo (ej. Bio_101, Bio_102)
     * y devuelve el siguiente número disponible.
     */
    private function getNextClassroomNumber(string $prefix, int $baseNumber): int
    {
        $existingNames = DB::table('aulas')
            ->where('nombre', 'LIKE', $prefix . '_%')
            ->pluck('nombre');

        $maxNumber = $baseNumber - 1;

        foreach ($existingNames as $name) {
            // Extraer el número de "Bio_101" → 101
            $parts = explode('_', $name);
            if (count($parts) === 2 && $parts[0] === $prefix) {
                $num = (int) $parts[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $maxNumber + 1;
    }

    private function getClassroomAssignmentsWithDetails(int $classroomId, int $filterGroupId): array
    {
        return ClassroomAssignment::where('aula_id', $classroomId)
            ->join('postulante', 'asignaciones_aulas.id_postulante', '=', 'postulante.id')
            ->leftJoin('colegios', 'postulante.id_colegio', '=', 'colegios.id')
            ->join('inscripciones', function ($join) use ($filterGroupId) {
                $join->on('postulante.id', '=', 'inscripciones.id_postulante')
                    ->where('inscripciones.id_proceso', '=', function ($query) use ($filterGroupId) {
                        $query->select('id_proceso')
                            ->from('grupos_filtro')
                            ->where('id', $filterGroupId)
                            ->limit(1);
                    })
                    ->where('inscripciones.estado', 0);
            })
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->select([
                'asignaciones_aulas.posicion',
                'asignaciones_aulas.id_postulante',
                'asignaciones_aulas.codigo',
                'asignaciones_aulas.tipo_examen',
                'postulante.nro_doc as n_documento',
                'postulante.primer_apellido as paterno',
                'postulante.segundo_apellido as materno',
                'postulante.nombres',
                'postulante.anio_egreso as egreso',
                'postulante.id_colegio',
                'colegios.cod_modular',
                'programa.nombre as programa_estudios',
                'modalidad.nombre as modalidad',
            ])
            ->orderBy('asignaciones_aulas.posicion')
            ->get()
            ->toArray();
    }

    private function buildGrid(array $assignments): array
    {
        $grid = array_fill(0, 8, array_fill(0, 5, null));

        foreach ($assignments as $assignment) {
            $col = floor(($assignment['posicion'] - 1) / 8);
            $row = ($assignment['posicion'] - 1) % 8;

            if ($col < 5 && $row < 8) {
                $grid[$row][$col] = is_array($assignment) ? $assignment : (array) $assignment;
            }
        }

        return $grid;
    }
}
