<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Models\AsignacionPersonal;
use App\Modules\Calificacion\Models\DistribucionAmbiente;
use App\Modules\Calificacion\Models\Participante;
use App\Models\Cargo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionPersonalController extends BaseCalificacionController
{
    /**
     * Listar todo el personal asignado a una distribución.
     * Devuelve la estructura de aulas agrupadas por ambiente y piso.
     */
    public function index(int $idDistribucion): JsonResponse
    {
        $distribucion = DistribucionAmbiente::with([
            'detalles.ambiente:id,nombre,codigo',
            'detalles.aulaGestion:id,codigo,piso,tipo,capacidad',
            'detalles.personalAsignado.cargo:id,nombre',
            'detalles.personalAsignado.participante:id,dni,nombres,paterno,materno,puesto,unidad',
        ])->find($idDistribucion);

        if (!$distribucion) {
            return $this->errorResponse('Distribución no encontrada', 404);
        }

        $ambientes = [];
        foreach ($distribucion->detalles as $detalle) {
            $idAmb = $detalle->id_ambiente;
            if (!isset($ambientes[$idAmb])) {
                $ambientes[$idAmb] = [
                    'id_ambiente' => $idAmb,
                    'nombre_ambiente' => $detalle->ambiente->nombre ?? '',
                    'codigo_ambiente' => $detalle->ambiente->codigo ?? '',
                    'aulas' => [],
                ];
            }

            $aulaGestion = $detalle->aulaGestion;
            $personal = $detalle->personalAsignado;

            $ambientes[$idAmb]['aulas'][] = [
                'id_detalle' => $detalle->id,
                'codigo_asignado' => $detalle->codigo_asignado,
                'piso' => $aulaGestion->piso ?? 1,
                'capacidad' => $detalle->capacidad,
                'estudiantes_asignados' => $detalle->estudiantes_asignados,
                'personal' => $personal->map(fn ($p) => $this->formatPersonal($p))->values()->toArray(),
            ];
        }

        // Personal a nivel de ambiente o distribución
        $personalGlobal = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->whereNull('id_distribucion_detalle')
            ->with(['cargo:id,nombre', 'ambiente:id,nombre,codigo', 'participante:id,dni,nombres,paterno,materno,puesto,unidad'])
            ->get();

        $supervisores = $personalGlobal->filter(fn ($p) => $this->isSupervisor($p->cargo))->values();
        $coordinadores = $personalGlobal->filter(fn ($p) => $this->isCoordinador($p->cargo))->values();

        return $this->successResponse([
            'distribucion' => [
                'id' => $distribucion->id,
                'total_estudiantes' => $distribucion->total_estudiantes,
                'total_aulas' => $distribucion->total_aulas,
                'estado' => $distribucion->estado,
            ],
            'ambientes' => array_values($ambientes),
            'supervisores' => $supervisores->map(fn ($p) => $this->formatPersonal($p))->toArray(),
            'coordinadores' => $coordinadores->map(fn ($p) => $this->formatPersonal($p))->toArray(),
        ]);
    }

    /**
     * Asignar vigilantes aleatoriamente: 1 por aula.
     *
     * - No limpia asignaciones previas (permite múltiples rondas)
     * - Solo asigna a aulas que NO tienen vigilante aún
     * - Si ambientes_seleccionados viene, solo asigna a esos ambientes
     * - Los sobrantes quedan en espera (no se asignan)
     */
    public function asignarAleatorio(Request $request, int $idDistribucion): JsonResponse
    {
        $request->validate([
            'id_proceso' => 'required|integer',
            'ambientes_seleccionados' => 'nullable|array',
            'ambientes_seleccionados.*' => 'integer',
        ]);

        $distribucion = DistribucionAmbiente::with(['detalles.aulaGestion'])->find($idDistribucion);
        if (!$distribucion) {
            return $this->errorResponse('Distribución no encontrada', 404);
        }

        $idProceso = $request->input('id_proceso');
        $ambientesSel = $request->input('ambientes_seleccionados');

        // Obtener cargo vigilante
        $cargos = Cargo::where('estado', true)->get();
        $vigilanteCargo = $cargos->first(fn ($c) => $this->isVigilante($c));

        if (!$vigilanteCargo) {
            return $this->errorResponse('No existe el cargo "Vigilante". Cree el cargo primero.', 422);
        }

        // Filtrar detalles: solo aulas sin vigilante aún
        $detallesAsignadosIds = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->where('id_cargo', $vigilanteCargo->id)
            ->whereNotNull('id_distribucion_detalle')
            ->pluck('id_distribucion_detalle')
            ->toArray();

        $aulasPendientes = $distribucion->detalles
            ->filter(function ($detalle) use ($detallesAsignadosIds, $ambientesSel) {
                if (in_array($detalle->id, $detallesAsignadosIds)) return false;
                if ($ambientesSel && !in_array($detalle->id_ambiente, $ambientesSel)) return false;
                return true;
            })
            ->shuffle();

        if ($aulasPendientes->isEmpty()) {
            return $this->errorResponse('No hay aulas pendientes de vigilante en la selección actual.', 422);
        }

        // Participantes disponibles (no asignados a esta distribución)
        $yaAsignados = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->pluck('id_participante')
            ->toArray();

        $disponibles = Participante::where('id_proceso', $idProceso)
            ->whereNotIn('id', $yaAsignados)
            ->inRandomOrder()
            ->get();

        if ($disponibles->isEmpty()) {
            return $this->errorResponse('No hay participantes disponibles para asignar.', 422);
        }

        // Asignar 1 vigilante por aula hasta que se acaben los disponibles o las aulas
        $asignaciones = [];
        $idx = 0;

        foreach ($aulasPendientes as $detalle) {
            if ($idx >= $disponibles->count()) break;

            $part = $disponibles[$idx++];
            $asignaciones[] = [
                'id_distribucion' => $idDistribucion,
                'id_participante' => $part->id,
                'id_cargo' => $vigilanteCargo->id,
                'id_ambiente' => $detalle->id_ambiente,
                'id_aula_gestion' => $detalle->id_aula_gestion,
                'id_distribucion_detalle' => $detalle->id,
                'turno' => 'manana',
                'estado' => 'asignado',
                'observaciones' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('asignacion_personal')->insert($asignaciones);

        $asignadas = count($asignaciones);
        $quedanDisponibles = $disponibles->count() - $asignadas;
        $aulasSinCubrir = $aulasPendientes->count() - $asignadas;

        $mensaje = "Se asignaron {$asignadas} vigilantes.";
        if ($quedanDisponibles > 0) {
            $mensaje .= " {$quedanDisponibles} en espera.";
        }
        if ($aulasSinCubrir > 0) {
            $mensaje .= " ⚠ {$aulasSinCubrir} aula(s) sin vigilante.";
        }

        return $this->successResponse([
            'total_asignadas' => $asignadas,
            'en_espera' => $quedanDisponibles,
            'aulas_sin_cubrir' => $aulasSinCubrir,
        ], $mensaje);
    }

    /**
     * Asignar manualmente una persona a un aula/ambiente.
     */
    public function store(Request $request, int $idDistribucion): JsonResponse
    {
        $data = $request->validate([
            'id_participante' => 'required|integer|exists:participantes,id',
            'id_cargo' => 'required|integer|exists:cargos,id',
            'id_ambiente' => 'nullable|integer|exists:ambientes,id',
            'id_aula_gestion' => 'nullable|integer|exists:aulas_gestion,id',
            'id_distribucion_detalle' => 'nullable|integer|exists:distribucion_ambiente_detalles,id',
            'turno' => 'nullable|in:manana,tarde,noche',
            'observaciones' => 'nullable|string',
        ]);

        $data['id_distribucion'] = $idDistribucion;
        $data['estado'] = 'asignado';

        $asignacion = AsignacionPersonal::create($data);
        $asignacion->load([
            'cargo:id,nombre',
            'participante:id,dni,nombres,paterno,materno,puesto,unidad',
        ]);

        return $this->successResponse(
            $this->formatPersonal($asignacion),
            'Personal asignado correctamente',
            201
        );
    }

    /**
     * Actualizar una asignación.
     */
    public function update(Request $request, int $idDistribucion, int $id): JsonResponse
    {
        $asignacion = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->where('id', $id)
            ->first();

        if (!$asignacion) {
            return $this->errorResponse('Asignación no encontrada', 404);
        }

        $data = $request->validate([
            'id_ambiente' => 'nullable|integer|exists:ambientes,id',
            'id_aula_gestion' => 'nullable|integer|exists:aulas_gestion,id',
            'id_distribucion_detalle' => 'nullable|integer|exists:distribucion_ambiente_detalles,id',
            'turno' => 'nullable|in:manana,tarde,noche',
            'estado' => 'nullable|in:asignado,confirmado,ausente,reasignado',
            'observaciones' => 'nullable|string',
        ]);

        $asignacion->update($data);
        $asignacion->load([
            'cargo:id,nombre',
            'participante:id,dni,nombres,paterno,materno,puesto,unidad',
        ]);

        return $this->successResponse(
            $this->formatPersonal($asignacion),
            'Asignación actualizada correctamente'
        );
    }

    /**
     * Eliminar una asignación (liberar al personal).
     */
    public function destroy(int $idDistribucion, int $id): JsonResponse
    {
        $asignacion = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->where('id', $id)
            ->first();

        if (!$asignacion) {
            return $this->errorResponse('Asignación no encontrada', 404);
        }

        $asignacion->delete();

        return $this->successResponse(null, 'Personal liberado correctamente');
    }

    /**
     * Listar participantes disponibles (no asignados a esta distribución).
     */
    public function disponibles(int $idDistribucion, Request $request): JsonResponse
    {
        $request->validate([
            'id_proceso' => 'required|integer',
        ]);

        $idProceso = $request->input('id_proceso');

        $yaAsignados = AsignacionPersonal::where('id_distribucion', $idDistribucion)
            ->pluck('id_participante')
            ->toArray();

        $disponibles = Participante::where('id_proceso', $idProceso)
            ->whereNotIn('id', $yaAsignados)
            ->get(['id', 'dni', 'nombres', 'paterno', 'materno', 'puesto', 'unidad']);

        return $this->successResponse($disponibles);
    }

    /**
     * Listar cargos disponibles.
     */
    public function cargos(): JsonResponse
    {
        $cargos = Cargo::where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion']);

        return $this->successResponse($cargos);
    }

    // ===== MÉTODOS PRIVADOS =====

    private function isVigilante($cargo): bool
    {
        if (!$cargo) return false;
        $nombre = strtolower(trim($cargo->nombre));
        return $nombre === 'vigilante' || str_contains($nombre, 'vigilante');
    }

    private function isSupervisor($cargo): bool
    {
        if (!$cargo) return false;
        $nombre = strtolower(trim($cargo->nombre));
        return $nombre === 'supervisor' || str_contains($nombre, 'supervisor');
    }

    private function isCoordinador($cargo): bool
    {
        if (!$cargo) return false;
        $nombre = strtolower(trim($cargo->nombre));
        return $nombre === 'coordinador' || str_contains($nombre, 'coordinador');
    }

    private function formatPersonal($p): array
    {
        return [
            'id' => $p->id,
            'id_cargo' => $p->id_cargo,
            'cargo_nombre' => $p->cargo?->nombre ?? '',
            'id_ambiente' => $p->id_ambiente,
            'id_aula_gestion' => $p->id_aula_gestion,
            'turno' => $p->turno,
            'estado' => $p->estado,
            'observaciones' => $p->observaciones,
            'participante' => $p->participante ? [
                'id' => $p->participante->id,
                'dni' => $p->participante->dni,
                'nombres' => $p->participante->nombres,
                'paterno' => $p->participante->paterno,
                'materno' => $p->participante->materno,
                'puesto' => $p->participante->puesto,
                'unidad' => $p->participante->unidad,
            ] : null,
        ];
    }
}
