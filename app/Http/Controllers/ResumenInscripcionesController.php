<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Exports\ResumenInscripcionExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Pdf;

class ResumenInscripcionesController extends Controller
{
    public function resumenInscripciones(Request $request)
    {
        $groupByColumns = $request->input('group_by', []);
        $columnsMap = [
            'programa' => 'pro.nombre',
            'modalidad' => 'moda.nombre',
            'sexo' => DB::raw("IF(pos.sexo = 1, 'M', 'F')"),
            'area' => 'pro.area',
            'usuario' => DB::raw("concat(usu.name,' ',usu.paterno,' ',usu.materno)"),
        ];

        $query = DB::table('inscripciones as ins')
            ->join('postulante as pos', 'ins.id_postulante', '=', 'pos.id')
            ->join('programa as pro', 'pro.id', '=', 'ins.id_programa')
            ->join('modalidad as moda', 'ins.id_modalidad', '=', 'moda.id')
            ->join('users as usu', 'usu.id', '=', 'ins.id_usuario')
            ->where('ins.id_proceso', auth()->user()->id_proceso)
            ->where('ins.estado', 0);

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

        $total = DB::table('inscripciones as ins')
            ->where('ins.id_proceso', auth()->user()->id_proceso)
            ->where('ins.estado', 0)
            ->count();

        if($request->excel == 1){

            $headings = array_map('strtoupper', $groupByColumns);
            $headings[] = 'TOTAL';
            return Excel::download(new ResumenInscripcionExport($res, $headings), 'reporte.xlsx');
            
        }

        if( $request->descargar == 1){
            $sim = auth()->user()->id_proceso;
            $proceso = Proceso::find($sim);
            $pdf = Pdf::loadView('Reportes.inscripciones', compact('res', 'proceso','total','groupByColumns'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');
            
            $rutaCarpeta = public_path("/documentos/$sim/reportes-inscripcion/");
            $rutaArchivo = $rutaCarpeta . 'ReporteInscripción_' . date('Y-m-d_H-i-s') .auth()->id(). '.pdf';
        
            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }
            file_put_contents($rutaArchivo, $pdf->output());                    
            return $pdf->stream(date('d/m/Y H:i:s')." ".auth()->id()." Resumen de inscripciones.pdf");
        }else{
            return response()->json([
                'success' => true,
                'data' => $res,
                'total_general' => $total
            ]);
        }




        

    }


    

}
