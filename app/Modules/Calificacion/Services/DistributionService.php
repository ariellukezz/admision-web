<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\FilterGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributionService
{
    public function getStatsByModalities(array $idModalidades, int $idProceso, ?string $filterType = null, array $filterIds = []): array
    {
        $baseQuery = DB::table('inscripciones')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->whereIn('inscripciones.id_modalidad', $idModalidades)
            ->where('inscripciones.id_proceso', $idProceso)
            ->where('inscripciones.estado', 0);

        if ($filterType === 'area' && !empty($filterIds)) {
            $baseQuery->whereIn('areas.id', $filterIds);
        } elseif ($filterType === 'programa' && !empty($filterIds)) {
            $baseQuery->whereIn('programa.id', $filterIds);
        }

        $totalPostulantes = (clone $baseQuery)->count();
        $conCodigo = (clone $baseQuery)
            ->whereNotNull('inscripciones.codigo_distribucion')
            ->count();

        $disponibles = $totalPostulantes - $conCodigo;

        $porModalidad = DB::table('inscripciones')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->whereIn('inscripciones.id_modalidad', $idModalidades)
            ->where('inscripciones.id_proceso', $idProceso)
            ->where('inscripciones.estado', 0)
            ->select('modalidad.id as id_modalidad', 'modalidad.nombre as modalidad')
            ->selectRaw('COUNT(DISTINCT inscripciones.id_postulante) as total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN inscripciones.codigo_distribucion IS NOT NULL THEN inscripciones.id_postulante END) as con_codigo')
            ->groupBy('modalidad.id', 'modalidad.nombre')
            ->get();

        $codigosPorArea = $this->getCodesByArea($idModalidades, $idProceso);
        $codigosPorPrograma = $this->getCodesByPrograma($idModalidades, $idProceso);

        return [
            'total_postulantes' => $totalPostulantes,
            'total_disponibles' => $disponibles,
            'total_con_codigo' => $conCodigo,
            'porcentaje_procesado' => $totalPostulantes > 0 ? round(($conCodigo / $totalPostulantes) * 100, 2) : 0,
            'por_modalidad' => $porModalidad,
            'codigos_generados' => [
                'por_area' => $codigosPorArea,
                'por_programa' => $codigosPorPrograma,
            ],
        ];
    }

    public function getAvailablePostulantes(array $idModalidades, int $idProceso, ?string $filterType = null, array $filterIds = [], int $limit = 50, int $offset = 0): array
    {
        $query = $this->buildBaseQuery($idModalidades, $idProceso, $filterType, $filterIds);
        $total = $query->count();

        $postulantes = (clone $query)
            ->select([
                'inscripciones.id as id_inscripcion',
                'inscripciones.fecha as fecha_inscripcion',
                'inscripciones.id_programa',
                'inscripciones.id_modalidad',
                'inscripciones.id_postulante',
                'inscripciones.id_proceso',
                'postulante.nro_doc as n_documento',
                'postulante.primer_apellido as paterno',
                'postulante.segundo_apellido as materno',
                'postulante.nombres',
                'postulante.sexo',
                'postulante.fec_nacimiento',
                'postulante.anio_egreso as egreso',
                'postulante.id_colegio',
                'programa.nombre as programa_estudios',
                'programa.id as id_programa',
                'areas.nombre as area',
                'areas.id as id_area',
                'modalidad.nombre as modalidad',
            ])
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'data' => $postulantes,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total,
            ],
        ];
    }

    public function generateAndSaveCodes(array $params): array
    {
        $tableName = $params['table_name'];
        $idModalidades = $params['id_modalidades'];
        $idProceso = $params['id_proceso'];
        $conditions = $params['conditions'];
        $filterType = $params['filter_type'] ?? null;
        $filterIds = $params['filter_ids'] ?? [];
        $processingOrder = $params['processing_order'] ?? 999;
        $description = $params['description'] ?? 'Grupo de filtrado';

        $selection = DB::table('selecciones_columnas')
            ->where('nombre_tabla', $tableName)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$selection) {
            throw new \DomainException('No se encontró selección de columnas');
        }

        DB::beginTransaction();

        $selectedColumns = is_array($selection->columnas_seleccionadas)
            ? $selection->columnas_seleccionadas
            : json_decode($selection->columnas_seleccionadas, true);

        $selectFields = $this->buildSelectFieldsWithAliases($selectedColumns);

        $query = $this->buildBaseQuery($idModalidades, $idProceso, $filterType, $filterIds);
        $postulantes = (clone $query)->select($selectFields)->get();

        if ($postulantes->isEmpty()) {
            DB::rollBack();
            throw new \DomainException('No hay postulantes disponibles para generar códigos');
        }

        $filterGroup = FilterGroup::create([
            'seleccion_id' => $selection->id,
            'descripcion' => $description,
            'id_modalidad' => $idModalidades[0],
            'id_modalidades' => $idModalidades,
            'id_proceso' => $idProceso,
            'orden_procesamiento' => $processingOrder,
            'postulantes_count' => $postulantes->count(),
        ]);

        $conditionsData = [];
        foreach ($conditions as $condition) {
            $this->validateConditionParams($condition);
            $conditionsData[] = [
                'grupo_filtro_id' => $filterGroup->id,
                'nombre_columna' => $condition['column'],
                'tipo_condicion' => $condition['condition'],
                'parametros_condicion' => json_encode($condition['params']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('condiciones_filtro')->insert($conditionsData);

        $codesData = [];
        $codesGenerated = 0;
        $codesFailed = 0;

        foreach ($postulantes as $postulante) {
            try {
                $code = $this->generateCode($postulante, $conditions, $selectedColumns);
                $codesData[] = [
                    'id_postulante' => $postulante->id_postulante,
                    'id_modalidad' => $postulante->id_modalidad,
                    'codigo_distribucion' => strtoupper($code),
                ];
                $codesGenerated++;
            } catch (\Exception $e) {
                Log::error('Error generando código', [
                    'postulante_id' => $postulante->id_postulante ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $codesFailed++;
            }
        }

        foreach ($codesData as $codeRow) {
            DB::table('inscripciones')
                ->where('id_postulante', $codeRow['id_postulante'])
                ->where('id_modalidad', $codeRow['id_modalidad'])
                ->where('id_proceso', $idProceso)
                ->update([
                    'codigo_distribucion' => $codeRow['codigo_distribucion'],
                    'grupo_filtro_id' => $filterGroup->id,
                    'updated_at' => now(),
                ]);
        }

        DB::commit();

        return [
            'filter_group_id' => $filterGroup->id,
            'codes_count' => count($codesData),
            'codes_generated' => $codesGenerated,
            'codes_failed' => $codesFailed,
            'modalidades' => $idModalidades,
        ];
    }

    public function getPreview(int $filterGroupId): array
    {
        $filterGroup = FilterGroup::with(['conditions', 'selection'])->findOrFail($filterGroupId);

        $codes = DB::table('inscripciones')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->where('inscripciones.grupo_filtro_id', $filterGroupId)
            ->where('inscripciones.estado', 0)
            ->whereNotNull('inscripciones.codigo_distribucion')
            ->select([
                'inscripciones.id', 'inscripciones.codigo_distribucion', 'inscripciones.id_postulante',
                'postulante.nro_doc as n_documento',
                'postulante.primer_apellido as paterno',
                'postulante.segundo_apellido as materno',
                'postulante.nombres',
                'programa.nombre as programa_estudios',
                'areas.nombre as area',
                'modalidad.nombre as modalidad',
            ])
            ->get();

        $stats = [
            'total_codes' => $codes->count(),
            'por_modalidad' => $codes->groupBy('modalidad')->map->count(),
            'por_area' => $codes->groupBy('area')->map->count(),
            'por_programa' => $codes->groupBy('programa_estudios')->map->count(),
        ];

        $idModalidades = is_array($filterGroup->id_modalidades)
            ? $filterGroup->id_modalidades
            : json_decode($filterGroup->id_modalidades, true);

        $modalidadesNames = DB::table('modalidad')
            ->whereIn('id', $idModalidades ?? [])
            ->pluck('nombre');

        $conditions = $filterGroup->conditions->map(function ($c) {
            return [
                'id' => $c->id,
                'column_name' => $c->nombre_columna,
                'condition_type' => $c->tipo_condicion,
                'condition_params' => $c->parametros_condicion,
            ];
        });

        $codes->transform(function ($c) {
            $c->codigo = $c->codigo_distribucion;
            return $c;
        });

        return [
            'filter_group' => $filterGroup,
            'conditions' => $conditions,
            'codes' => $codes,
            'stats' => $stats,
            'modalidades_names' => $modalidadesNames,
        ];
    }

    public function deleteFilterGroup(int $filterGroupId): void
    {
        DB::beginTransaction();
        DB::table('inscripciones')
            ->where('grupo_filtro_id', $filterGroupId)
            ->update(['codigo_distribucion' => null, 'grupo_filtro_id' => null]);
        DB::table('condiciones_filtro')->where('grupo_filtro_id', $filterGroupId)->delete();
        FilterGroup::findOrFail($filterGroupId)->delete();
        DB::commit();
    }

    public function getFilterGroups()
    {
        $groups = FilterGroup::with(['conditions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $groups->map(function ($g) {
            $count = DB::table('inscripciones')
                ->where('grupo_filtro_id', $g->id)
                ->where('estado', 0)
                ->whereNotNull('codigo_distribucion')
                ->count();

            return [
                'id' => $g->id,
                'description' => $g->descripcion,
                'processing_order' => $g->orden_procesamiento,
                'postulantes_count' => $count,
                'conditions' => $g->conditions->map(fn($c) => [
                    'column' => $c->nombre_columna,
                    'condition' => $c->tipo_condicion,
                ])->toArray(),
            ];
        });
    }

    private function buildBaseQuery($idModalidades, $idProceso, $filterType, $filterIds)
    {
        $query = DB::table('inscripciones')
            ->join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->leftJoin('colegios', 'postulante.id_colegio', '=', 'colegios.id')
            ->whereIn('inscripciones.id_modalidad', $idModalidades)
            ->where('inscripciones.id_proceso', $idProceso)
            ->where('inscripciones.estado', 0);

        if ($filterType === 'area' && !empty($filterIds)) {
            $query->whereIn('areas.id', $filterIds);
        } elseif ($filterType === 'programa' && !empty($filterIds)) {
            $query->whereIn('programa.id', $filterIds);
        }

        $query->whereNull('inscripciones.codigo_distribucion');

        return $query;
    }

    private function getColumnMapping(): array
    {
        return [
            'postulante.postulante.nro_doc' => 'n_documento',
            'postulante.postulante.primer_apellido' => 'paterno',
            'postulante.postulante.segundo_apellido' => 'materno',
            'postulante.postulante.nombres' => 'nombres',
            'postulante.postulante.sexo' => 'sexo',
            'postulante.postulante.fec_nacimiento' => 'fec_nacimiento',
            'postulante.postulante.anio_egreso' => 'egreso',
            'postulante.postulante.id_colegio' => 'id_colegio',
            'postulante.colegio.colegio.id' => 'colegio_id',
            'postulante.colegio.colegio.cod_modular' => 'colegio_cod_modular',
            'postulante.colegio.colegio.nombre' => 'colegio_nombre',
            'programa.programa.id' => 'programa_id',
            'programa.programa.nombre' => 'programa_estudios',
            'programa.area.id' => 'area_id',
            'programa.area.nombre' => 'area',
            'modalidad.id' => 'modalidad_id',
            'modalidad.nombre' => 'modalidad',
            'inscripciones.id' => 'id_inscripcion',
            'inscripciones.fecha' => 'fecha_inscripcion',
        ];
    }

    private function buildSelectFieldsWithAliases(array $selectedColumns): array
    {
        $selectFields = ['inscripciones.id_postulante', 'inscripciones.id_modalidad'];
        $mapping = $this->getColumnMapping();

        foreach ($selectedColumns as $columnPath) {
            $alias = $mapping[$columnPath] ?? null;
            if (!$alias) continue;

            if (str_contains($columnPath, 'programa.area')) {
                if ($alias === 'area_id') {
                    $selectFields[] = 'areas.id as area_id';
                } else {
                    $selectFields[] = "areas.nombre as {$alias}";
                }
            } elseif (str_contains($columnPath, 'programa.programa')) {
                if ($alias === 'programa_id') {
                    $selectFields[] = 'programa.id as programa_id';
                } else {
                    $selectFields[] = "programa.nombre as {$alias}";
                }
            } elseif (str_contains($columnPath, 'postulante.colegio')) {
                $field = match ($alias) {
                    'colegio_id' => 'colegios.id as colegio_id',
                    'colegio_cod_modular' => 'colegios.cod_modular as colegio_cod_modular',
                    'colegio_nombre' => 'colegios.nombre as colegio_nombre',
                    default => null,
                };
                if ($field) $selectFields[] = $field;
            } elseif (str_contains($columnPath, 'postulante.postulante')) {
                $selectFields[] = "postulante.{$this->getRealPostulanteColumn($alias)} as {$alias}";
            } elseif (str_contains($columnPath, 'modalidad')) {
                if ($alias === 'modalidad_id') {
                    $selectFields[] = 'modalidad.id as modalidad_id';
                } else {
                    $selectFields[] = "modalidad.nombre as {$alias}";
                }
            } elseif (str_contains($columnPath, 'inscripciones')) {
                if ($alias === 'id_inscripcion') {
                    $selectFields[] = 'inscripciones.id as id_inscripcion';
                } elseif ($alias === 'fecha_inscripcion') {
                    $selectFields[] = 'inscripciones.fecha as fecha_inscripcion';
                }
            }
        }

        return array_unique($selectFields);
    }

    private function getRealPostulanteColumn(string $alias): string
    {
        return match ($alias) {
            'n_documento' => 'nro_doc',
            'paterno' => 'primer_apellido',
            'materno' => 'segundo_apellido',
            'id_colegio' => 'id_colegio',
            'egreso' => 'anio_egreso',
            default => $alias,
        };
    }

    private function generateCode($postulante, array $conditions, array $selectedColumns): string
    {
        $code = '';
        $mapping = $this->getColumnMapping();

        foreach ($conditions as $condition) {
            $columnPath = $condition['column'];
            $mappedColumn = $mapping[$columnPath] ?? basename(str_replace('.', '/', $columnPath));
            $value = $postulante->$mappedColumn ?? '';

            if ($value === '' || $value === null) continue;

            $stringValue = (string) $value;
            $length = mb_strlen($stringValue);

            switch ($condition['condition']) {
                case 'last_n':
                    $n = (int)($condition['params']['n'] ?? 1);
                    $code .= mb_substr($stringValue, -$n);
                    break;
                case 'first_n':
                    $n = (int)($condition['params']['n'] ?? 1);
                    $code .= mb_substr($stringValue, 0, $n);
                    break;
                case 'nth_digit':
                    $position = (int)($condition['params']['position'] ?? 1) - 1;
                    if ($position >= 0 && $position < $length) {
                        $code .= mb_substr($stringValue, $position, 1);
                    }
                    break;
                case 'middle_m_n':
                    $start = (int)($condition['params']['start'] ?? 1) - 1;
                    $end = (int)($condition['params']['end'] ?? 1) - 1;
                    $extractLength = $end - $start + 1;
                    if ($start >= 0 && $start < $length && $extractLength > 0) {
                        $code .= mb_substr($stringValue, $start, $extractLength);
                    }
                    break;
                case 'reverse_n_m':
                    $n = (int)($condition['params']['n'] ?? 1);
                    $m = (int)($condition['params']['m'] ?? 1);
                    $start = $length - $m;
                    $extractLength = $m - $n + 1;
                    if ($start >= 0 && $extractLength > 0) {
                        $code .= mb_substr($stringValue, $start, $extractLength);
                    }
                    break;
            }
        }

        $finalCode = trim($code);
        return empty($finalCode) ? 'N/A' : $finalCode;
    }

    private function validateConditionParams(array $condition): void
    {
        $type = $condition['condition'];
        $params = $condition['params'];

        if (in_array($type, ['last_n', 'first_n']) && !isset($params['n'])) {
            throw new \DomainException("Parámetro 'n' requerido para {$type}");
        }
        if ($type === 'nth_digit' && !isset($params['position'])) {
            throw new \DomainException("Parámetro 'position' requerido para nth_digit");
        }
        if ($type === 'middle_m_n' && (!isset($params['start']) || !isset($params['end']))) {
            throw new \DomainException("Parámetros 'start' y 'end' requeridos para middle_m_n");
        }
        if ($type === 'reverse_n_m' && (!isset($params['n']) || !isset($params['m']))) {
            throw new \DomainException("Parámetros 'n' y 'm' requeridos para reverse_n_m");
        }
        if (isset($params['end']) && isset($params['start']) && $params['end'] < $params['start']) {
            throw new \DomainException("El valor de 'end' debe ser mayor o igual a 'start'");
        }
        if ($type === 'reverse_n_m' && $params['m'] < $params['n']) {
            throw new \DomainException("El valor de 'm' debe ser mayor o igual a 'n'");
        }
    }

    private function getCodesByArea(array $idModalidades, int $idProceso)
    {
        return DB::table('inscripciones')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->whereIn('inscripciones.id_modalidad', $idModalidades)
            ->where('inscripciones.id_proceso', $idProceso)
            ->where('inscripciones.estado', 0)
            ->whereNotNull('inscripciones.codigo_distribucion')
            ->select('areas.id as id_area', 'areas.nombre as area')
            ->selectRaw('COUNT(DISTINCT inscripciones.id_postulante) as cantidad')
            ->groupBy('areas.id', 'areas.nombre')
            ->orderBy('cantidad', 'desc')
            ->get();
    }

    private function getCodesByPrograma(array $idModalidades, int $idProceso)
    {
        return DB::table('inscripciones')
            ->join('programa', 'inscripciones.id_programa', '=', 'programa.id')
            ->leftJoin('areas', 'programa.id_area', '=', 'areas.id')
            ->whereIn('inscripciones.id_modalidad', $idModalidades)
            ->where('inscripciones.id_proceso', $idProceso)
            ->where('inscripciones.estado', 0)
            ->whereNotNull('inscripciones.codigo_distribucion')
            ->select('programa.id as id_programa', 'programa.nombre as programa_estudios', 'areas.nombre as area')
            ->selectRaw('COUNT(DISTINCT inscripciones.id_postulante) as cantidad')
            ->groupBy('programa.id', 'programa.nombre', 'areas.nombre')
            ->orderBy('cantidad', 'desc')
            ->limit(10)
            ->get();
    }
}
