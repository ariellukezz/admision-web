<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vacante;
use App\Models\Proceso;
use Pdf;
use Illuminate\Support\Facades\DB;


class RatioController extends Controller
{
    public function getRatio(Request $request)
    {
        $idProceso = auth()->user()->id_proceso;
        $proceso = Proceso::find($idProceso);

        // $vacantes = Vacante::select(
        //         'programa.nombre as programa',
        //         DB::raw('COALESCE(i.cantidad, 0) as cantidad'),
        //         'vacantes.vacantes',
        //         DB::raw('ROUND(COALESCE(i.cantidad, 0) / NULLIF(vacantes.vacantes, 0), 6) as porcentaje_ocupado'),
        //         DB::raw('ceil(i.cantidad * 0.20) as veinte_por_ciento')
        //     )
        //     ->join('programa', 'vacantes.id_programa', '=', 'programa.id')
        //     ->leftJoin(DB::raw('(
        //         SELECT id_programa, id_modalidad, COUNT(*) as cantidad
        //         FROM inscripciones
        //         WHERE id_proceso = ' . intval($idProceso) . ' AND estado = 0
        //         GROUP BY id_programa, id_modalidad
        //     ) as i'), function($join) {
        //         $join->on('vacantes.id_programa', '=', 'i.id_programa')
        //              ->on('vacantes.id_modalidad', '=', 'i.id_modalidad');
        //     })
        //     ->where('vacantes.id_proceso', $idProceso)
        //     ->orderByDesc(DB::raw('porcentaje_ocupado'))
        //     ->get();

        $vacantes = Vacante::select(
            'programa.nombre as programa',
            DB::raw('COALESCE(i.cantidad, 0) as cantidad'),
            DB::raw('SUM(vacantes.vacantes) as vacantes'),
            DB::raw('ROUND(COALESCE(i.cantidad, 0) / NULLIF(SUM(vacantes.vacantes), 0), 6) as porcentaje_ocupado'),
            DB::raw('CEIL(COALESCE(i.cantidad, 0) * 0.20) as veinte_por_ciento')
        )
        ->join('programa', 'vacantes.id_programa', '=', 'programa.id')
        ->leftJoin(DB::raw('(
            SELECT id_programa, COUNT(*) as cantidad
            FROM inscripciones
            WHERE id_proceso = ' . intval($idProceso) . ' AND estado = 0 AND id_modalidad = 9
            GROUP BY id_programa
        ) as i'), 'vacantes.id_programa', '=', 'i.id_programa')
        ->where('vacantes.id_proceso', $idProceso)
        ->where('vacantes.id_modalidad', 9)
        ->groupBy('vacantes.id_programa', 'programa.nombre', 'i.cantidad')
        ->orderByDesc('porcentaje_ocupado')
        ->get();
        
        $totalVacantes = $vacantes->sum('vacantes');
        $totalCantidad = $vacantes->sum('cantidad');
        $top8VeintePorCiento = $vacantes->take(8)->sum('veinte_por_ciento');

        if($request->descargar == 1){
            $res = $vacantes;
            $totales = [
                'cantidad' => $totalCantidad,
                'vacantes' => $totalVacantes,
                'suma_veinte_por_ciento_top_8' => $top8VeintePorCiento
            ];

            $pdf = Pdf::loadView('Reportes.ratio', compact('res', 'proceso','totales'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/$idProceso/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteUsuarios_' . date('Y-m-d_H-i-s') . auth()->id() . '.pdf';

            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }

            file_put_contents($rutaArchivo, $pdf->output());

            return $pdf->stream(date('d/m/Y H:i:s')." Reporte inscripciones diarias del usuario.pdf");

        }else{
            $this->response['estado'] = true;
            $this->response['datos'] = $vacantes;
            $this->response['totales'] = [
                'cantidad' => $totalCantidad,
                'vacantes' => $totalVacantes,
                'suma_veinte_por_ciento_top_8' => $top8VeintePorCiento
            ];
            return response()->json($this->response, 200);
        }



    }

}
