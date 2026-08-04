<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\Ambiente;
use App\Modules\Calificacion\Models\Classroom;
use App\Modules\Calificacion\Models\DistribucionAmbiente;
use App\Modules\Calificacion\Models\DistribucionAmbienteDetalle;
use App\Modules\Calificacion\Models\FilterGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribucionAmbienteController extends BaseCalificacionController
{
    /**
     * Listar distribuciones guardadas.
     */
    public function index(): JsonResponse
    {
        $distribuciones = DistribucionAmbiente::query()
            ->with(['grupoFiltro:id,descripcion,id_proceso,postulantes_count'])
            ->withCount('detalles')
            ->orderByDesc('id')
            ->get();

        return $this->successResponse($distribuciones);
    }

    /**
     * Listar grupos_filtro disponibles para vincular.
     */
    public function gruposFiltro(Request $request): JsonResponse
    {
        $query = FilterGroup::query()
            ->withCount(['aulas'])
            ->orderBy('orden_procesamiento');

        if ($request->has('id_proceso')) {
            $query->where('id_proceso', $request->input('id_proceso'));
        }

        $grupos = $query->get(['id', 'descripcion', 'id_proceso', 'postulantes_count', 'orden_procesamiento']);

        // IDs de grupos que ya tienen distribución
        $gruposConDistribucion = DistribucionAmbiente::pluck('id_grupo_filtro')
            ->filter()
            ->toArray();

        // Agregar info de aulas virtuales y estado
        $grupos->each(function ($g) use ($gruposConDistribucion) {
            $aulas = Classroom::where('grupo_filtro_id', $g->id)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'capacidad', 'contador_actual']);
            $g->aulas_virtuales = $aulas;
            $g->aulas_count = $aulas->count();
            $g->total_estudiantes_real = $aulas->sum('contador_actual');
            $g->ya_distribuido = in_array($g->id, $gruposConDistribucion);
        });

        return $this->successResponse($grupos);
    }

    /**
     * Mostrar una distribución con sus detalles.
     */
    public function show(int $id): JsonResponse
    {
        $distribucion = DistribucionAmbiente::with([
            'grupoFiltro:id,descripcion,id_proceso,postulantes_count',
            'detalles.ambiente:id,nombre,codigo',
            'detalles.aulaGestion:id,codigo,piso,tipo',
            'detalles.aulaVirtual:id,nombre,capacidad,contador_actual',
        ])->find($id);

        if (!$distribucion) {
            return $this->errorResponse('Distribución no encontrada', 404);
        }

        return $this->successResponse($distribucion);
    }

    /**
     * Crear una distribución de ambientes vinculada a un grupo_filtro.
     *
     * Recibe: { id_grupo_filtro: 18, ambientes: [id1, id3, id2] }
     * Lee las aulas virtuales del grupo y las mapea a aulas físicas (aulas_gestion).
     * El orden del array de ambientes define el orden de asignación de códigos.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_grupo_filtro' => 'required|integer|exists:grupos_filtro,id',
            'ambientes' => 'required|array|min:1',
            'ambientes.*' => 'integer|exists:ambientes,id',
        ]);

        $idGrupoFiltro = $data['id_grupo_filtro'];
        $ambientesIds = $data['ambientes'];

        // Cargar aulas virtuales del grupo_filtro (ya tienen estudiantes reales)
        $aulasVirtuales = Classroom::where('grupo_filtro_id', $idGrupoFiltro)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'capacidad', 'contador_actual']);

        if ($aulasVirtuales->isEmpty()) {
            return $this->errorResponse('El grupo seleccionado no tiene aulas virtuales generadas. Ejecute primero la distribución de aulas.', 422);
        }

        $totalEstudiantes = $aulasVirtuales->sum('contador_actual');
        $totalAulasNecesarias = $aulasVirtuales->count();

        // Extraer área del nombre (ej: "Bio_101" → "BIO")
        $areaNombre = $this->extraerArea($aulasVirtuales->first()->nombre);

        // Cargar ambientes físicos con sus aulas_gestion
        $ambientes = Ambiente::whereIn('id', $ambientesIds)
            ->where('estado', true)
            ->with(['aulas' => function ($q) {
                $q->where('estado', true)->orderBy('piso')->orderBy('codigo');
            }])
            ->get()
            ->keyBy('id');

        $ambientesOrdenados = collect($ambientesIds)
            ->map(fn($id) => $ambientes->get($id))
            ->filter();

        if ($ambientesOrdenados->isEmpty()) {
            return $this->errorResponse('No se encontraron ambientes activos', 422);
        }

        // Aplanar aulas físicas disponibles
        $aulasFisicas = collect();
        $ordenAmbiente = 0;

        foreach ($ambientesOrdenados as $ambiente) {
            $ordenAmbiente++;
            foreach ($ambiente->aulas as $aula) {
                $aulasFisicas->push([
                    'id_ambiente' => $ambiente->id,
                    'id_aula_gestion' => $aula->id,
                    'orden_ambiente' => $ordenAmbiente,
                    'capacidad' => $aula->capacidad,
                ]);
            }
        }

        if ($aulasFisicas->isEmpty()) {
            return $this->errorResponse('Los ambientes seleccionados no tienen aulas activas', 422);
        }

        if ($aulasFisicas->count() < $totalAulasNecesarias) {
            return $this->errorResponse(
                "Aulas físicas insuficientes. Se necesitan {$totalAulasNecesarias} (una por aula virtual) pero solo hay {$aulasFisicas->count()} disponibles.",
                422
            );
        }

        // Mapear 1 a 1: cada aula virtual → un aula física
        $codigoBase = 101;
        $detalles = [];

        foreach ($aulasVirtuales as $index => $aulaVirtual) {
            $aulaFisica = $aulasFisicas[$index];

            $detalles[] = [
                'id_ambiente' => $aulaFisica['id_ambiente'],
                'id_aula_gestion' => $aulaFisica['id_aula_gestion'],
                'id_aula' => $aulaVirtual->id,
                'area_nombre' => $areaNombre,
                'aula_virtual_nombre' => $aulaVirtual->nombre,
                'orden_ambiente' => $aulaFisica['orden_ambiente'],
                'codigo_asignado' => (string) ($codigoBase + $index),
                'capacidad' => $aulaFisica['capacidad'],
                'estudiantes_asignados' => $aulaVirtual->contador_actual,
            ];
        }

        $totalCapacidad = $aulasFisicas->take($totalAulasNecesarias)->sum('capacidad');

        // Persistir en transacción
        DB::transaction(function () use ($detalles, $data, $idGrupoFiltro, $totalEstudiantes, $totalAulasNecesarias, $totalCapacidad, &$distribucion) {
            $grupoFiltro = FilterGroup::find($idGrupoFiltro);

            $distribucion = DistribucionAmbiente::create([
                'id_proceso' => $grupoFiltro->id_proceso,
                'id_grupo_filtro' => $idGrupoFiltro,
                'total_estudiantes' => $totalEstudiantes,
                'total_aulas' => count($detalles),
                'total_capacidad' => $totalCapacidad,
                'estado' => 'confirmada',
            ]);

            foreach ($detalles as &$detalle) {
                $detalle['id_distribucion'] = $distribucion->id;
            }

            DB::table('distribucion_ambiente_detalles')->insert($detalles);
        });

        $distribucion->load([
            'grupoFiltro:id,descripcion,id_proceso,postulantes_count',
            'detalles.ambiente:id,nombre,codigo',
            'detalles.aulaGestion:id,codigo,piso,tipo',
            'detalles.aulaVirtual:id,nombre,capacidad,contador_actual',
        ]);

        return $this->successResponse(
            $distribucion,
            "Distribución creada: {$totalEstudiantes} estudiantes en " . count($detalles) . " aulas. Área: {$areaNombre}",
            201
        );
    }

    /**
     * Actualizar una distribución existente (cambiar ambientes y reordenar).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'ambientes' => 'required|array|min:1',
            'ambientes.*' => 'integer|exists:ambientes,id',
        ]);

        $distribucion = DistribucionAmbiente::find($id);
        if (!$distribucion) {
            return $this->errorResponse('Distribución no encontrada', 404);
        }

        $idGrupoFiltro = $distribucion->id_grupo_filtro;
        $ambientesIds = $data['ambientes'];

        // Recargar aulas virtuales del grupo
        $aulasVirtuales = Classroom::where('grupo_filtro_id', $idGrupoFiltro)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'capacidad', 'contador_actual']);

        if ($aulasVirtuales->isEmpty()) {
            return $this->errorResponse('El grupo no tiene aulas virtuales.', 422);
        }

        $totalEstudiantes = $aulasVirtuales->sum('contador_actual');
        $totalAulasNecesarias = $aulasVirtuales->count();
        $areaNombre = $this->extraerArea($aulasVirtuales->first()->nombre);

        // Cargar ambientes físicos
        $ambientes = Ambiente::whereIn('id', $ambientesIds)
            ->where('estado', true)
            ->with(['aulas' => function ($q) {
                $q->where('estado', true)->orderBy('piso')->orderBy('codigo');
            }])
            ->get()
            ->keyBy('id');

        $ambientesOrdenados = collect($ambientesIds)
            ->map(fn($id) => $ambientes->get($id))
            ->filter();

        if ($ambientesOrdenados->isEmpty()) {
            return $this->errorResponse('No se encontraron ambientes activos', 422);
        }

        $aulasFisicas = collect();
        $ordenAmbiente = 0;

        foreach ($ambientesOrdenados as $ambiente) {
            $ordenAmbiente++;
            foreach ($ambiente->aulas as $aula) {
                $aulasFisicas->push([
                    'id_ambiente' => $ambiente->id,
                    'id_aula_gestion' => $aula->id,
                    'orden_ambiente' => $ordenAmbiente,
                    'capacidad' => $aula->capacidad,
                ]);
            }
        }

        if ($aulasFisicas->count() < $totalAulasNecesarias) {
            return $this->errorResponse(
                "Aulas físicas insuficientes. Se necesitan {$totalAulasNecesarias} pero solo hay {$aulasFisicas->count()}.",
                422
            );
        }

        // Reconstruir detalles
        $codigoBase = 101;
        $detalles = [];

        foreach ($aulasVirtuales as $index => $aulaVirtual) {
            $aulaFisica = $aulasFisicas[$index];

            $detalles[] = [
                'id_distribucion' => $id,
                'id_ambiente' => $aulaFisica['id_ambiente'],
                'id_aula_gestion' => $aulaFisica['id_aula_gestion'],
                'id_aula' => $aulaVirtual->id,
                'area_nombre' => $areaNombre,
                'aula_virtual_nombre' => $aulaVirtual->nombre,
                'orden_ambiente' => $aulaFisica['orden_ambiente'],
                'codigo_asignado' => (string) ($codigoBase + $index),
                'capacidad' => $aulaFisica['capacidad'],
                'estudiantes_asignados' => $aulaVirtual->contador_actual,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $totalCapacidad = $aulasFisicas->take($totalAulasNecesarias)->sum('capacidad');

        DB::transaction(function () use ($id, $detalles, $distribucion, $totalEstudiantes, $totalAulasNecesarias, $totalCapacidad) {
            // Eliminar detalles viejos
            DB::table('distribucion_ambiente_detalles')->where('id_distribucion', $id)->delete();

            // Actualizar encabezado
            $distribucion->update([
                'total_estudiantes' => $totalEstudiantes,
                'total_aulas' => count($detalles),
                'total_capacidad' => $totalCapacidad,
            ]);

            // Insertar nuevos detalles
            DB::table('distribucion_ambiente_detalles')->insert($detalles);
        });

        $distribucion->load([
            'grupoFiltro:id,descripcion,id_proceso,postulantes_count',
            'detalles.ambiente:id,nombre,codigo',
            'detalles.aulaGestion:id,codigo,piso,tipo',
            'detalles.aulaVirtual:id,nombre,capacidad,contador_actual',
        ]);

        return $this->successResponse(
            $distribucion,
            "Distribución actualizada: {$totalEstudiantes} estudiantes en " . count($detalles) . " aulas."
        );
    }

    /**
     * Eliminar una distribución.
     */
    public function destroy(int $id): JsonResponse
    {
        $distribucion = DistribucionAmbiente::find($id);

        if (!$distribucion) {
            return $this->errorResponse('Distribución no encontrada', 404);
        }

        $distribucion->delete();

        return $this->successResponse(null, 'Distribución eliminada correctamente');
    }

    /**
     * Extraer el área del nombre del aula virtual.
     * Ej: "Bio_101" → "BIO", "Ing_1" → "ING", "Soc_1" → "SOC"
     */
    private function extraerArea(string $nombre): string
    {
        $parts = explode('_', $nombre);
        return strtoupper($parts[0] ?? 'GEN');
    }
}
