<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\ControlBiometrico;
use App\Models\Documento;
use App\Models\Cambio;
use DB;

class RevisorPersonalController extends Controller
{
    /**
     * KPIs personales del revisor autenticado
     */
    public function resumen()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        $hoy = now()->toDateString();

        // Documentos verificados por mí
        $docsVerificados = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->where('d.id_usuario', $userId)
            ->count();

        $docsVerificadosHoy = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->where('d.id_usuario', $userId)
            ->whereDate('d.updated_at', $hoy)
            ->count();

        // Comprobantes verificados por mí
        $compVerificados = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->where('c.id_usuario', $userId)
            ->count();

        $compVerificadosHoy = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->where('c.id_usuario', $userId)
            ->whereDate('c.updated_at', $hoy)
            ->count();

        // Controles biométricos por mí
        $biometricos = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->where('id_usuario', $userId)
            ->count();

        $biometricosHoy = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->where('id_usuario', $userId)
            ->whereDate('created_at', $hoy)
            ->count();

        // Inscripciones procesadas por mí
        $inscripciones = Inscripcion::where('id_proceso', $proceso)
            ->where('estado', 0)
            ->where('id_usuario', $userId)
            ->count();

        $inscripcionesHoy = Inscripcion::where('id_proceso', $proceso)
            ->where('estado', 0)
            ->where('id_usuario', $userId)
            ->whereDate('created_at', $hoy)
            ->count();

        // Totales del proceso (para contexto)
        $totalDocsPendientes = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 0)
            ->count();

        $totalCompPendientes = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 0)
            ->count();

        return response()->json([
            'success' => true,
            'datos' => [
                'docs_verificados'       => $docsVerificados,
                'docs_verificados_hoy'   => $docsVerificadosHoy,
                'comp_verificados'       => $compVerificados,
                'comp_verificados_hoy'   => $compVerificadosHoy,
                'biometricos'            => $biometricos,
                'biometricos_hoy'        => $biometricosHoy,
                'inscripciones'          => $inscripciones,
                'inscripciones_hoy'      => $inscripcionesHoy,
                'total_docs_pendientes'  => $totalDocsPendientes,
                'total_comp_pendientes'  => $totalCompPendientes,
            ],
        ]);
    }

    /**
     * Línea de tiempo de actividad del revisor (últimos 30 días)
     */
    public function timeline()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;

        // Documentos verificados por día
        $docs = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->selectRaw("DATE(d.updated_at) as fecha, COUNT(*) as cant")
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->where('d.id_usuario', $userId)
            ->whereDate('d.updated_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(d.updated_at)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Comprobantes verificados por día
        $comps = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->selectRaw("DATE(c.updated_at) as fecha, COUNT(*) as cant")
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->where('c.id_usuario', $userId)
            ->whereDate('c.updated_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(c.updated_at)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Biometricos por día
        $bios = DB::table('control_biometrico')
            ->selectRaw("DATE(created_at) as fecha, COUNT(*) as cant")
            ->where('id_proceso', $proceso)
            ->where('id_usuario', $userId)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // Combinar todas las fechas
        $fechas = collect()
            ->merge($docs->keys())
            ->merge($comps->keys())
            ->merge($bios->keys())
            ->unique()
            ->sort()
            ->values();

        $timeline = $fechas->map(function ($fecha) use ($docs, $comps, $bios) {
            return [
                'fecha'     => $fecha,
                'docs'      => $docs->get($fecha)?->cant ?? 0,
                'comps'     => $comps->get($fecha)?->cant ?? 0,
                'bios'      => $bios->get($fecha)?->cant ?? 0,
                'total'     => ($docs->get($fecha)?->cant ?? 0) + ($comps->get($fecha)?->cant ?? 0) + ($bios->get($fecha)?->cant ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'datos'   => $timeline,
        ]);
    }

    /**
     * Últimas acciones del revisor
     */
    public function accionesRecientes()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;
        $acciones = collect();

        // Documentos verificados recientemente
        $docs = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->join('postulante as p', 'p.id', '=', 'd.id_postulante')
            ->join('tipo_documento as td', 'td.id', '=', 'd.id_tipo_documento')
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, td.nombre as detalle, d.updated_at as fecha, 'Documento' as tipo")
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->where('d.id_usuario', $userId)
            ->orderByDesc('d.updated_at')
            ->limit(5)
            ->get();

        // Comprobantes verificados recientemente
        $comps = DB::table('comprobante as c')
            ->join('postulante as p', 'p.id', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, CONCAT('Voucher ', c.nro_operacion) as detalle, c.updated_at as fecha, 'Comprobante' as tipo")
            ->where('c.verificado', 1)
            ->where('c.id_usuario', $userId)
            ->orderByDesc('c.updated_at')
            ->limit(5)
            ->get();

        // Controles biométricos recientes
        $bios = DB::table('control_biometrico as cb')
            ->join('postulante as p', 'p.id', '=', 'cb.id_postulante')
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, cb.codigo_ingreso as detalle, cb.created_at as fecha, 'Biométrico' as tipo")
            ->where('cb.id_proceso', $proceso)
            ->where('cb.id_usuario', $userId)
            ->orderByDesc('cb.created_at')
            ->limit(5)
            ->get();

        // Inscripciones recientes
        $inscs = Inscripcion::join('postulante as p', 'p.id', '=', 'inscripciones.id_postulante')
            ->join('programa as pr', 'pr.id', '=', 'inscripciones.id_programa')
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, pr.nombre as detalle, inscripciones.created_at as fecha, 'Inscripción' as tipo")
            ->where('inscripciones.id_proceso', $proceso)
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_usuario', $userId)
            ->orderByDesc('inscripciones.created_at')
            ->limit(5)
            ->get();

        $acciones = $docs->merge($comps)->merge($bios)->merge($inscs)
            ->sortByDesc('fecha')
            ->take(15)
            ->values();

        return response()->json([
            'success' => true,
            'datos'   => $acciones,
        ]);
    }

    /**
     * Distribución de actividad del revisor por tipo
     */
    public function distribucionActividad()
    {
        $userId = auth()->id();
        $proceso = auth()->user()->id_proceso;

        $docs = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->where('d.id_usuario', $userId)
            ->count();

        $comps = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->where('c.id_usuario', $userId)
            ->count();

        $bios = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->where('id_usuario', $userId)
            ->count();

        $inscs = Inscripcion::where('id_proceso', $proceso)
            ->where('estado', 0)
            ->where('id_usuario', $userId)
            ->count();

        return response()->json([
            'success' => true,
            'datos'   => [
                ['tipo' => 'Documentos', 'cant' => $docs],
                ['tipo' => 'Comprobantes', 'cant' => $comps],
                ['tipo' => 'Biométrico', 'cant' => $bios],
                ['tipo' => 'Inscripciones', 'cant' => $inscs],
            ],
        ]);
    }

    /**
     * Ranking de revisores del proceso
     */
    public function ranking()
    {
        $proceso = auth()->user()->id_proceso;
        $userId = auth()->id();

        // Obtener todos los revisores (rol=2) del proceso
        $revisores = DB::table('users')
            ->where('id_proceso', $proceso)
            ->where('id_rol', 2)
            ->select('id', 'name', 'paterno')
            ->get();

        $ranking = $revisores->map(function ($revisor) use ($proceso) {
            $docs = DB::table('documento as d')
                ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
                ->where('i.id_proceso', $proceso)
                ->where('i.estado', 0)
                ->where('d.verificado', 1)
                ->where('d.id_usuario', $revisor->id)
                ->count();

            $comps = DB::table('comprobante as c')
                ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
                ->where('i.id_proceso', $proceso)
                ->where('i.estado', 0)
                ->where('c.verificado', 1)
                ->where('c.id_usuario', $revisor->id)
                ->count();

            $bios = DB::table('control_biometrico')
                ->where('id_proceso', $proceso)
                ->where('id_usuario', $revisor->id)
                ->count();

            $inscs = Inscripcion::where('id_proceso', $proceso)
                ->where('estado', 0)
                ->where('id_usuario', $revisor->id)
                ->count();

            return [
                'id'           => $revisor->id,
                'nombre'       => trim($revisor->name . ' ' . ($revisor->paterno ?? '')),
                'docs'         => $docs,
                'comps'        => $comps,
                'bios'         => $bios,
                'inscs'        => $inscs,
                'total'        => $docs + $comps + $bios + $inscs,
                'es_yo'        => $revisor->id == auth()->id(),
            ];
        })->sortByDesc('total')->values();

        return response()->json([
            'success' => true,
            'datos'   => $ranking,
        ]);
    }

    /**
     * Postulantes con documentos/comprobantes pendientes de verificar
     */
    public function pendientes()
    {
        $proceso = auth()->user()->id_proceso;

        // Documentos sin verificar
        $docsPendientes = DB::table('inscripciones as i')
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->join('programa as pr', 'pr.id', '=', 'i.id_programa')
            ->join('documento as d', 'd.id_postulante', '=', 'i.id_postulante')
            ->join('tipo_documento as td', 'td.id', '=', 'd.id_tipo_documento')
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, pr.nombre as programa, td.nombre as tipo_doc, d.updated_at")
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 0)
            ->orderByDesc('d.updated_at')
            ->limit(10)
            ->get();

        // Comprobantes sin verificar
        $compsPendientes = DB::table('inscripciones as i')
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->join('programa as pr', 'pr.id', '=', 'i.id_programa')
            ->join('comprobante as c', 'c.ndoc_postulante', '=', DB::raw('CAST(i.id_postulante AS CHAR)'))
            ->selectRaw("p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, pr.nombre as programa, c.nro_operacion, c.monto, c.updated_at")
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 0)
            ->orderByDesc('c.updated_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'datos'   => [
                'docs_pendientes'  => $docsPendientes,
                'comps_pendientes' => $compsPendientes,
            ],
        ]);
    }
}
