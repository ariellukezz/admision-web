<?php

namespace App\Http\Controllers;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Pdf;
use DB;

class ReporteController extends Controller
{
    public function reportePrograma(Request $request) {

        $proceso = Proceso::find(auth()->user()->id_proceso);

        $res = DB::select("SELECT 
            pre.cod_pro AS codigo,
            COALESCE(pre.programa, ins.programa) AS programa,
            COALESCE(ins.inscripciones, 0) AS inscripciones,
            COALESCE(pre.preinscripciones, 0) AS preinscripciones,
            COALESCE(pre.preinscripciones, 0) - COALESCE(ins.inscripciones, 0) AS diferencia
        FROM (
            SELECT pro.id AS cod_pro, pro.nombre AS programa, COUNT(*) AS preinscripciones FROM pre_inscripcion pre
            JOIN programa pro ON pre.id_programa = pro.id
            WHERE pre.id_proceso = ".auth()->user()->id_proceso." AND pre.estado = 1
            GROUP BY pro.id, pro.nombre
        ) pre
        INNER JOIN (
            SELECT pro.nombre AS programa, COUNT(*) AS inscripciones
            FROM inscripciones ins
            JOIN programa pro ON ins.id_programa = pro.id
            WHERE ins.id_proceso = ".auth()->user()->id_proceso." AND ins.estado = 0
            GROUP BY pro.nombre
        ) ins ON pre.programa = ins.programa
        ORDER BY inscripciones desc;");

        $totales = DB::select("SELECT
            SUM(COALESCE(ins.inscripciones, 0)) AS total_inscripciones,
            SUM(COALESCE(pre.preinscripciones, 0)) AS total_preinscripciones,
            SUM(COALESCE(pre.preinscripciones, 0)) - SUM(COALESCE(ins.inscripciones, 0)) AS total_diferencia
        FROM (
            SELECT COUNT(*) AS preinscripciones
            FROM pre_inscripcion pre
            WHERE pre.id_proceso = ".auth()->user()->id_proceso." AND pre.estado = 1
            ) pre
        INNER JOIN (
        SELECT COUNT(*) AS inscripciones
            FROM inscripciones ins
            WHERE ins.id_proceso = ".auth()->user()->id_proceso." AND ins.estado = 0
            ) ins;");

        if($request->descargar == 1){
            $pdf = Pdf::loadView('Reportes.programa_pre_ins', compact('res', 'proceso','totales'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/".auth()->user()->id_proceso."/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteProgramas_' . date('Y-m-d_H-i-s') .auth()->id(). '.pdf';        
            if (!file_exists($rutaCarpeta)) { mkdir($rutaCarpeta, 0755, true); }
        
            file_put_contents($rutaArchivo, $pdf->output());
            return $pdf->stream(date('d/m/Y H:i:s')." ".auth()->id()." Resumen por programa.pdf");
        }else{
            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }

    }


    public function reporteProgramaDiario(Request $request)
    {
        $proceso = Proceso::find(auth()->user()->id_proceso);
        $fechas = DB::table('inscripciones')
            ->selectRaw('DATE(created_at) as fecha')
            ->where('id_proceso', auth()->user()->id_proceso)
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('fecha')
            ->toArray();

        if (empty($fechas)) {
            return response()->json(['message' => 'No hay datos disponibles para este proceso'], 404);
        }
    
        $columnasFecha = implode(", ", array_map(function ($fecha) {
            return "COALESCE(SUM(CASE WHEN DATE(ins.created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `ins_$fecha`";
        }, $fechas));
    
        $query = "SELECT p.nombre AS programa, p.id AS codigo, $columnasFecha, COUNT(ins.id) AS inscripciones
            FROM programa p
            LEFT JOIN inscripciones ins ON p.id = ins.id_programa 
                AND ins.id_proceso = ? 
            WHERE p.id_filial = $proceso->id_sede_filial 
            AND p.nivel_academico = 'CARRERA PROFESIONAL'
            AND p.estado = 1
            GROUP BY p.nombre, p.id 
            ORDER BY p.id";

        $res = DB::select($query, [auth()->user()->id_proceso]);
        
        $columnasTotales = implode(", ", array_map(function ($fecha) {
            return "COALESCE(SUM(CASE WHEN DATE(created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `ins_$fecha`";
        }, $fechas));
    
        $totales = DB::select("SELECT $columnasTotales, COUNT(id) AS total_inscripciones FROM inscripciones 
            WHERE id_proceso = ? ", [auth()->user()->id_proceso]);
    
        if($request->descargar == 1){

            $pdf = Pdf::loadView('Reportes.programa_ins', compact('res', 'proceso', 'totales', 'fechas')); 
            $pdf->getDomPDF()->set_option("isPhpEnabled", true); 
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true); 
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/".auth()->user()->id_proceso."/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteProgramas_' . date('Y-m-d_H-i-s') . auth()->id() . '.pdf';
        
            if (!file_exists($rutaCarpeta)) { mkdir($rutaCarpeta, 0755, true); } 
            file_put_contents($rutaArchivo, $pdf->output()); 
            return $pdf->stream(date('d/m/Y H:i:s')." Reporte inscripciones diarias por programa.pdf"); 

        }else{

            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['fechas'] = $fechas;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }

    }



    public function reporteUsuarios(Request $request)
    {
        $sim = auth()->user()->id_proceso;
        $proceso = Proceso::find($sim);

        $fechas = DB::table('inscripciones')
            ->select(DB::raw('DATE(created_at) as fecha'))
            ->where('id_proceso', $sim)
            ->where('estado', 0)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->pluck('fecha')
            ->toArray();

        if (empty($fechas)) {
            return response()->json(['message' => 'No hay datos disponibles para este proceso'], 404);
        }

        $columnas = [];
        foreach ($fechas as $fecha) {
            $columnas[] = "COALESCE(SUM(CASE WHEN DATE(ins.created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `$fecha`";
        }

        $columnas[] = "COALESCE(SUM(CASE WHEN DATE(ins.created_at) IN ('" . implode("','", $fechas) . "') THEN 1 ELSE 0 END), 0) AS total";


        $query = "
            SELECT 
                upper(users.name) as name,
                upper(users.paterno) as paterno,
                " . implode(", ", $columnas) . "
            FROM inscripciones ins
            JOIN users ON users.id = ins.id_usuario
            WHERE ins.estado = 0 AND ins.id_proceso = ?
            GROUP BY users.paterno, users.name
            ORDER BY Total DESC
        ";


        $columnasTotales = [];
        foreach ($fechas as $fecha) {
            $columnasTotales[] = "COALESCE(SUM(CASE WHEN DATE(created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `$fecha`";
        }
        $columnasTotales[] = "COUNT(id) AS total_inscripciones";

        $totales = DB::select("
            SELECT " . implode(", ", $columnasTotales) . "
            FROM inscripciones 
            WHERE id_proceso = ? AND estado = 0
        ", [$sim]);

        $res = DB::select($query, [$sim]);

        
        if($request->descargar == 1){

            $pdf = Pdf::loadView('Reportes.usuarios_inscripciones', compact('res', 'fechas', 'proceso','totales'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/$sim/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteUsuarios_' . date('Y-m-d_H-i-s') . auth()->id() . '.pdf';

            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }

            file_put_contents($rutaArchivo, $pdf->output());

            return $pdf->stream(date('d/m/Y H:i:s')." Reporte inscripciones diarias del usuario.pdf");

        }else{

            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['fechas'] = $fechas;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }


    }

    
    




}
