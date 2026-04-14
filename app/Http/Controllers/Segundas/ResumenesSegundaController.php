<?php
namespace App\Http\Controllers\Segundas;
use DB;
use App\Models\Preinscripcion;
use App\Models\Proceso;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Pdf;

class ResumenesSegundaController extends Controller
{
    
    public function getResumenPreinscripcion( ) {

        $proceso = Proceso::find(auth()->user()->id_proceso);
        $query_where = [];

        if (auth()->user()->programas != null) {
            $array = json_decode(auth()->user()->programas, true);
            if (!empty($array)) { $query_where[] = ['pre_inscripcion.id_programa', $array]; }
        }

        $datos = Preinscripcion::select('programa.nombre AS programa', 'programa.id AS codigo', DB::raw('COUNT(*) AS cant'))
            ->join('programa', 'programa.id', '=', 'pre_inscripcion.id_programa')
            ->when(!empty($query_where), function ($query) use ($query_where) {
                foreach ($query_where as $condition) {
                    $query->whereIn($condition[0], $condition[1]);
                }
            })
            ->where('pre_inscripcion.id_proceso', auth()->user()->id_proceso)
            ->groupBy('programa.nombre','programa.id')
            ->orderByDesc('cant')
            ->get();
        
            $total = $datos->sum('cant');

            
            
            $pdf = Pdf::loadView('Segundas.Resumenes.resumen_preinscripcion', compact('datos','total','proceso'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');
        

            // $rutaCarpeta = public_path("/documentos/".auth()->user()->id_proceso."/reportes/");
            // $rutaArchivo = $rutaCarpeta . 'ReporteProgramas_' . date('Y-m-d_H-i-s') .auth()->id(). '.pdf';
        
            // if (!file_exists($rutaCarpeta)) {
            //     mkdir($rutaCarpeta, 0755, true);
            // }
        
            // file_put_contents($rutaArchivo, $pdf->output());
        
            return $pdf->stream(date('d/m/Y H:i:s')." Resumen por programa.pdf");
    
    }

    public function getPreinscripciones(Request $request) {

        $proceso = Proceso::find(auth()->user()->id_proceso);
        $query_where = [];
        if (auth()->user()->programas != null) {
            $array = json_decode(auth()->user()->programas, true);
            if (!empty($array)) {
                $query_where[] = ['pre_inscripcion.id_programa', $array];
            }
        }

        if ($request->programa) {
            $query_where[] = ['pre_inscripcion.id_programa', '=', $request->programa];
        }
        $query_where[] = ['pre_inscripcion.id_proceso', '=', auth()->user()->id_proceso];

        $res = Preinscripcion::select(
            'pre_inscripcion.id as id', 'postulante.id as id_postulante', 'postulante.nro_doc AS dni',
            'postulante.nombres AS nombres', 'postulante.primer_apellido AS paterno',
            'postulante.segundo_apellido AS materno', 'postulante.celular', 'postulante.email',
            'programa.nombre as programa', 'pre_inscripcion.id_programa as id_programa',
            'modalidad.id as id_modalidad', 'modalidad.nombre as modalidad', 'procesos.nombre AS proceso',
            'pre_inscripcion.created_at as fecha', 'postulante.sexo',
            'inscripciones.estado', 'pre_inscripcion.observacion'
        )
        ->join('postulante', 'pre_inscripcion.id_postulante', 'postulante.id')
        ->leftJoin('inscripciones', function ($join) {
            $join->on('inscripciones.id_postulante', '=', 'postulante.id')
                ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso);
        })
        ->join('programa', 'pre_inscripcion.id_programa', 'programa.id')
        ->join('modalidad', 'pre_inscripcion.id_modalidad', 'modalidad.id')
        ->join('procesos', 'pre_inscripcion.id_proceso', 'procesos.id')
        ->when(!empty($query_where), function ($query) use ($query_where) {
            foreach ($query_where as $condition) {
                if (is_array($condition[1])) {
                    $query->whereIn($condition[0], $condition[1]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2] ?? null);
                }
            }
        })
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%')
                ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%');
        })
        ->orderBy('programa.nombre')
        ->orderBy('postulante.primer_apellido')
        ->orderBy('postulante.segundo_apellido')
        ->get();
    
        $datos = $res->groupBy('programa')->map(function ($items, $programa) {
            return [    
                'programa' => $programa,
                'data' => $items->toArray()
            ];
        })->values();

        // return response()->json($datos);

        $pdf = Pdf::loadView('Segundas.Detalle.detalle_preinscripcion', compact('datos','proceso'));
        $pdf->getDomPDF()->set_option("isPhpEnabled", true);
        $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()), 
            "Resumen por programa.pdf",
            ['Content-Type' => 'application/pdf']
        );

        return $pdf->stream(date('d/m/Y H:i:s')." Resumen por programa.pdf");
    
    }



}
