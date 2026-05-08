<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;  
use App\Models\Postulante;
use App\Models\Colegio;
use App\Models\ControlBiometrico;
use App\Models\Proceso;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use DB;

class DashboardController extends Controller
{

  #Pre inscritos 
  public function preinscritos(){
    $preinscritos = DB::table('pre_inscripcion')
    ->where('id_proceso','=',auth()->user()->id_proceso)
    ->count();

    $lastRegistro = Preinscripcion::selectRaw('COUNT(*) as count, DATE(created_at) as date')
    ->whereNotNull('created_at')
    ->where('id_proceso','=',auth()->user()->id_proceso)
    ->groupBy(DB::raw('DATE(created_at)'))
    ->orderByDesc(DB::raw('DATE(created_at)'))
    ->first();

    $this->response['fecha'] = $lastRegistro;
    $this->response['preinscritos'] = $preinscritos;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function inscritos(){
    $inscritos = DB::table('inscripciones')
    ->where('estado','=',0)
    ->where('id_proceso','=',auth()->user()->id_proceso)
    ->count();

    $lastRegistro = Inscripcion::selectRaw('COUNT(*) as count, DATE(created_at) as date')
    ->whereNotNull('created_at')
    ->where('id_proceso','=',auth()->user()->id_proceso)
    ->groupBy(DB::raw('DATE(created_at)'))
    ->orderByDesc(DB::raw('DATE(created_at)'))
    ->first();

    $this->response['fecha'] = $lastRegistro;
    $this->response['inscritos'] = $inscritos;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function mInscriptores(){
    $minscriptores = Inscripcion::selectRaw('COUNT(*) as cant, users.name, users.paterno, users.materno')
    ->join('users','inscripciones.id_usuario','users.id')
    ->where('inscripciones.estado','=',0)
    ->where('inscripciones.id_proceso','=',auth()->user()->id_proceso)
    ->groupBy(DB::raw('users.id'))
    ->orderByDesc(DB::raw('cant'))
    ->limit(4)
    ->get();

    $this->response['inscriptores'] = $minscriptores;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function mInscriptoresDia(Request $request){
    $mins = Inscripcion::selectRaw('COUNT(*) as cant, users.name, users.paterno, users.materno')
    ->join('users','inscripciones.id_usuario','users.id')
    ->where('inscripciones.estado','=',0)
    ->where('inscripciones.id_proceso','=',auth()->user()->id_proceso)
    ->groupBy(DB::raw('users.id'))
    ->orderBy('cant','asc')
    ->having('cant','>',5)
    ->limit(5)
    ->get()
    ->reverse()
    ->values();

    $this->response['inscriptores'] = $mins;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  // ─── DASHBOARD API ────────────────────────────────────────────────

  public function resumenGeneral()
  {
    $proceso = auth()->user()->id_proceso;

    $preinscritos = DB::table('pre_inscripcion')
      ->where('id_proceso', $proceso)
      ->count();

    $inscritos = DB::table('inscripciones')
      ->where('estado', 0)
      ->where('id_proceso', $proceso)
      ->count();

    $biometricos = DB::table('control_biometrico')
      ->where('id_proceso', $proceso)
      ->count();

    $postulantes = DB::table('postulante')
      ->join('inscripciones', 'inscripciones.id_postulante', '=', 'postulante.id')
      ->where('inscripciones.estado', 0)
      ->where('inscripciones.id_proceso', $proceso)
      ->count();

    $hoy = now()->toDateString();
    $inscritosHoy = DB::table('inscripciones')
      ->where('estado', 0)
      ->where('id_proceso', $proceso)
      ->whereDate('created_at', $hoy)
      ->count();

    $preinscritosHoy = DB::table('pre_inscripcion')
      ->where('id_proceso', $proceso)
      ->whereDate('created_at', $hoy)
      ->count();

    $biometricosHoy = DB::table('control_biometrico')
      ->where('id_proceso', $proceso)
      ->whereDate('created_at', $hoy)
      ->count();

    return response()->json([
      'success' => true,
      'datos' => [
        'preinscritos'      => $preinscritos,
        'inscritos'         => $inscritos,
        'biometricos'       => $biometricos,
        'postulantes'       => $postulantes,
        'preinscritos_hoy'  => $preinscritosHoy,
        'inscritos_hoy'     => $inscritosHoy,
        'biometricos_hoy'   => $biometricosHoy,
      ],
    ]);
  }

  public function postulantesPorArea()
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

  public function inscritosPorPrograma()
  {
    $proceso = auth()->user()->id_proceso;

    $datos = Inscripcion::join('programa', 'programa.id', '=', 'inscripciones.id_programa')
      ->selectRaw('programa.nombre, programa.area, COUNT(*) as cant')
      ->where('inscripciones.estado', 0)
      ->where('inscripciones.id_proceso', $proceso)
      ->groupBy('programa.id', 'programa.nombre', 'programa.area')
      ->orderByDesc('cant')
      ->limit(15)
      ->get();

    return response()->json([
      'success' => true,
      'datos'  => $datos,
    ]);
  }

  public function timelineInscripciones()
  {
    $idProceso = auth()->user()->id_proceso;

    $datos = Inscripcion::selectRaw("DATE(created_at) as fecha, COUNT(*) as cant")
      ->where('estado', 0)
      ->where('id_proceso', $idProceso)
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

    $totalIngresantes = DB::table('resultados')
      ->where('id_proceso', $proceso)
      ->where('apto', 'SI')
      ->count();

    $conBiometrico = DB::table('control_biometrico')
      ->where('id_proceso', $proceso)
      ->count();

    $sinBiometrico = max(0, $totalIngresantes - $conBiometrico);

    $ingresantesPorArea = DB::table('resultados as r')
      ->join('inscripciones as i', function ($join) use ($proceso) {
        $join->on('i.id_postulante', '=', DB::raw('(SELECT id FROM postulante WHERE nro_doc = r.dni_postulante LIMIT 1)'))
          ->where('i.estado', 0)
          ->where('i.id_proceso', $proceso);
      })
      ->join('programa as p', 'p.id', '=', 'i.id_programa')
      ->where('r.id_proceso', $proceso)
      ->where('r.apto', 'SI')
      ->selectRaw('p.area, COUNT(DISTINCT r.id) as ingresantes')
      ->groupBy('p.area')
      ->orderByDesc('ingresantes')
      ->get();

    $biometricoPorArea = DB::table('control_biometrico as cb')
      ->join('inscripciones as i', function ($join) use ($proceso) {
        $join->on('i.id_postulante', '=', 'cb.id_postulante')
          ->where('i.estado', 0)
          ->where('i.id_proceso', $proceso);
      })
      ->join('programa as p', 'p.id', '=', 'i.id_programa')
      ->where('cb.id_proceso', $proceso)
      ->selectRaw('p.area, COUNT(DISTINCT cb.id) as biometrico')
      ->groupBy('p.area')
      ->orderByDesc('biometrico')
      ->get();

    $areas = $ingresantesPorArea->pluck('area')->merge($biometricoPorArea->pluck('area'))->unique()->values();
    $porArea = $areas->map(function ($area) use ($ingresantesPorArea, $biometricoPorArea) {
      return [
        'area' => $area,
        'ingresantes' => $ingresantesPorArea->firstWhere('area', $area)?->ingresantes ?? 0,
        'biometrico' => $biometricoPorArea->firstWhere('area', $area)?->biometrico ?? 0,
      ];
    });

    $fechasIngreso = DB::table('resultados')
      ->where('id_proceso', $proceso)
      ->where('apto', 'SI')
      ->selectRaw('fecha, COUNT(*) as cant')
      ->groupBy('fecha')
      ->orderBy('fecha')
      ->get()
      ->pluck('fecha')
      ->filter()
      ->values();

    return response()->json([
      'success' => true,
      'datos' => [
        'total_ingresantes' => $totalIngresantes,
        'con_biometrico'   => $conBiometrico,
        'sin_biometrico'   => $sinBiometrico,
        'porcentaje'       => $totalIngresantes > 0 ? round(($conBiometrico / $totalIngresantes) * 100, 1) : 0,
        'por_area'         => $porArea,
        'fechas_ingreso'   => $fechasIngreso,
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

  public function tipoColegioDistribucion()
  {
    $proceso = auth()->user()->id_proceso;

    $datos = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
      ->join('colegios', 'colegios.id', '=', 'postulante.id_colegio')
      ->selectRaw('colegios.gestion as tipo, COUNT(*) as cant')
      ->where('inscripciones.estado', 0)
      ->where('inscripciones.id_proceso', $proceso)
      ->groupBy('colegios.gestion')
      ->orderByDesc('cant')
      ->get();

    return response()->json([
      'success' => true,
      'datos'  => $datos,
    ]);
  }

  // ─── REPORTES VARIOS ──────────────────────────────────────────────

  public function reporteInscritosGenero(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->selectRaw('COUNT(*) AS cant, postulante.sexo')
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('postulante.sexo')
    ->orderByDesc('cant')
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosEdad(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->select(
        DB::raw('COUNT(*) AS cantidad'),
        DB::raw('TIMESTAMPDIFF(YEAR, postulante.fec_nacimiento, CURDATE()) AS edad')
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('edad')
    ->orderByDesc('cantidad','edad')
    ->limit(7)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosResidencia(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('ubigeo', 'postulante.ubigeo_residencia', '=', 'ubigeo.ubigeo')
    ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
    ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
    ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'departamento.nombre AS dep',
        'provincia.nombre AS prov',
        'distritos.nombre AS dist'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('dep', 'prov', 'dist')
    ->orderByDesc('cant')
    ->limit(6)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosProcedencia(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('ubigeo', 'postulante.ubigeo_nacimiento', '=', 'ubigeo.ubigeo')
    ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
    ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
    ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'departamento.nombre AS dep',
        'provincia.nombre AS prov',
        'distritos.nombre AS dist'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('dep', 'prov', 'dist')
    ->orderByDesc('cant')
    ->limit(8)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosEgreso(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'postulante.anio_egreso AS egreso'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('egreso')
    ->orderByDesc('cant')
    ->limit(7)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosDiscapacidad(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'postulante.discapacidad AS discapacidad'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('discapacidad')
    ->orderByDesc('cant')
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosTipoDocumento(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('tipo_documento_identidad', 'postulante.tipo_doc', '=', 'tipo_documento_identidad.id')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'tipo_documento_identidad.nombre AS tipo_doc'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('tipo_documento_identidad.nombre')
    ->orderByDesc('cant')
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosColegio(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('colegios', 'colegios.id', '=', 'postulante.id_colegio')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'colegios.nombre AS cole',
        'colegios.cod_modular'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('colegios.cod_modular', 'colegios.nombre')
    ->orderByDesc('cant')
    ->Limit(7)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosProcedenciaColegio(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('colegios', 'colegios.id', '=', 'postulante.id_colegio')
    ->join('ubigeo', 'colegios.ubigeo', '=', 'ubigeo.ubigeo')
    ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
    ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
    ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'departamento.nombre AS dep',
        'provincia.nombre AS prov',
        'distritos.nombre AS dist'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('dep', 'prov', 'dist')
    ->orderByDesc('cant')
    ->Limit(7)
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function reporteInscritosTipoColegio(Request $request){
    $resultados = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
    ->join('colegios', 'colegios.id', '=', 'postulante.id_colegio')
    ->select(
        DB::raw('COUNT(*) AS cant'),
        'colegios.gestion AS tipo_colegio'
    )
    ->where('inscripciones.estado', '=', 0)
    ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso)
    ->groupBy('colegios.gestion')
    ->orderByDesc('cant')
    ->get();

    $this->response['datos'] = $resultados;
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
  }

  public function showPostulante($dni) {
    $postulanteInfo = Postulante::select(
        'postulante.id AS id_postulante',
        'postulante.nombres',
        'postulante.email',
        'postulante.celular',
        'departamento.nombre AS departamento',
        'provincia.nombre AS provincia',
        'distritos.nombre AS distrito'
    )
    ->leftJoin('ubigeo', 'ubigeo.ubigeo', '=', 'postulante.ubigeo_residencia')
    ->leftJoin('departamento', 'departamento.id', '=', 'ubigeo.id_departamento')
    ->leftJoin('provincia', 'provincia.id', '=', 'ubigeo.id_provincia')
    ->leftJoin('distritos', 'distritos.id', '=', 'ubigeo.id_distrito')
    ->where('postulante.nro_doc', '=', $dni)
    ->first();

    $colegioInfo = Colegio::select( 'colegios.nombre AS colegio', 'distritos.nombre AS distrito' )
    ->join('postulante','postulante.id_colegio','=','colegios.id')
    ->leftJoin('ubigeo', 'ubigeo.ubigeo', '=', 'postulante.ubigeo_residencia')
    ->leftJoin('distritos', 'distritos.id', '=', 'ubigeo.id_distrito')
    ->where('postulante.nro_doc', '=', $dni)
    ->first();

    $procesos = Inscripcion::select('procesos.id AS id_proceso','procesos.nombre AS proceso','inscripciones.codigo')
    ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
    ->where('inscripciones.id_postulante', '=', $postulanteInfo->id_postulante)
    ->orderBy('procesos.id', 'desc')
    ->get();

    $foto = "https://inscripciones.admision.unap.edu.pe/fotos/inscripcion/$dni.jpg";

    $countPreInscripcion = Preinscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
    $countInscripcion = Inscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
    $countControlBiometrico = ControlBiometrico::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();

    $usuarioVinculado = null;
    if ($postulanteInfo) {
        $usuarioVinculado = \App\Models\User::where('dni', $postulanteInfo->nro_doc ?? null)
            ->first(['id', 'dni', 'name', 'paterno', 'materno', 'email']);
    }

    return Inertia::render('Admin/Postulante/Perfil',
      [
        'info' => $postulanteInfo, 
        'infoColegio' => $colegioInfo, 
        'preinscripciones'=>  $countPreInscripcion,
        'inscripciones' => $countInscripcion,
        'control_biometrico' => $countControlBiometrico,
        'foto' => $foto,
        'pro' => $procesos,
        'usuarioVinculado' => $usuarioVinculado,
      ]); 
  }

}
