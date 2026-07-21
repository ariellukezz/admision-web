<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;  
use App\Models\Postulante;
use App\Models\Colegio;
use App\Models\ControlBiometrico;
use App\Models\Proceso;
use App\Models\AuditTrail;
use App\Models\CarrerasPrevias;
use App\Models\Documento;
use App\Models\Ingresante;
use App\Models\Paso;
use App\Models\Comprobante;
use App\Models\RevisionSolicitud;
use App\Models\AvancePostulante;
use App\Models\TipoDocumento;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        'postulante.nro_doc',
        'postulante.nombres',
        'postulante.primer_apellido',
        'postulante.segundo_apellido',
        'postulante.email',
        'postulante.celular',
        'postulante.sexo',
        'postulante.fec_nacimiento',
        'postulante.direccion',
        'postulante.discapacidad',
        'postulante.tipo_discapacidad',
        'postulante.estado_civil',
        'postulante.anio_egreso',
        'postulante.correo_institucional',
        'postulante.cod_orcid',
        'postulante.foto_url',
        'postulante.observaciones',
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

    if (!$postulanteInfo) {
        abort(404, 'Postulante no encontrado');
    }

    $colegioInfo = Colegio::select( 'colegios.nombre AS colegio', 'distritos.nombre AS distrito' )
    ->join('postulante','postulante.id_colegio','=','colegios.id')
    ->leftJoin('ubigeo', 'ubigeo.ubigeo', '=', 'postulante.ubigeo_residencia')
    ->leftJoin('distritos', 'distritos.id', '=', 'ubigeo.id_distrito')
    ->where('postulante.nro_doc', '=', $dni)
    ->first();

    // Inscripciones con relaciones
    $inscripciones = Inscripcion::with(['programa:id,nombre,codigo', 'modalidad:id,nombre,codigo'])
        ->select('inscripciones.id', 'inscripciones.codigo', 'inscripciones.id_proceso', 'inscripciones.id_programa', 'inscripciones.id_modalidad', 'inscripciones.estado', 'inscripciones.created_at')
        ->where('inscripciones.id_postulante', '=', $postulanteInfo->id_postulante)
        ->orderBy('inscripciones.id', 'desc')
        ->get();

    $procesos = Inscripcion::select('procesos.id AS id_proceso','procesos.nombre AS proceso','inscripciones.codigo')
    ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
    ->where('inscripciones.id_postulante', '=', $postulanteInfo->id_postulante)
    ->orderBy('procesos.id', 'desc')
    ->get();

    // Preinscripciones con relaciones
    $preinscripcionesData = Preinscripcion::with(['programa:id,nombre,codigo', 'modalidad:id,nombre,codigo'])
        ->select('pre_inscripcion.id', 'pre_inscripcion.id_programa', 'pre_inscripcion.id_modalidad', 'pre_inscripcion.estado', 'pre_inscripcion.codigo_seguridad', 'pre_inscripcion.observacion', 'pre_inscripcion.created_at')
        ->where('pre_inscripcion.id_postulante', '=', $postulanteInfo->id_postulante)
        ->orderBy('pre_inscripcion.id', 'desc')
        ->get();

    // Foto: buscar localmente primero
    $foto = null;
    if ($postulanteInfo->foto_url) {
        $fotoPath = public_path($postulanteInfo->foto_url);
        if (File::exists($fotoPath)) {
            $foto = url($postulanteInfo->foto_url) . '?v=' . time();
        }
    }
    if (!$foto) {
        $procesoIds = $inscripciones->pluck('id_proceso')->unique();
        foreach ($procesoIds as $pid) {
            $localPath = public_path("documentos/{$pid}/inscripciones/fotos/{$dni}.jpg");
            if (File::exists($localPath)) {
                $foto = url("documentos/{$pid}/inscripciones/fotos/{$dni}.jpg") . '?v=' . time();
                break;
            }
            $bioPath = public_path("documentos/{$pid}/control_biometrico/fotos/{$dni}.jpg");
            if (File::exists($bioPath)) {
                $foto = url("documentos/{$pid}/control_biometrico/fotos/{$dni}.jpg") . '?v=' . time();
                break;
            }
        }
    }
    if (!$foto) {
        $foto = "https://inscripciones.admision.unap.edu.pe/fotos/inscripcion/$dni.jpg";
    }

    $countPreInscripcion = $preinscripcionesData->count();
    $countInscripcion = $inscripciones->count();
    $countControlBiometrico = ControlBiometrico::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();

    // Puntajes / Resultados
    $puntajes = Ingresante::where('dni_postulante', $dni)
        ->orderBy('id_proceso', 'desc')
        ->get();

    // Pasos realizados
    $pasos = Paso::where('postulante', $postulanteInfo->id_postulante)
        ->orderBy('nro', 'asc')
        ->get();

    // Avance del postulante
    $avance = AvancePostulante::where('dni_postulante', $dni)
        ->orderBy('id_proceso', 'desc')
        ->get();

    // Documentos con tipo
    $documentos = Documento::with('tipoDocumento:id,nombre,codigo')
        ->select('id', 'nombre', 'id_tipo_documento', 'estado', 'verificado', 'valido', 'observacion', 'url', 'path', 'created_at', 'revisado_at', 'validado_at')
        ->where('id_postulante', '=', $postulanteInfo->id_postulante)
        ->where('is_deleted', false)
        ->orderBy('id', 'desc')
        ->get();

    // Comprobantes de pago
    $comprobantes = Comprobante::where('ndoc_postulante', $dni)
        ->orderBy('fecha', 'desc')
        ->get();

    // Solicitudes de revisión
    $revisiones = RevisionSolicitud::where('id_postulante', '=', $postulanteInfo->id_postulante)
        ->orderBy('id', 'desc')
        ->get();

    $usuarioVinculado = null;
    if ($postulanteInfo) {
        $usuarioVinculado = \App\Models\User::where('dni', $postulanteInfo->nro_doc ?? null)
            ->first(['id', 'dni', 'name', 'paterno', 'materno', 'email']);
    }

    // Carreras terminadas
    $carrerasPrevias = CarrerasPrevias::where('dni_postulante', $dni)
        ->orderBy('fecha', 'desc')
        ->get();
    $countCarrerasTerminadas = $carrerasPrevias->count();

    // IDs para filtrar actividad
    $inscripcionIds = $inscripciones->pluck('id');
    $preinscripcionIds = $preinscripcionesData->pluck('id');
    $documentIds = $documentos->pluck('id');

    // Descargas
    $countDownloads = AuditTrail::where('action', 'download')
        ->where('model_type', Documento::class)
        ->whereIn('model_id', $documentIds)
        ->count();

    // Actividad reciente
    $actividades = DB::table('activity_logs')
        ->leftJoin('users', 'users.id', '=', 'activity_logs.user_id')
        ->select(
            'users.name as usuario',
            'activity_logs.action as acciones',
            'activity_logs.model_id as registro',
            'activity_logs.tabla',
            'activity_logs.direccion',
            'activity_logs.fecha'
        )
        ->where(function ($q) use ($postulanteInfo, $inscripcionIds, $preinscripcionIds, $documentIds, $usuarioVinculado) {
            $q->where(function ($q2) use ($postulanteInfo) {
                $q2->where('activity_logs.tabla', 'postulante')
                   ->where('activity_logs.model_id', $postulanteInfo->id_postulante);
            });
            if ($inscripcionIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($inscripcionIds) {
                    $q2->where('activity_logs.tabla', 'inscripciones')
                       ->whereIn('activity_logs.model_id', $inscripcionIds);
                });
            }
            if ($preinscripcionIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($preinscripcionIds) {
                    $q2->where('activity_logs.tabla', 'pre_inscripcion')
                       ->whereIn('activity_logs.model_id', $preinscripcionIds);
                });
            }
            if ($documentIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($documentIds) {
                    $q2->where('activity_logs.tabla', 'documento')
                       ->whereIn('activity_logs.model_id', $documentIds);
                });
            }
            if ($usuarioVinculado) {
                $q->orWhere('activity_logs.user_id', $usuarioVinculado->id);
            }
        })
        ->orderBy('activity_logs.fecha', 'desc')
        ->limit(20)
        ->get();

    return Inertia::render('Admin/Postulante/Perfil',
      [
        'info' => $postulanteInfo, 
        'infoColegio' => $colegioInfo, 
        'preinscripciones' => $countPreInscripcion,
        'inscripciones' => $countInscripcion,
        'control_biometrico' => $countControlBiometrico,
        'foto' => $foto,
        'pro' => $procesos,
        'usuarioVinculado' => $usuarioVinculado,
        'carrerasTerminadas' => $countCarrerasTerminadas,
        'downloads' => $countDownloads,
        'actividades' => $actividades,
        'puntajes' => $puntajes,
        'pasos' => $pasos,
        'avance' => $avance,
        'documentos' => $documentos,
        'comprobantes' => $comprobantes,
        'revisiones' => $revisiones,
        'inscripcionesData' => $inscripciones,
        'preinscripcionesData' => $preinscripcionesData,
        'carrerasPrevias' => $carrerasPrevias,
      ]); 
  }

}
