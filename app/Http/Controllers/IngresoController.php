<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\TipoProceso;
use App\Models\Preinscripcion;
use App\Models\Documento;
use App\Models\Paso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\File;
use setasign\Fpdi\Fpdi;


class IngresoController extends Controller
{


    public function pdf(){

        $data = "";
        $pdf = Pdf::loadView('ingreso.constancia', compact('data'));
        return $pdf->stream();
            
    }

    public function pdfvocacional($dni) {
        $res = Preinscripcion::select(
            'postulante.id as idP',
            'postulante.nro_doc', 'postulante.primer_apellido', 'postulante.segundo_apellido',
            'postulante.nombres', 'programa.nombre AS programa', 'procesos.nombre AS proceso')
        ->join ('postulante','postulante.id','pre_inscripcion.id_postulante')
        ->join ('procesos','procesos.id','pre_inscripcion.id_proceso')
        ->join ('programa','programa.id','pre_inscripcion.id_programa')
        ->where('postulante.nro_doc','=', $dni)
        ->get();
        

        $data = $res[0];
        $pdf = Pdf::loadView('vocacional.constanciavocacional', compact('data'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();


        $rutaCarpeta = public_path('/documentos/cepre2023-II/'.$res[0]->nro_doc);

        if (!File::exists($rutaCarpeta)) {
            File::makeDirectory($rutaCarpeta, 0755, true, true);
        }

        $doc = Documento::create([
            'codigo' => '23-2-VOC-'.$res[0]->nro_doc.'-1', 
            'nombre' => 'CONSTANCIA VOCACIONAL',
            'numero' => 1,
            'id_postulante' => $res[0]->idP,
            'id_tipo_documento' => 7,
            'estado' => 1,
            'url' => 'documentos/cepre2023-II/'.$res[0]->nro_doc.'/'.'constancia vocacional-1.pdf',
            'fecha' => date('Y-m-d')
        ]);

        file_put_contents(public_path('/documentos/cepre2023-II/'.$res[0]->nro_doc.'/').'constancia vocacional-1.pdf', $output);
        return $pdf->stream();
    }

    public function pdfsolicitud($dni) {

        $res = Preinscripcion::select(
            'postulante.id as idP',
            'postulante.nro_doc as dni', 
            'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido',
            'postulante.anio_egreso AS egreso',
            'colegios.nombre AS colegio',
            'modalidad.nombre as modalidad', 
            'distritos.nombre AS distrito',
            'procesos.nombre AS proceso',
            'programa.nombre AS programa' 
        )
          ->leftjoin ('postulante', 'postulante.id', '=','pre_inscripcion.id_postulante')
          ->join ('procesos', 'procesos.id', '=','pre_inscripcion.id_proceso')
          ->join ('programa', 'programa.id', '=','pre_inscripcion.id_programa')
          ->join ('modalidad', 'modalidad.id', '=','pre_inscripcion.id_modalidad')
          ->join ('colegios', 'colegios.id', '=','postulante.id_colegio')
          ->join ('ubigeo', 'ubigeo.ubigeo', '=','colegios.ubigeo')
          ->join ('distritos', 'distritos.id', '=','ubigeo.id_distrito')
          ->where('postulante.nro_doc','=', $dni)->get();

        $pos = DB::select('SELECT tipo_documento_identidad.nombre AS tipo_doc, postulante.direccion, distritos.nombre AS distrito_residencia FROM postulante
        JOIN ubigeo ON postulante.ubigeo_residencia = ubigeo.ubigeo
        JOIN distritos ON ubigeo.id_distrito = distritos.id
        JOIN tipo_documento_identidad ON tipo_documento_identidad.id = postulante.tipo_doc
        WHERE postulante.nro_doc = ' .$dni);

        $data = $res[0];
        $dataP = $pos[0]; 
        setlocale(LC_TIME, 'es_ES.utf8'); // Establece la configuración regional en español
        // $date = strftime('%d de %B del %Y');
        $date = Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
        //$date = date('d \d\e F \d\e\l Y');
        $pdf = Pdf::loadView('solicitud.solicitud', compact('data','date','dataP'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();


        $rutaCarpeta = public_path('/documentos/cepre2023-II/'.$res[0]->dni);

        if (!File::exists($rutaCarpeta)) {
            File::makeDirectory($rutaCarpeta, 0755, true, true);
        }

        $doc = Documento::create([
            'codigo' => '23-2-SOL-'.$res[0]->dni.'-1', 
            'nombre' => 'SOLICITUD DE POSTULACIÓN',
            'numero' => 1,
            'id_postulante' => $res[0]->idP,
            'id_tipo_documento' => 6,
            'estado' => 1,
            'url' => 'documentos/cepre2023-II/'.$res[0]->dni.'/'.'solicitud-1.pdf',
            'fecha' => date('Y-m-d')
        ]);

        file_put_contents(public_path('/documentos/cepre2023-II/'.$res[0]->dni.'/').'solicitud-1.pdf', $output);
        return $pdf->stream();

    }

    public function UnirPDF($dni){

        $pdf = new Fpdi();
        
        $files = [
            public_path('/documentos/cepre2023-II/'.$dni.'/').'solicitud-1.pdf',
            public_path('/documentos/cepre2023-II/'.$dni.'/').'constancia vocacional-1.pdf',
            public_path('/documentos/cepre2023-II/'.$dni.'/').'certificado-1.pdf'
        ];

        foreach ($files as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $template = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($template);
            }
        }

        $outputFilePath = public_path('/documentos'.'/'.$dni.'.pdf');
        $pdf->Output($outputFilePath, 'F');

        return response()->download($outputFilePath);
        // return response()->download($outputFilePath)->deleteFileAfterSend();

    }





}