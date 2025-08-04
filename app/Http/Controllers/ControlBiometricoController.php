<?php

namespace App\Http\Controllers;
use App\Models\ControlBiometrico;
use Illuminate\Http\Request;
use DB;
class ControlBiometricoController extends Controller
{

    public function getControlPosterior(Request $request) {

        $procesoId = auth()->user()->id_proceso;
        $pageSize = 50; 

        $result = ControlBiometrico::select('control_biometrico.codigo_ingreso', 'postulante.nro_doc', 'postulante.primer_apellido',
            'postulante.segundo_apellido', 'postulante.nombres', 'programa.nombre_corto AS programa', 
            'programa.nombre as programa_estudio', 'modalidad.nombre AS modalidad',
            'puntajes.puntaje AS puntaje', 'puntajes.puntaje_vocacional', 
            DB::raw('COALESCE(puntajes.puntaje_vocacional, 0) AS puntaje_vocacional'),
            DB::raw('(puntajes.puntaje + COALESCE(puntajes.puntaje_vocacional, 0)) AS puntaje_total'),
            DB::raw("CONCAT('/documentos/',".$procesoId.",'/control_biometrico/constancias/',postulante.nro_doc,'.pdf') AS url"))
            ->join('postulante', 'postulante.id', '=', 'control_biometrico.id_postulante')
            ->join('inscripciones', function ($join) use ($procesoId) {
                $join->on('inscripciones.id_postulante', '=', 'postulante.id')
                    ->where('inscripciones.id_proceso', '=', $procesoId)
                    ->where('inscripciones.estado', '=', 0);
            })
            ->join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->join('modalidad', 'modalidad.id', '=', 'inscripciones.id_modalidad')
            ->join('puntajes', function ($join) use ($procesoId) {
                $join->on('puntajes.dni', '=', 'postulante.nro_doc')
                    ->where('puntajes.id_proceso', '=', $procesoId)
                    ->where('puntajes.apto', '=', 'SI');
            })
            ->where('control_biometrico.id_proceso', '=', $procesoId)
            ->where(function ($query) use ($request) {
                $query->where('postulante.nro_doc', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.primer_apellido', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.segundo_apellido', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.nombres', 'LIKE', "%$request->term%")
                    ->orWhere('programa.nombre', 'LIKE', "%$request->term%")
                    ->orWhere(DB::raw("CONCAT(postulante.primer_apellido, ' ', postulante.segundo_apellido, ' ', postulante.nombres)"), 'LIKE', "%$request->term%")
                    ->orWhere(DB::raw("CONCAT(postulante.nombres,' ',postulante.primer_apellido, ' ', postulante.segundo_apellido)"), 'LIKE', "%$request->term%")
                    ->orWhere('modalidad.nombre', 'LIKE', "%$request->term%");
            })
            ->orderBy('programa.nombre', 'ASC')
            ->orderBy(DB::raw('(puntajes.puntaje + puntajes.puntaje_vocacional)'), 'DESC')
            ->paginate($pageSize);
    
        $response = [ 'estado' => true, 'datos' => $result];
        return response()->json($response, 200);

    }


    



}
