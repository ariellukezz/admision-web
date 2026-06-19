<?php

namespace App\Services\Revisor;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

class RevisorDashboardService
{
    public function resumen(int $idProceso): array
    {
        $hoy = now()->toDateString();

        $inscritos = Inscripcion::where('estado', 0)
            ->where('id_proceso', $idProceso)
            ->count();

        $inscritosHoy = Inscripcion::where('estado', 0)
            ->where('id_proceso', $idProceso)
            ->whereDate('created_at', $hoy)
            ->count();

        $preinscritos = DB::table('pre_inscripcion')
            ->where('id_proceso', $idProceso)
            ->count();

        $preinscritosHoy = DB::table('pre_inscripcion')
            ->where('id_proceso', $idProceso)
            ->whereDate('created_at', $hoy)
            ->count();

        $biometricos = DB::table('control_biometrico')
            ->where('id_proceso', $idProceso)
            ->count();

        $biometricosHoy = DB::table('control_biometrico')
            ->where('id_proceso', $idProceso)
            ->whereDate('created_at', $hoy)
            ->count();

        $documentosPendientes = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $idProceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 0)
            ->count();

        $documentosVerificados = DB::table('documento as d')
            ->join('inscripciones as i', 'i.id_postulante', '=', 'd.id_postulante')
            ->where('i.id_proceso', $idProceso)
            ->where('i.estado', 0)
            ->where('d.verificado', 1)
            ->count();

        $comprobantesPendientes = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $idProceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 0)
            ->count();

        $comprobantesVerificados = DB::table('comprobante as c')
            ->join('inscripciones as i', 'i.id_postulante', '=', DB::raw('CAST(c.ndoc_postulante AS UNSIGNED)'))
            ->where('i.id_proceso', $idProceso)
            ->where('i.estado', 0)
            ->where('c.verificado', 1)
            ->count();

        return [
            'inscritos'                => $inscritos,
            'inscritos_hoy'            => $inscritosHoy,
            'preinscritos'             => $preinscritos,
            'preinscritos_hoy'         => $preinscritosHoy,
            'biometricos'              => $biometricos,
            'biometricos_hoy'          => $biometricosHoy,
            'documentos_pendientes'    => $documentosPendientes,
            'documentos_verificados'   => $documentosVerificados,
            'comprobantes_pendientes'  => $comprobantesPendientes,
            'comprobantes_verificados' => $comprobantesVerificados,
        ];
    }

    public function inscripcionesPorPrograma(int $idProceso)
    {
        return Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->selectRaw('programa.nombre, programa.area, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $idProceso)
            ->groupBy('programa.id', 'programa.nombre', 'programa.area')
            ->orderByDesc('cant')
            ->limit(12)
            ->get();
    }

    public function inscripcionesPorArea(int $idProceso)
    {
        return Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->selectRaw('programa.area, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $idProceso)
            ->groupBy('programa.area')
            ->orderByDesc('cant')
            ->get();
    }

    public function generoPorArea(int $idProceso)
    {
        return Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
            ->selectRaw('programa.area, postulante.sexo, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $idProceso)
            ->groupBy('programa.area', 'postulante.sexo')
            ->orderBy('programa.area')
            ->get();
    }

    public function timelineInscripciones(int $idProceso)
    {
        return Inscripcion::selectRaw("DATE(created_at) as fecha, COUNT(*) as cant")
            ->where('estado', 0)
            ->where('id_proceso', $idProceso)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();
    }

    public function biometricoResumen(int $idProceso): array
    {
        $totalInscritos = Inscripcion::where('estado', 0)
            ->where('id_proceso', $idProceso)
            ->count();

        $conBiometrico = DB::table('control_biometrico')
            ->where('id_proceso', $idProceso)
            ->count();

        $sinBiometrico = max(0, $totalInscritos - $conBiometrico);

        $porArea = DB::table('control_biometrico as cb')
            ->join('inscripciones as i', function ($join) use ($idProceso) {
                $join->on('i.id_postulante', '=', 'cb.id_postulante')
                    ->where('i.estado', 0)
                    ->where('i.id_proceso', $idProceso);
            })
            ->join('programa as p', 'p.id', '=', 'i.id_programa')
            ->selectRaw('p.area, COUNT(DISTINCT cb.id) as cant')
            ->where('cb.id_proceso', $idProceso)
            ->groupBy('p.area')
            ->orderByDesc('cant')
            ->get();

        return [
            'total_inscritos' => $totalInscritos,
            'con_biometrico'  => $conBiometrico,
            'sin_biometrico'  => $sinBiometrico,
            'porcentaje'      => $totalInscritos > 0 ? round(($conBiometrico / $totalInscritos) * 100, 1) : 0,
            'por_area'        => $porArea,
        ];
    }

    public function modalidadDistribucion(int $idProceso)
    {
        return Inscripcion::join('modalidad', 'modalidad.id', '=', 'inscripciones.id_modalidad')
            ->selectRaw('modalidad.nombre, COUNT(*) as cant')
            ->where('inscripciones.estado', 0)
            ->where('inscripciones.id_proceso', $idProceso)
            ->groupBy('modalidad.id', 'modalidad.nombre')
            ->orderByDesc('cant')
            ->get();
    }

    public function verificacionesPendientes(int $idProceso): array
    {
        $sinDocumento = DB::table('inscripciones as i')
            ->leftJoin('documento as d', function ($join) {
                $join->on('d.id_postulante', '=', 'i.id_postulante')
                    ->where('d.verificado', '=', 1);
            })
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->selectRaw('p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, "documento" as tipo')
            ->where('i.estado', 0)
            ->where('i.id_proceso', $idProceso)
            ->whereNull('d.id')
            ->limit(10)
            ->get();

        $sinComprobante = DB::table('inscripciones as i')
            ->leftJoin('comprobante as c', function ($join) {
                $join->on('c.ndoc_postulante', '=', DB::raw('CAST(i.id_postulante AS CHAR)'))
                    ->where('c.verificado', '=', 1);
            })
            ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
            ->selectRaw('p.nro_doc as dni, p.nombres, p.primer_apellido as paterno, p.segundo_apellido as materno, "comprobante" as tipo')
            ->where('i.estado', 0)
            ->where('i.id_proceso', $idProceso)
            ->whereNull('c.id')
            ->limit(10)
            ->get();

        return [
            'sin_documento'   => $sinDocumento,
            'sin_comprobante' => $sinComprobante,
        ];
    }
}
