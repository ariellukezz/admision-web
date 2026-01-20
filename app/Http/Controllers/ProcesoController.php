<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProcesoController extends Controller
{

  public function index()
  {
      return Inertia::render('Procesos/procesos');
  }

  public function getProcesos(Request $request)
  {
    $query_where = [];
    $res = Proceso::select(
      'procesos.id', 'procesos.nombre','procesos.estado','procesos.anio',
      'procesos.url', 'procesos.fecha_examen', 'procesos.ciclo', 'procesos.slug',
      'procesos.nro_convocatoria as convocatoria', 'procesos.fec_inicio', 'procesos.fec_fin',
      'procesos.fecha_examen as fec_examen', 'procesos.observaciones as observacion',
      'filial.id as id_sede', 'filial.nombre as sede',
      'tipo_proceso.id as id_tipo', 'tipo_proceso.nombre as tipo',
      'codigo_proceso','fec_1','fec_2','id_reglamento',
      'modalidad_proceso.id as id_modalidad', 'modalidad_proceso.nombre as modalidad'
    )
      ->join ('filial', 'filial.id', '=','procesos.id_sede_filial')
      ->join ('tipo_proceso', 'tipo_proceso.id', '=','procesos.id_tipo_proceso')
      ->join ('modalidad_proceso', 'modalidad_proceso.id', '=','procesos.id_modalidad_proceso')
      ->where($query_where)
      ->where('nivel',1)
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('procesos.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('filial.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('modalidad_proceso.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('procesos.anio', 'LIKE', '%' . $request->term . '%');
      })->orderBy('procesos.id', 'DESC')
      ->paginate(50);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }

  public function saveProceso(Request $request ) {
    $fec_inicio = null;
    $fec_fin = null;
    $dia1 = null;
    $dia2 = null;

    if ($request->has('f_inicio')) { $fec_inicio = substr($request->f_inicio, 0, 10); }
    if ($request->has('f_fin')) { $fec_fin = substr($request->f_fin, 0, 10); }
    if ($request->has('dia_1')) { $dia1 = substr($request->dia_1, 0, 10); }
    if ($request->has('dia_2')) { $dia2 = substr($request->dia_2, 0, 10); }

    $proceso = null;
    if (!$request->id) {
        $proceso = Proceso::create([
            'nombre' => $request->nombre,
            'slug' => $request->slug,
            'id_tipo_proceso' => $request->tipo,
            'ciclo' => $request->ciclo,
            'ciclo_oti' => str_pad($request->ciclo, 2, '0', STR_PAD_LEFT),
            'id_modalidad_proceso' => $request->modalidad,
            'anio' => $request->anio,
            'estado' => $request->estado,
            'fecha_examen' => $request->fec_examen,
            'fec_inicio' => $fec_inicio,
            'fec_fin' => $fec_fin,
            'fec_1' => $dia1,
            'fec_2' => $dia2,
            'id_sede_filial' => $request->sede,
            'nro_convocatoria' => $request->convocatoria,
            'id_modalidad_estudio' => 1,
            'observaciones' => $request->observacion,
            'url' => $request->url,
            'nivel' => 1,
            'codigo_proceso' => $request->cod_proceso,
            'id_reglamento' => $request->id_reglamento,
            'id_usuario' => auth()->id()
        ]);
        $this->response['titulo'] = 'REGISTRO NUEVO';
        $this->response['mensaje'] = 'Proceso '.$proceso->nombre.' creado con exito';
        $this->response['estado'] = true;
        $this->response['datos'] = $proceso;
    } else {

        $proceso = Proceso::find($request->id);
        $proceso->nombre = $request->nombre;
        $proceso->slug = $request->slug;
        $proceso->id_tipo_proceso = $request->tipo;
        $proceso->ciclo = $request->ciclo;
        $proceso->id_modalidad_proceso = $request->modalidad;
        $proceso->anio = $request->anio;
        $proceso->estado = $request->estado;
        $proceso->fecha_examen = $request->fec_examen;
        $proceso->fec_inicio = $fec_inicio;
        $proceso->fec_fin = $fec_fin;
        $proceso->id_sede_filial = $request->sede;
        $proceso->nro_convocatoria = $request->convocatoria;
        $proceso->observaciones = $request->observacion;
        $proceso->id_reglamento = $request->id_reglamento;
        $proceso->id_usuario = auth()->id();
        $proceso->save();

        $this->response['titulo'] = '!REGISTRO MODIFICADO!';
        $this->response['mensaje'] = 'Proceso '.$proceso->nombre.' modificado con exito';
        $this->response['estado'] = true;
        $this->response['datos'] = $proceso;
    }

    return response()->json($this->response, 200);
  }

  public function deleteProceso($id){
    $proceso = Proceso::find($id);
    $p = $proceso;
    $proceso->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'Proceso '.$p->nombre.' eliminado con exito';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }

  public function getTipoProceso(){

    $res = DB::select('SELECT id as value, nombre as label FROM tipo_proceso ');
    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);

  }


  public function getModalidades(){
    $res = DB::select('SELECT id as value, nombre as label FROM modalidad_proceso');
    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


  public function getFormulario($nombreProceso)
  {
    $proceso = Proceso::where('slug', $nombreProceso)->where('estado',1)->first();
    if($proceso){
      if( $proceso->nivel == 1 ){
        return Inertia::render('Publico/preinscripcioncepre', ['procceso_seleccionado' => $proceso]);
      }else{
        if( $proceso->nivel == 2 ){
          return Inertia::render('Segundas/Publico/preinscripcion', ['procceso_seleccionado' => $proceso]);
        }
      }

    } else {
      abort(404);
    }
  }

  public function getViewResultados($nombreProceso)
  {
    $admin = User::where('estado', 1)->where('id', auth()->id())->where('id_rol', 1)->exists() ? 1 : 0;
    $proceso = Proceso::where('slug', $nombreProceso)->first();
    if($proceso){
      return Inertia::render('Publico/Resultados/index', ['proceso_seleccionado' => $proceso, 'admin' => $admin]);
    }
    abort(404);
  }


  public function getSelectProceso( ) {
    $res = Proceso::where('estado', 1)
    ->select('id as value', 'nombre as label')
    ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }

  public function getSelectProcesoHuellas( ) {
    $res = Proceso::where('estado', 1)
    ->select('id as value', 'nombre as label','anio', DB::raw("IF(ciclo = 1, 'I', 'II') as ciclo"))
    ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }

  public function cambiarProceso(Request $request) {
      $usuario = User::find(auth()->user()->id);
      $usuario->id_proceso = $request->id_proceso;
      $usuario->save();

      $this->response['estado'] = true;
      return response()->json($this->response, 200);
  }

  public function getProcesoResultados(Request $request) {

      $res = Proceso::select(
      "procesos.id",
      "procesos.nombre",
      "procesos.slug",
      "procesos.anio",
      "procesos.estado",
      "procesos.fec_fin",
      "procesos.id_sede_filial",
      "procesos.fecha_examen",
      "reglamento.url",
      "procesos.fec_1",
      "procesos.fec_2"
      )->orderby('procesos.id','desc')
      ->leftjoin('reglamento','reglamento.id', 'procesos.id_reglamento')
      ->where('nivel',1)->get();
      $this->response['estado'] = true;
      $this->response['res'] = $res;
      return response()->json($this->response, 200);

  }






}
