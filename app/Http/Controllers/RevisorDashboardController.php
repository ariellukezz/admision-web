<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\ControlBiometrico;
use App\Models\Documento;
use DB;

class RevisorDashboardController extends Controller
{
    public function resumen()
    {
        $proceso = auth()->user()->id_proceso;
        $hoy = now()->toDateString();

        // Inscripciones del proceso
        $inscritos = Inscripcion::where('estado', 0)
            ->where('id_proceso', $proceso)
            ->count();

        $inscritosHoy = Inscripcion::where('estado', 0)
            ->where('id_proceso', $proceso)
            ->whereDate('created_at', $hoy)
            ->count();

        // Preinscritos
        $preinscritos = DB::table('pre_inscripcion')
            ->where('id_proceso', $proceso)
            ->count();

        $preinscritosHoy = DB::table('pre_inscripcion')
            ->where('id_proceso', $proceso)
            ->whereDate('created_at', $hoy)
            ->count();

        // Biométrico
        $biometricos = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->count();

        $biometricosHoy = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->whereDate('created_at', $hoy)
            ->count();

        // Documentos por verificar
        $documentosPendientes = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 0)
            ->count();

        $documentosVerificados = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->count();

        // Comprobantes por verificar
        $comprobantesPendientes = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 0)
            ->count();

        $comprobantesVerificados = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $proceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->count();

        return response()->json([
            'success' => true,
            'datos' => [
                'inscritos'              => $inscritos,
                'inscritos_hoy'          => $inscritosHoy,
                'preinscritos'           => $preinscritos,
                'preinscritos_hoy'       => $preinscritosHoy,
                'biometricos'            => $biometricos,
                'biometricos_hoy'        => $biometricosHoy,
                'documentos_pendientes'  => $documentosPendientes,
                'documentos_verificados' => $documentosVerificados,
                'comprobantes_pendientes'  => $comprobantesPendientes,
                'comprobantes_verificados' => $comprobantesVerificados,
            ],
        ]);
    }

    public function inscripcionesPorPrograma()
    {
        $proceso = auth()->user()->id_proceso;

        $datos = Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->selectRaw('programa.nombre, programa.area, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $proceso)
            ->groupBy('programa.id', 'programa.nombre', 'programa.area')
            ->orderByDesc('cant')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'datos'  => $datos,
        ]);
    }

    public function inscripcionesPorArea()
    {
        $proceso = auth()->user()->id_proceso;

        $datos = Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->selectRaw('programa.area, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $proceso)
            ->groupBy('programa.area')
            ->orderByDesc('cant')
            ->get();

        return response()->json([
            'success' => true,
            'datos'  => $datos,
        ]);
    }

    public function generoPorArea()
    {
        $proceso = auth()->user()->id_proceso;

        $datos = Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
            ->selectRaw('programa.area, postulante.sexo, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $proceso)
            ->groupBy('programa.area', 'postulante.sexo')
            ->orderBy('programa.area')
            ->get();

        return response()->json([
            'success' => true,
            'datos'  => $datos,
        ]);
    }

    public function timelineInscripciones()
    {
        $proceso = auth()->user()->id_proceso;

        $datos = Inscripcion::selectRaw("DATE(created_at) as fecha, COUNT(*) as cant")
            ->where('estado', 0)
            ->where('id_proceso', $proceso)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'datos'  => $datos,
        ]);
    }

    public function biometricoResumen()
    {
        $proceso = auth()->user()->id_proceso;

        $totalInscritos = Inscripcion::where('estado', 0)
            ->where('id_proceso', $proceso)
            ->count();

        $conBiometrico = DB::table('control_biometrico')
            ->where('id_proceso', $proceso)
            ->count();

        $sinBiometrico = max(0, $totalInscritos - $conBiometrico);

        $porArea = DB::table('control_biometrico as cb')
            ->join('inscripciones as i', function ($join) use ($proceso) {
                $join->on('i.id_postulante', '=', 'cb.id_postulante')
                    ->where('i.estado', 0)
                    ->where('i.id_proceso', $proceso);
            })
            ->join('programa as p', 'p.id', '=', 'i.id_programa')
            ->selectRaw('p.area, COUNT(DISTINCT cb.id) as cant')
            ->where('cb.id_proceso', $proceso)
            ->groupBy('p.area')
            ->orderByDesc('cant')
            ->get();

        return response()->json([
            'success' => true,
            'datos' => [
                'total_inscritos'  => $totalInscritos,
                'con_biometrico'  => $conBiometrico,
                'sin_biometrico'  => $sinBiometrico,
                'porcentaje'      => $totalInscritos > 0 ? round(($conBiometrico / $totalInscritos) * 100, 1) : 0,
                'por_area'        => $porArea,
            ],
        ]);
    }

    public function modalidadDistribucion()
    {
        $proceso = auth()->user()->id_proceso;

        $datos = Inscripcion::join('modalidad', 'modalidad.id', '=', 'inscripciones.id_modalidad')
            ->selectRaw('modalidad.nombre, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $proceso)
            ->groupBy('modalidad.id', 'modalidad.nombre')
            ->orderByDesc('cant')
            ->get();

        return response()->json([
            'success' => true,
            'datos'  => $datos,
        ]);
    }

    public function verificacionesPendientes()
    {
        $proceso = auth()->user()->id_proceso;

        // Postulantes con documentos sin verificar
        $sinDocumento = DB::table('inscripciones as i')
            ->leftJoin('documento as d', function ($join) {
                $join->on('d.id_postulante', '=', 'i.id_postulante')
                    ->where('d.verificado', '=', 1);
            })
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->selectRaw('p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, "documento" as tipo')
            ->where('i.estado', 0)
            ->where('i.id_proceso', $proceso)
            ->whereNull('d.id')
            ->limit(10)
            ->get();

        // Postulantes con comprobantes sin verificar
        $sinComprobante = DB::table('inscripciones as i')
            ->leftJoin('comprobante as c', function ($join) {
                $join->on('c.ndoc_postulante', '=', DB::raw('CAST(i.id_postulante AS CHAR)'))
                    ->where('c.verificado', '=', 1);
            })
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->selectRaw('p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, "comprobante" as tipo')
            ->where('i.estado', 0)
            ->where('i.id_proceso', $proceso)
            ->whereNull('c.id')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'datos' => [
                'sin_documento'   => $sinDocumento,
                'sin_comprobante' => $sinComprobante,
            ],
        ]);
    }
}
