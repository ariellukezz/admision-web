<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postulante;
use App\Models\ControlBiometrico;
use App\Models\Inscripcion;
use App\Models\Programa;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApixController extends Controller {

    public function getIngresante($dni, $anio, $ciclo){
        try {

            $res = Postulante::select(
                'postulante.nombres',
                'postulante.segundo_apellido',
                'postulante.primer_apellido',
                'postulante.tipo_doc AS tipo_documento',
                'postulante.nro_doc AS nro_documento',
                'postulante.sexo',
                'postulante.estado_civil',
                'postulante.celular',
                'postulante.fec_nacimiento AS fecha_nacimiento',
                'postulante.email',
                'postulante.ubigeo_residencia',
                'postulante.ubigeo_nacimiento',
                'postulante.direccion',
                'postulante.discapacidad',
                'paises.codigo AS pais_nacimiento', 'paises.nacionalidad',
                'control_biometrico.codigo_ingreso',
                'filial.codigo as codigo_sede_filial', 'tipo_proceso.id AS tipo_proceso',
                DB::raw("CONCAT( procesos.anio,'-',procesos.ciclo) as proceso_admision"),
                'facultad.codigo AS codigo_facultad',
                'programa.codigo_sunedu AS codigo_programa',
            )
            ->leftjoin('paises','paises.id','postulante.id_pais')
            ->join('inscripciones','inscripciones.id_postulante','postulante.id')
            ->join('programa','inscripciones.id_programa','programa.id')
            ->leftjoin('facultad','programa.id_facultad','facultad.id')
            ->join('resultados','resultados.dni_postulante','postulante.nro_doc')
            ->join('modalidad','inscripciones.id_modalidad','modalidad.id')
            ->leftjoin('procesos','procesos.id','inscripciones.id_proceso')
            ->leftjoin('filial','filial.id','procesos.id_sede_filial')
            ->leftjoin('tipo_proceso','tipo_proceso.id','procesos.id_tipo_proceso')
            ->join('control_biometrico','control_biometrico.id_postulante','postulante.id')
            ->where('resultados.apto', '=','SI')
            //->where('procesos.id', '=',$proceso)
            ->where('procesos.anio', '=',$anio)
            ->where('procesos.ciclo', '=',$ciclo)
            ->where('inscripciones.estado','=',0)
            ->where('postulante.nro_doc','=',$dni)
            ->orderby('inscripciones.id', 'desc')
            ->first();
            if ($res ){
                return response()->json(['status' => true, 'mensaje'=>'-', 'data' => $res], 200);
            }else {
                return response()->json(['status' => false, 'mensaje'=>'Ingresante no encontrado'], 203);
            }

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'mensaje'=>$th->getMessage()], 500);
        }

        
        
    }


    public function getIngresantePago($dni, $anio, $ciclo){
        try {

            $res = Postulante::select(
                'postulante.nombres',
                'postulante.segundo_apellido',
                'postulante.primer_apellido',
                'postulante.nro_doc AS nro_documento',
                'control_biometrico.codigo_ingreso',
                'inscripciones.id_programa'
            )
            ->join('control_biometrico','control_biometrico.id_postulante','postulante.id')
            ->join('procesos','control_biometrico.id_proceso','procesos.id')
            ->join('inscripciones','inscripciones.id_postulante','postulante.id')
            // ->where('procesos.anio', '=',$anio)
            // ->where('procesos.ciclo', '=',$ciclo)
            ->where('postulante.nro_doc','=',$dni)->first();

            if ( $res ){
                return response()->json(['status' => true, 'mensaje'=>'Ingresante encontrado', 'data' => $res], 200);
            }else {
                return response()->json(['status' => false, 'mensaje'=>'Ingresante no encontrado'], 203);
            }

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'mensaje'=>$th->getMessage()], 500);
        }

    }


    public function getPostulantePago($dni, $proceso){

            $res = null;

            $res = Postulante::select(
                'postulante.nro_doc',
                'postulante.primer_apellido',
                'postulante.segundo_apellido',
                'postulante.nombres',
                DB::raw('"" as id_gestion'),
                DB::raw('"" as id_programa'),
                DB::raw('null as pagos')
            )
            ->where('nro_doc', '=', $dni)
            ->first();
            return $res;

    }

    public function getBiometrico($codigo){
        $res = DB::select("SELECT control_biometrico.estado FROM control_biometrico
            JOIN postulante ON postulante.id = control_biometrico.id_postulante
            WHERE control_biometrico.id_proceso IN (
                SELECT pp.id_proceso FROM periodo_proceso pp
                JOIN periodos_matricula pm ON pm.id = pp.id_periodo
                WHERE pm.activo = 1
            ) AND postulante.nro_doc = ?", [$codigo]);

        if (count($res) > 0 ){
            return response()->json(['status' => true, 'data' => $res[0]], 200);
        }else {
            return response()->json(['status' => false, 'mensaje'=>'Postulante no encontrado'], 203);
        }

    }


    public function update(Request $request, Postulante $postulante){
        $rules = [
            'matricula'=>'required|string|max:6'];
            $validator = \Validator::make($request->input(), $rules);
            if($validator->fails()){
                return response()->json([
                    'status'=> false,
                    'errors'=> $validator->errors()->all()
                ],200);
            }
            $departament->update($request->input());
            return response()->json([
                'status' => true,
                'message' => 'Postulante actualizado'
            ],200);
    }


    public function getPostulanteProcesos($dni)
    {
      $postulante = Postulante::selectRaw('
          postulante.nro_doc,
          postulante.nombres,
          postulante.primer_apellido,
          postulante.segundo_apellido,
          JSON_ARRAYAGG(procesos.nombre) as procesos
      ')
      ->join('inscripciones', 'inscripciones.id_postulante', '=', 'postulante.id')
      ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
      ->where('postulante.nro_doc', $dni)
      ->groupBy(
          'postulante.nro_doc',
          'postulante.nombres',
          'postulante.primer_apellido',
          'postulante.segundo_apellido'
      )
      ->first();

      if (!$postulante) {
          return response()->json([
              'estado' => false,
              'user_info' => null
          ], 200);
      }

      return response()->json([
          'estado' => true,
          'user_info' => [
              'dni'      => $postulante->nro_doc,
              'nombres'  => $postulante->nombres,
              'paterno'  => $postulante->primer_apellido,
              'materno'  => $postulante->segundo_apellido,
              'procesos' => json_decode($postulante->procesos, true),
          ]
      ], 200);

    }

    function ingresanteBase64($dni)
    {
        $registro = ControlBiometrico::join('postulante', 'control_biometrico.id_postulante', '=', 'postulante.id')
            ->join('procesos','control_biometrico.id_proceso', '=', 'procesos.id')        
            ->where('postulante.nro_doc', $dni)
            ->whereDate('control_biometrico.created_at', '>', '2025-01-01')
            ->orderBy('control_biometrico.id', 'DESC')
            ->first();

        if (!$registro) {
            return response()->json([
                'status' => false,
                'mensaje' => 'Registro no encontrado'
            ], 404);
        }

        $ruta = "https://inscripciones.admision.unap.edu.pe/documentos/{$registro->id_proceso}/control_biometrico/fotos/{$registro->nro_doc}.jpg";

        $context = stream_context_create([
            'http' => [
                'timeout' => 5
            ]
        ]);

        $contenidoImagen = @file_get_contents($ruta, false, $context);

        if ($contenidoImagen === false) {
            return response()->json([
                'status' => false,
                'mensaje' => 'No se pudo acceder a la imagen. Puede que la URL esté mal o el servicio no responda.'
            ], 502);
        }

        $infoImagen = @getimagesizefromstring($contenidoImagen);

        if (!$infoImagen || !isset($infoImagen['mime'])) {
            return response()->json([
                'status' => false,
                'mensaje' => 'El archivo descargado no es una imagen válida.'
            ], 415);
        }

        $mime = $infoImagen['mime'];
        $base64 = base64_encode($contenidoImagen);

        return response()->json([
            'estado' => true,
            'foto' => "data:$mime;base64,$base64",
            'periodo' => $registro->anio.$registro->ciclo_oti
        ]);
    }

    public function esIngresante($periodo, $dni){
        $existe = DB::table('resultados as res')
            ->join('procesos as pr', 'res.id_proceso', '=', 'pr.id')
            ->join('postulante as pos', 'res.dni_postulante', '=', 'pos.nro_doc')
            ->leftJoin('control_biometrico as cb', function ($join) {
                $join->on('cb.id_postulante', '=', 'pos.id')
                    ->on('pr.id', '=', 'cb.id_proceso');
            })
            ->whereRaw("CONCAT(pr.anio, pr.ciclo_oti) = ?", [$periodo])
            ->where(function ($q) use ($dni) {
                $q->where('res.dni_postulante', $dni)
                ->orWhere('cb.codigo_ingreso', $dni);
            })
            ->select('cb.codigo_ingreso as codigo')
            ->first();

        return response()->json([
            'estado' => true,
            'ingreso' => $existe ? true : false,
            'codigo'  => $existe->codigo ?? null,
        ]);

    }


    public function getIngresantePeriodoDniSeguimiento($periodo, $dni){

        $result = DB::table('control_biometrico as cb')
            ->join('procesos as proc', 'cb.id_proceso', '=', 'proc.id')
            ->join('postulante as pos', 'pos.id', '=', 'cb.id_postulante')
            ->join('inscripciones as ins', function($join) {
                $join->on('ins.id_postulante', '=', 'pos.id')
                    ->where('ins.estado', 0)
                    ->on('ins.id_proceso', '=', 'cb.id_proceso');
            })
            ->join('programa as pro', 'pro.id', '=', 'ins.id_programa')
            ->join('modalidad as mo', 'mo.id', '=', 'ins.id_modalidad')
            ->select(
                'cb.codigo_ingreso as codigo',
                'pos.nro_doc',
                'pos.nombres',
                'pos.primer_apellido as paterno',
                'pos.segundo_apellido as materno',
                'pos.celular',
                'pos.email',
                'pro.nombre as programa',
                'mo.nombre as modalidad',
                'proc.nombre as procesos',
                DB::raw("CONCAT(proc.anio,'-',proc.ciclo) as Periodo")
            )
            ->where('pos.nro_doc', $dni)
            ->where(DB::raw("CONCAT(proc.anio,'-',proc.ciclo)"), $periodo)
            ->get();

        return response()->json([
            'estado' => true,
            'datos' => $result,
        ]);

    }


    public function getPostulantesProceso($id_proceso, $programa)
    {
        try {
            $validator = \Validator::make([
                'id_proceso' => $id_proceso,
                'programa' => $programa
            ], [
                'id_proceso' => 'required|integer|exists:procesos,id',
                'programa' => 'required|integer|exists:programa,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()->all()
                ], 400);
            }

            $res = Inscripcion::join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
                ->leftJoin('control_biometrico', function ($join) use ($id_proceso) {
                    $join->on('control_biometrico.id_postulante', '=', 'postulante.id')
                        ->where('control_biometrico.id_proceso', '=', $id_proceso);
                })
                ->join('resultados', function ($join) use ($id_proceso) {
                    $join->on('resultados.dni_postulante', '=', 'postulante.nro_doc')
                        ->where('resultados.id_proceso', '=', $id_proceso);
                })
                ->join('ubigeo', 'postulante.ubigeo_nacimiento', '=', 'ubigeo.ubigeo')
                ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
                ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
                ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
                ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
                ->join('programa', 'programa.id', '=', 'inscripciones.id_programa')
                ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
                ->join('colegios', 'colegios.id', '=', 'postulante.id_colegio')
                ->where('inscripciones.estado', '=', 0)
                ->where('inscripciones.id_proceso', '=', $id_proceso)
                ->where('inscripciones.id_programa', '=', $programa)
                ->select(
                    'control_biometrico.codigo_ingreso AS codigo',
                    'postulante.nro_doc AS DNI',
                    'postulante.primer_apellido',
                    'postulante.segundo_apellido',
                    'postulante.nombres',
                    DB::raw("IF(postulante.sexo = 1, 'M', 'F') AS sexo"),
                    'postulante.email',
                    'postulante.fec_nacimiento',
                    'postulante.ubigeo_nacimiento',
                    'postulante.estado_civil',
                    'postulante.anio_egreso',
                    'colegios.gestion',
                    'colegios.nombre',
                    'colegios.ubigeo AS c_ubigeo',
                    'postulante.direccion',
                    'postulante.celular',
                    'resultados.fecha AS f_examen',
                    'modalidad.nombre AS modalidad',
                    'resultados.puntaje',
                    'procesos.nombre AS proceso',
                    'programa.id AS id_programa',
                    'programa.nombre AS programa',
                    'departamento.nombre AS departamento',
                    'provincia.nombre AS provincia',
                    'distritos.nombre AS distrito'
                )
                ->get();

            return response()->json([
                'status' => true,
                'mensaje' => 'Consulta exitosa',
                'total' => $res->count(),
                'data' => $res
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'mensaje' => $th->getMessage()], 500);
        }
    }

    public function getSelectProgramasAdmision()
    {
        try {
            $res = Programa::select('programa.id', 'programa.siu', 'programa.nombre')
                ->orderBy('programa.nombre')
                ->get();

            return response()->json([
                'status' => true,
                'mensaje' => 'Consulta exitosa',
                'total' => $res->count(),
                'data' => $res
            ], 200);

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'mensaje' => $th->getMessage()], 500);
        }
    }

}
