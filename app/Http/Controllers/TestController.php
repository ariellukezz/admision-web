<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AvancePostulante;
use App\Models\Inscripcion;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Options;
use App\Models\DetalleExamenVocacional;


class TestController extends Controller
{
    public function saveAvance(){
        $usuario = AvancePostulante::create([
            'dni_postulante'=> '70757838',
            'id_proceso' => 4,
            'id_usuario' => 1,
            'avance' => 1,
        ]);
    }

    public function getAvancePostulante(Request $request)
    {
        $res = DB::select(
            'SELECT dni_postulante, postulante.nombres, id_proceso, avance, avance_postulante.id_usuario, observacion 
            FROM avance_postulante 
            join postulante on postulante.nro_doc = avance_postulante.dni_postulante
            WHERE dni_postulante = '.$request->dni .' AND postulante.ubigeo_nacimiento = ' . $request->ubigeo
        );

        $this->response['estado'] = true;
        $this->response['datos'] = $res[0];
        return response()->json($this->response, 200);
    }

    public function getAvancePostulante2(Request $request)
    {
        $res = DB::select(
            'SELECT dni_postulante, id_proceso, avance, avance_postulante.id_usuario, observacion 
            FROM avance_postulante 
            WHERE dni_postulante = '.$request->dni
        );

        $this->response['estado'] = true;
        $this->response['datos'] = $res[0];
        return response()->json($this->response, 200);
    }


    public function getNroConstancia($carrera, $dni){

        $resultado = Inscripcion::select('inscripciones.codigo', 'postulante.nro_doc', 'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido')
        ->join('postulante', 'postulante.id', '=', 'inscripciones.id_postulante')
        ->where('inscripciones.codigo', 'LIKE', $carrera.'%')
        ->where('postulante.nro_doc', '=', $dni)
        ->get();

        return $resultado[0];
    }

    public function Distribucion() {
        $personas = DB::table('inscripcion_simulacro as iss')
        ->join('programa as pro', 'iss.id_programa', '=', 'pro.id')
        ->join('participantes_simulacro as ps', 'ps.id', '=', 'iss.id_estudiante')
        ->select('ps.id','ps.nro_doc', 'ps.nombres', 'ps.paterno')
        // ->where('pro.area', 'SOCIALES')
        ->orderby('id')
        ->get();
        $cant = $personas->count();
        $personasPorGrupo = 50;
        $cant2 = $ultimoMultiploDe50 = floor($cant / $personasPorGrupo) * $personasPorGrupo;

        $ng = ceil($cant2 / $personasPorGrupo);

        $arrayDeGrupos = [];
        for ($i = 1; $i <= $ng; $i++) {
            $temp = 0;
            $grupo = [];        
            for ($j = $i; $j <= $cant2; $j += $ng) {
                if ($j <= $cant2) {
                    if($i == 1){
                        array_push($grupo, ["dependencia"=>"SALON DE EVENTOS", "nro" => $temp+1, "aula" => 100+$i, "data" => $personas[$j - 1]]);
                    }else {
                        array_push($grupo, ["dependencia"=>"COLISEO", "nro" => $temp+1, "aula" => 100+$i, "data" => $personas[$j - 1]]);
                    }
                }
                $temp = $temp + 1;
            }
        
            $arrayDeGrupos["$i"] = $grupo;
        }

        $grupof = [];        
        $tempf = 1;
        for ($i = $cant2; $i < $cant; $i++) {
            if ($i <= $cant) {
                array_push($grupof, ["nro" => $tempf, "aula" => 100+5, "data" => $personas[$i]]);
            }
            $tempf++;
        }
        $arrayDeGrupos[$ng+1] = $grupof;



        return $arrayDeGrupos;
        //return $cant2;

    }

    

    public function pdfLista() {

        // $datos = DB::select("
        //   SELECT 
        //   programa.nombre AS programa,
        //   participantes_simulacro.nro_doc,
        //   participantes_simulacro.paterno,
        //   participantes_simulacro.materno,
        //   participantes_simulacro.nombres
        //   FROM inscripcion_simulacro
        //   JOIN programa ON inscripcion_simulacro.id_programa = programa.id
        //   JOIN participantes_simulacro ON inscripcion_simulacro.id_estudiante = participantes_simulacro.id
        //   WHERE participantes_simulacro.nro_doc = $dni
        // ");
  
        // if (count($datos) === 0) {
        //     return "No registrado";
        // }else {
        //   $data = $datos[0];

        $pdf = PDF::loadView('distribucion.cargo');

        // Agrega el script JavaScript para la numeración de páginas
        $pdf->getDomPDF()->set_option("isPhpEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);

        // Configura el tamaño y orientación del papel
        $pdf->setPaper('A4', 'portrait');

        // Renderiza el PDF

        //   $pdf = Pdf::loadView('distribucion.cargo');        
        //   $pdf->setPaper('A4', 'portrait');
        //   $output = $pdf->output();
  
        //   file_put_contents(public_path('/documentos/simulacro2023/').$dni.'.pdf', $output);
          return $pdf->stream();
        // }
        // return $pdf->download();
  
    }


    public function pdfTest(){
        $pdf = PDF::loadView('solicitud.solicitud');
        $pdf->getDomPDF()->set_option("isPhpEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->setPaper('A4', 'portrait');

        // Renderiza el PDF

        //$pdf = Pdf::loadView('solicitud.solicitud');        
        //   $pdf->setPaper('A4', 'portrait');
        //   $output = $pdf->output();
  
        //   file_put_contents(public_path('/documentos/simulacro2023/').$dni.'.pdf', $output);
        return $pdf->stream();
    
    }

    public function getAvancePostulanteProceso($proceso, $dni)
    {
        $resultado = DB::table('avance_postulante as ap')
            ->join('avance as av', 'ap.avance', '=', 'av.id')
            ->where('ap.dni_postulante', $dni)
            ->where('ap.id_proceso', $proceso)
            ->select( 'ap.dni_postulante as dni', 'ap.id_proceso', 'ap.avance', 'av.descripcion as estado', 'ap.observacion')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res[0];
        return response()->json($this->response, 200);
    }

    


  
}