<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postulante;
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
                DB::raw('[] as pagos')        
            )
            ->where('nro_doc', '=', $dni)
            ->first();
            return $res;
      
    }

    public function getBiometrico($codigo){
        $res = DB::select("SELECT control_biometrico.estado FROM control_biometrico
            JOIN postulante ON postulante.id = control_biometrico.id_postulante
            WHERE control_biometrico.id_proceso IN (8) AND postulante.nro_doc = ".$codigo );

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
    public function destroy(Postulante $postulante){

        
    }



}