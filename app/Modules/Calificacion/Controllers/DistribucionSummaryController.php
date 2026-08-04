<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\DistribucionAmbiente;
use App\Modules\Calificacion\Models\AsignacionPersonal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistribucionSummaryController extends BaseCalificacionController
{
    /**
     * Resumen agregado de todas las distribuciones de un proceso.
     *
     * Combina el sistema legacy (grupos_filtro → aulas → asignaciones_aulas)
     * y el sistema de ambientes (distribucion_ambientes → detalles → personal).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_proceso' => 'required|integer',
        ]);

        $idProceso = (int) $request->input('id_proceso');

        // ─── Datos del proceso ───
        $proceso = DB::table('procesos')->where('id', $idProceso)->first();

        // ─── Distribuciones Legacy (grupos_filtro → aulas) ───
        $grupos = DB::table('grupos_filtro')
            ->where('id_proceso', $idProceso)
            ->orderByDesc('id')
            ->get();

        // Total real de postulantes del proceso desde inscripciones (estado = 0)
        $totalPostulantes = DB::table('inscripciones')
            ->where('id_proceso', $idProceso)
            ->where('estado', 0)
            ->count();

        $distribucionesLegacy = [];
        $legacyTotalPostulantes = 0;
        $legacyTotalAulas = 0;

        foreach ($grupos as $grupo) {
            $aulas = DB::table('aulas')->where('grupo_filtro_id', $grupo->id)->get();
            $aulasIds = $aulas->pluck('id')->toArray();

            $postulantesCount = DB::table('inscripciones')
                ->where('grupo_filtro_id', $grupo->id)
                ->where('estado', 0)
                ->count();

            $distribucionesLegacy[] = [
                'grupo_filtro_id' => $grupo->id,
                'descripcion' => $grupo->descripcion,
                'postulantes_count' => $postulantesCount,
                'aulas_count' => $aulas->count(),
                'tiene_distribucion' => $aulas->isNotEmpty(),
            ];

            $legacyTotalPostulantes += $postulantesCount;
            $legacyTotalAulas += $aulas->count();
        }

        // ─── Distribuciones de Ambientes ───
        $distAmbientes = DistribucionAmbiente::where('id_proceso', $idProceso)
            ->withCount('detalles')
            ->orderByDesc('id')
            ->get();

        $ambientesTotalEstudiantes = 0;
        $ambientesTotalAulas = 0;
        $ambientesTotalCapacidad = 0;
        $personalAsignadoTotal = 0;

        $distribucionesAmbientes = [];

        foreach ($distAmbientes as $dist) {
            $personalCount = AsignacionPersonal::where('id_distribucion', $dist->id)->count();

            $distribucionesAmbientes[] = [
                'id' => $dist->id,
                'total_estudiantes' => $dist->total_estudiantes,
                'total_aulas' => $dist->total_aulas,
                'total_capacidad' => $dist->total_capacidad,
                'estado' => $dist->estado,
                'detalles_count' => $dist->detalles_count,
                'personal_asignado' => $personalCount,
                'created_at' => $dist->created_at?->format('Y-m-d H:i'),
            ];

            $ambientesTotalEstudiantes += $dist->total_estudiantes;
            $ambientesTotalAulas += $dist->total_aulas;
            $ambientesTotalCapacidad += $dist->total_capacidad;
            $personalAsignadoTotal += $personalCount;
        }

        // ─── Stats agregadas ───
        // Los estudiantes de ambientes son los mismos postulantes del proceso,
        // no se suman para evitar doble conteo. El total real viene de inscripciones.
        $stats = [
            'total_distribuciones' => count($distribucionesLegacy) + count($distribucionesAmbientes),
            'total_postulantes' => $totalPostulantes,
            'aulas_distribucion' => $legacyTotalAulas,
            'aulas_ambientes' => $ambientesTotalAulas,
            'total_capacidad_ambientes' => $ambientesTotalCapacidad,
            'personal_asignado' => $personalAsignadoTotal,
        ];

        return $this->successResponse([
            'proceso' => $proceso ? [
                'id_proceso' => $proceso->id,
                'nombre' => $proceso->nombre,
            ] : null,
            'stats' => $stats,
            'distribuciones_legacy' => $distribucionesLegacy,
            'distribuciones_ambientes' => $distribucionesAmbientes,
        ]);
    }
}
