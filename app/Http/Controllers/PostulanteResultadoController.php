<?php

namespace App\Http\Controllers;

use App\Models\Ingresante;
use App\Models\Postulante;
use App\Models\Proceso;
use App\Models\Programa;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostulanteResultadoController extends Controller
{
    /**
     * Vista principal de Resultados
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $miDni = $user->dni;

        // Mis resultados con joins
        $misResultados = [];
        if ($miDni) {
            $misResultados = Ingresante::where('resultados.dni_postulante', $miDni)
                ->leftJoin('programa', 'resultados.programa', '=', 'programa.id')
                ->leftJoin('modalidad', 'resultados.modalidad', '=', 'modalidad.id')
                ->select(
                    'resultados.id',
                    'resultados.id_proceso',
                    'resultados.paterno',
                    'resultados.materno',
                    'resultados.nombres',
                    'resultados.modalidad as id_modalidad',
                    'resultados.programa as id_programa',
                    'resultados.puntaje',
                    'resultados.puesto',
                    'resultados.puesto_general',
                    'resultados.apto',
                    'programa.nombre as programa_nombre',
                    'modalidad.nombre as modalidad_nombre',
                )
                ->get()
                ->map(function ($r) {
                    $proceso = Proceso::find($r->id_proceso);
                    return [
                        'id' => $r->id,
                        'id_proceso' => $r->id_proceso,
                        'proceso' => $proceso?->nombre ?? '—',
                        'anio' => $proceso?->anio ?? '—',
                        'nombres' => trim("{$r->paterno} {$r->materno} {$r->nombres}"),
                        'id_modalidad' => $r->id_modalidad,
                        'modalidad_nombre' => $r->modalidad_nombre ?? '—',
                        'id_programa' => $r->id_programa,
                        'programa_nombre' => $r->programa_nombre ?? '—',
                        'puntaje' => $r->puntaje,
                        'puesto' => $r->puesto,
                        'puesto_general' => $r->puesto_general,
                        'apto' => $r->apto,
                    ];
                })->toArray();
        }

        // Procesos agrupados por año
        $procesos = Proceso::where('estado', 1)
            ->select('id', 'nombre', 'slug', 'anio', 'estado')
            ->orderByDesc('anio')
            ->orderByDesc('id')
            ->get()
            ->groupBy('anio')
            ->map(fn($items, $anio) => [
                'anio' => $anio,
                'procesos' => $items->values()->toArray(),
            ])
            ->values()
            ->toArray();

        // Filtros: programas y modalidades disponibles
        $programas = Programa::where('estado', 1)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get()
            ->toArray();

        $modalidades = Modalidad::where('estado', 1)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get()
            ->toArray();

        // Consultas restantes de otros DNI (máx 5 por sesión diaria)
        $hoy = now()->toDateString();
        $consultaKey = "consultas_resultados_{$user->id}_{$hoy}";
        $consultasHoy = session($consultaKey, 0);
        $consultasRestantes = max(0, 100 - $consultasHoy);

        return Inertia::render('Postulante/Resultados', [
            'miDni' => $miDni,
            'misResultados' => $misResultados,
            'procesos' => $procesos,
            'programas' => $programas,
            'modalidades' => $modalidades,
            'consultasRestantes' => $consultasRestantes,
        ]);
    }

    /**
     * API: Resultados de un proceso (lista de ingresantes) con joins y filtros
     */
    public function getResultadosProceso(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|integer|exists:procesos,id',
        ]);

        $query = Ingresante::where('resultados.id_proceso', $request->id_proceso)
            ->leftJoin('programa', 'resultados.programa', '=', 'programa.id')
            ->leftJoin('modalidad', 'resultados.modalidad', '=', 'modalidad.id')
            ->select(
                'resultados.id',
                'resultados.dni_postulante',
                'resultados.paterno',
                'resultados.materno',
                'resultados.nombres',
                'resultados.modalidad as id_modalidad',
                'resultados.programa as id_programa',
                'resultados.puntaje',
                'resultados.puesto',
                'resultados.puesto_general',
                'resultados.apto',
                'programa.nombre as programa_nombre',
                'modalidad.nombre as modalidad_nombre',
            )
            ->orderBy('puesto_general');

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('resultados.dni_postulante', 'LIKE', "%{$search}%")
                  ->orWhere('resultados.paterno', 'LIKE', "%{$search}%")
                  ->orWhere('resultados.materno', 'LIKE', "%{$search}%")
                  ->orWhere('resultados.nombres', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por programa
        if ($request->filled('id_programa')) {
            $query->where('resultados.programa', $request->id_programa);
        }

        // Filtro por modalidad
        if ($request->filled('id_modalidad')) {
            $query->where('resultados.modalidad', $request->id_modalidad);
        }

        $resultados = $query->paginate(50);

        // Incrementar contador de consultas
        $user = $request->user();
        $miDni = $user->dni;
        $hoy = now()->toDateString();
        $consultaKey = "consultas_resultados_{$user->id}_{$hoy}";

        $consultasHoy = session($consultaKey, 0);
        if ($consultasHoy >= 100) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Has alcanzado el límite de 100 consultas diarias a resultados.',
            ], 429);
        }
        session([$consultaKey => $consultasHoy + 1]);

        // Ocultar DNI completo de otros (solo mostrar los 3 primeros dígitos)
        $resultados->getCollection()->transform(function ($r) use ($miDni) {
            if ($r->dni_postulante !== $miDni && $r->dni_postulante) {
                $r->dni_postulante = substr($r->dni_postulante, 0, 3) . '*****';
            }
            return $r;
        });

        return response()->json([
            'estado' => true,
            'datos' => $resultados,
        ]);
    }

    /**
     * API: Rendimiento del postulante vs ingresantes
     */
    public function getMiRendimiento(Request $request)
    {
        $user = $request->user();
        $miDni = $user->dni;

        if (!$miDni) {
            return response()->json(['estado' => false, 'mensaje' => 'No tienes DNI registrado']);
        }

        $misResultados = Ingresante::where('dni_postulante', $miDni)
            ->leftJoin('programa', 'resultados.programa', '=', 'programa.id')
            ->leftJoin('modalidad', 'resultados.modalidad', '=', 'modalidad.id')
            ->select(
                'resultados.*',
                'programa.nombre as programa_nombre',
                'modalidad.nombre as modalidad_nombre',
            )
            ->get();

        if ($misResultados->isEmpty()) {
            return response()->json(['estado' => true, 'datos' => []]);
        }

        $rendimiento = $misResultados->map(function ($mio) {
            $proceso = Proceso::find($mio->id_proceso);

            // Total ingresantes en el mismo proceso y modalidad
            $totalMismaModalidad = Ingresante::where('id_proceso', $mio->id_proceso)
                ->where('modalidad', $mio->modalidad)
                ->count();

            // Promedio puntaje misma modalidad
            $promedioMismaModalidad = Ingresante::where('id_proceso', $mio->id_proceso)
                ->where('modalidad', $mio->modalidad)
                ->avg('puntaje');

            // Puntaje más alto y más bajo
            $maxPuntaje = Ingresante::where('id_proceso', $mio->id_proceso)
                ->where('modalidad', $mio->modalidad)
                ->max('puntaje');

            $minPuntaje = Ingresante::where('id_proceso', $mio->id_proceso)
                ->where('modalidad', $mio->modalidad)
                ->min('puntaje');

            return [
                'id_proceso' => $mio->id_proceso,
                'proceso' => $proceso?->nombre ?? '—',
                'anio' => $proceso?->anio ?? '—',
                'id_modalidad' => $mio->modalidad,
                'modalidad_nombre' => $mio->modalidad_nombre ?? '—',
                'id_programa' => $mio->programa,
                'programa_nombre' => $mio->programa_nombre ?? '—',
                'mi_puntaje' => $mio->puntaje,
                'mi_puesto' => $mio->puesto,
                'mi_puesto_general' => $mio->puesto_general,
                'apto' => $mio->apto,
                'total_ingresantes_modalidad' => $totalMismaModalidad,
                'promedio_modalidad' => round($promedioMismaModalidad ?? 0, 2),
                'puntaje_max_modalidad' => $maxPuntaje,
                'puntaje_min_modalidad' => $minPuntaje,
                'percentil' => $totalMismaModalidad > 0
                    ? round((1 - ($mio->puesto / $totalMismaModalidad)) * 100, 1)
                    : 0,
            ];
        });

        return response()->json([
            'estado' => true,
            'datos' => $rendimiento,
        ]);
    }
}
