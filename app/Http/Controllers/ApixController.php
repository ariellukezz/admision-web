<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Postulante;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ApixController extends Controller {

    public function getIngresante($dni, $proceso){
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
            'programa.codigo AS codigo_programa',
        )
        ->leftjoin('paises','paises.id','postulante.id_pais')
        ->leftjoin('inscripciones','inscripciones.id_postulante','postulante.id')
        ->leftjoin('programa','inscripciones.id_programa','programa.id')
        ->leftjoin('facultad','programa.id_facultad','facultad.id')
        ->leftjoin('resultados','resultados.dni_postulante','postulante.nro_doc')
        ->leftjoin('modalidad','inscripciones.id_modalidad','modalidad.id')
        ->leftjoin('procesos','procesos.id','inscripciones.id_proceso')
        ->leftjoin('filial','filial.id','procesos.id_sede_filial')
        ->leftjoin('tipo_proceso','tipo_proceso.id','procesos.id_tipo_proceso')
        ->leftjoin('control_biometrico','control_biometrico.id_postulante','postulante.id')
        ->where('resultados.apto', '=','SI')
        ->where('procesos.id', '=',$proceso)
        ->where('nro_doc','=',$dni)->get();

        if (count($res) > 0 ){
            return response()->json(['status' => true, 'mensaje'=>'-', 'data' => $res[0]], 200);
        }else {
            return response()->json(['status' => false, 'mensaje'=>'Postulante no encontrado'], 203);
        }

    }


    public function getPostulantePago($dni, $proceso){
        $res = [];
        if($proceso == 4 ){
            $res = Postulante::select(
                'postulante.nro_doc', 'postulante.primer_apellido', 'postulante.segundo_apellido',
                'postulante.nombres', 'colegios.id_gestion',
                DB::raw("IF(colegios.id_gestion = 1, 21, IF((colegios.id_gestion = 2 OR colegios.id_gestion = 3), 21, IF(colegios.id_gestion = 4, 21, 0))) AS Monto")  
            )
            ->join('colegios','colegios.id','postulante.id_colegio')
            ->where('nro_doc','=',$dni)->get();
        }
        if($proceso == 5){
            $res = Postulante::select(
                'postulante.nro_doc', 'postulante.primer_apellido', 'postulante.segundo_apellido',
                'postulante.nombres', 'colegios.id_gestion',
                DB::raw("IF(colegios.id_gestion = 1, 200, IF((colegios.id_gestion = 2 OR colegios.id_gestion = 3), 350, IF(colegios.id_gestion = 4, 450, 0))) AS Monto")  
            )
            ->join('colegios','colegios.id','postulante.id_colegio')
            ->where('nro_doc','=',$dni)->get();
        }

        if (count($res) > 0 ){
            return response()->json(['status' => true, 'mensaje'=>'-', 'data' => $res[0]], 200);
        }else {
            return response()->json(['status' => false, 'mensaje'=>'Postulante no encontrado'], 203);
        }
    }

    public function getBiometrico($codigo){
        $res = DB::select("SELECT estado FROM control_biometrico
        WHERE codigo_ingreso = ".$codigo);

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
                ],400);
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
