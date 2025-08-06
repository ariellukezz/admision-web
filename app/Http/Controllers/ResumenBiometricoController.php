<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proceso;
use Pdf;
use DB;

class ResumenBiometricoController extends Controller
{
    public function resumenBiometrico(Request $request)
    {

        $totales = collect(DB::select("
            SELECT 
                pro.area, 
                COUNT(*) AS total
            FROM puntajes pun
            INNER JOIN postulante pos ON pos.nro_doc = pun.dni
            INNER JOIN inscripciones ins ON ins.id_postulante = pos.id 
                AND ins.id_proceso = pun.id_proceso 
                AND ins.estado = 0
            INNER JOIN programa pro ON pro.id = ins.id_programa
            WHERE pun.id_proceso = ? AND pun.apto = 'SI'
            GROUP BY pro.area WITH ROLLUP
        ", [auth()->user()->id_proceso]))->map(function ($row) {
            $row->area = $row->area ?? 'TOTAL';
            return $row;
        });

        $groupByColumns = $request->input('group_by', []);
        $columnsMap = [
            'programa' => 'pro.nombre',
            'modalidad' => 'moda.nombre',
            'sexo' => DB::raw("IF(pos.sexo = 1, 'M', 'F')"),
            'area' => 'pro.area',
            'usuario' => DB::raw("concat(usu.name,' ',usu.paterno,' ',usu.materno)"),
        ];

        $query = DB::table('control_biometrico as cb')
            ->join('postulante as pos', 'cb.id_postulante', '=', 'pos.id')
            ->join('inscripciones as ins', function ($join) {
                $join->on('ins.id_postulante', '=', 'pos.id')
                    ->on('ins.id_proceso', '=', 'cb.id_proceso')
                    ->where('ins.estado', 0);
            })
            ->join('programa as pro', 'pro.id', '=', 'ins.id_programa')
            ->join('modalidad as moda', 'ins.id_modalidad', '=', 'moda.id')
            ->join('users as usu', 'usu.id', '=', 'cb.id_usuario')
            ->where('cb.id_proceso', auth()->user()->id_proceso);

        $selectColumns = [];
        $groupBy = [];

        foreach ($groupByColumns as $alias) {
            if (isset($columnsMap[$alias])) {
                $column = $columnsMap[$alias];

                if ($column instanceof \Illuminate\Database\Query\Expression) {
                    $selectColumns[] = DB::raw("{$column->getValue(DB::connection()->getQueryGrammar())} as $alias");
                    $groupBy[] = DB::raw($column->getValue(DB::connection()->getQueryGrammar()));
                } else {
                    $selectColumns[] = DB::raw("$column as $alias");
                    $groupBy[] = $column;
                }
            }
        }

        if (empty($selectColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid group by columns provided.',
            ], 400);
        }

        $res = $query
            ->select(array_merge($selectColumns, [DB::raw('COUNT(*) as total')]))
            ->groupBy($groupBy)
            ->orderBy($groupBy[0],'asc')
            ->get();

        $total = DB::table('control_biometrico as cb')
            ->where('cb.id_proceso', auth()->user()->id_proceso)
            ->count();



        if( $request->descargar == 1){
            $sim = auth()->user()->id_proceso;
            $proceso = Proceso::find($sim);
            $pdf = Pdf::loadView('Reportes.resumen_biometrico', compact('res', 'proceso','total','groupByColumns'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');
            
            $rutaCarpeta = public_path("/documentos/$sim/reportes-biometrico/");
            $rutaArchivo = $rutaCarpeta . 'ReporteBiometrico_' . date('Y-m-d_H-i-s') .auth()->id(). '.pdf';
        
            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }
            file_put_contents($rutaArchivo, $pdf->output());                    
            return $pdf->stream(date('d/m/Y H:i:s')." ".auth()->id()." Resumen de biometrico.pdf");
        }else{
            return response()->json([
                'success' => true,
                'data' => $res,
                'total_general' => $total,
                'total_esperado' => $totales,
            ]);
        }

    }


}
