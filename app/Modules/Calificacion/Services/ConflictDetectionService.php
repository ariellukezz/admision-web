<?php

namespace App\Modules\Calificacion\Services;

use Illuminate\Support\Facades\DB;

class ConflictDetectionService
{
    public function detectConflicts(int $filterGroupId): array
    {
        $students = $this->getStudentsWithDetails($filterGroupId);

        if ($students->isEmpty()) {
            return [
                'same_college_groups' => [],
                'twin_alerts' => [],
                'same_parent_alerts' => [],
                'total_same_college' => 0,
                'total_twins' => 0,
                'total_same_parents' => 0,
            ];
        }

        $sameCollegeGroups = $this->detectSameCollege($students);
        $twinAlerts = $this->detectTwins($students);
        $sameParentAlerts = $this->detectSameParents($students);

        return [
            'same_college_groups' => $sameCollegeGroups,
            'twin_alerts' => $twinAlerts,
            'same_parent_alerts' => $sameParentAlerts,
            'total_same_college' => count($sameCollegeGroups),
            'total_twins' => count($twinAlerts),
            'total_same_parents' => count($sameParentAlerts),
        ];
    }

    private function getStudentsWithDetails(int $filterGroupId)
    {
        return DB::table('asignaciones_aulas as ca')
            ->join('aulas as c', 'ca.aula_id', '=', 'c.id')
            ->join('postulante as p', 'ca.id_postulante', '=', 'p.id')
            ->leftJoin('colegios as col', 'p.id_colegio', '=', 'col.id')
            ->join('inscripciones as i', function ($join) use ($filterGroupId) {
                $join->on('p.id', '=', 'i.id_postulante')
                    ->where('i.id_proceso', '=', function ($query) use ($filterGroupId) {
                        $query->select('id_proceso')
                            ->from('grupos_filtro')
                            ->where('id', $filterGroupId)
                            ->limit(1);
                    })
                    ->where('i.estado', 0);
            })
            ->join('programa as prog', 'i.id_programa', '=', 'prog.id')
            ->join('modalidad as mod', 'i.id_modalidad', '=', 'mod.id')
            ->where('c.grupo_filtro_id', $filterGroupId)
            ->select([
                'ca.id_postulante', 'ca.codigo', 'ca.posicion',
                'c.id as classroom_id', 'c.nombre as classroom_name',
                'p.primer_apellido as paterno',
                'p.segundo_apellido as materno',
                'p.nombres',
                'p.fec_nacimiento',
                'p.ubigeo_nacimiento',
                'p.anio_egreso as egreso',
                'p.id_colegio',
                'col.cod_modular',
                'prog.nombre as programa_estudios',
                'mod.nombre as modalidad',
            ])
            ->get();
    }

    private function detectSameCollege($students): array
    {
        $groups = [];

        // Filtrar estudiantes sin colegio asignado
        $filtered = $students->filter(fn($s) => !empty($s->id_colegio));

        $groupedByCollege = $filtered->groupBy(fn($s) => $s->id_colegio . '|' . $s->egreso);

        foreach ($groupedByCollege as $key => $group) {
            if (count($group) < 2) continue;

            [$colegioId, $egreso] = explode('|', $key);

            foreach ($group->groupBy('classroom_name') as $classroomName => $classroomStudents) {
                if (count($classroomStudents) < 2) continue;

                $groups[] = [
                    'id_colegio' => $colegioId ? (int)$colegioId : null,
                    'egreso' => $egreso ? (int)$egreso : null,
                    'cod_modular' => $classroomStudents[0]->cod_modular ?? 'No disponible',
                    'classroom_name' => $classroomName,
                    'students' => $classroomStudents->map(fn($s) => [
                        'id_postulante' => $s->id_postulante,
                        'code' => $s->codigo,
                        'position' => $s->posicion,
                        'nombres' => $s->nombres,
                        'paterno' => $s->paterno,
                        'materno' => $s->materno,
                        'programa_estudios' => $s->programa_estudios,
                        'modalidad' => $s->modalidad,
                    ])->toArray(),
                ];
            }
        }

        return $groups;
    }

    private function detectTwins($students): array
    {
        $alerts = [];

        // Filtrar estudiantes sin ubigeo o fecha de nacimiento (no se puede determinar mellizos)
        $filtered = $students->filter(fn($s) => !empty($s->ubigeo_nacimiento) && !empty($s->fec_nacimiento));

        // Agrupar por ambos apellidos, ubigeo_nacimiento y año de nacimiento
        $grouped = $filtered->groupBy(function ($s) {
            $birthYear = $s->fec_nacimiento ? substr($s->fec_nacimiento, 0, 4) : '';
            return $s->paterno . '|' . $s->materno . '|' . $s->ubigeo_nacimiento . '|' . $birthYear . '|' . $s->classroom_name;
        });

        foreach ($grouped as $key => $group) {
            if (count($group) < 2) continue;

            [$paterno, $materno, $ubigeoNac, $birthYear, $classroom] = explode('|', $key);

            $alerts[] = [
                'type' => 'twin_alert',
                'message' => 'Posibles mellizos/hermanos en el mismo salón',
                'classroom' => $classroom,
                'paterno' => $paterno,
                'materno' => $materno,
                'ubigeo_nacimiento' => $ubigeoNac ?: 'No disponible',
                'birth_year' => $birthYear ?: 'No disponible',
                'students' => $group->map(fn($s) => [
                    'id_postulante' => $s->id_postulante,
                    'code' => $s->codigo,
                    'position' => $s->posicion,
                    'nombres' => $s->nombres,
                    'paterno' => $s->paterno,
                    'materno' => $s->materno,
                    'fec_nacimiento' => $s->fec_nacimiento,
                    'ubigeo_nacimiento' => $s->ubigeo_nacimiento,
                ])->toArray(),
            ];
        }

        return $alerts;
    }

    private function detectSameParents($students): array
    {
        $studentIds = $students->pluck('id_postulante')->unique()->values()->all();

        // Obtener padres/madres de los postulantes
        // Filtrar padres sin documento (no se puede confirmar relación)
        $parents = DB::table('apoderado')
            ->whereIn('id_postulante', $studentIds)
            ->whereIn('tipo_apoderado', [1, 2])
            ->whereNotNull('nro_documento')
            ->where('nro_documento', '!=', '')
            ->select([
                'id_postulante', 'tipo_apoderado',
                'paterno', 'materno', 'nombres', 'nro_documento',
            ])
            ->get();

        if ($parents->isEmpty()) {
            return [];
        }

        // Agrupar por salón + tipo + identificador del padre/madre
        $groups = [];

        foreach ($students as $student) {
            $studentParents = $parents->where('id_postulante', $student->id_postulante);

            foreach ($studentParents as $parent) {
                // Usar DNI si existe, si no usar nombre completo
                $parentId = $parent->nro_documento
                    ? 'dni:' . $parent->nro_documento
                    : 'nom:' . strtolower($parent->paterno . $parent->materno . $parent->nombres);

                $typeLabel = $parent->tipo_apoderado == 1 ? 'Padre' : 'Madre';
                $groupKey = $student->classroom_name . '|' . $parent->tipo_apoderado . '|' . $parentId;

                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'classroom_name' => $student->classroom_name,
                        'parent_type' => $typeLabel,
                        'parent_name' => trim($parent->paterno . ' ' . $parent->materno . ', ' . $parent->nombres),
                        'parent_dni' => $parent->nro_documento ?: 'No disponible',
                        'students' => [],
                    ];
                }
                $groups[$groupKey]['students'][] = $student;
            }
        }

        $alerts = [];
        foreach ($groups as $group) {
            if (count($group['students']) < 2) continue;

            $alerts[] = [
                'type' => 'same_parent_alert',
                'message' => 'Mismos padres en el mismo salón',
                'classroom' => $group['classroom_name'],
                'parent_type' => $group['parent_type'],
                'parent_name' => $group['parent_name'],
                'parent_dni' => $group['parent_dni'],
                'students' => collect($group['students'])->map(fn($s) => [
                    'id_postulante' => $s->id_postulante,
                    'code' => $s->codigo,
                    'position' => $s->posicion,
                    'nombres' => $s->nombres,
                    'paterno' => $s->paterno,
                    'materno' => $s->materno,
                ])->toArray(),
            ];
        }

        return $alerts;
    }
}
