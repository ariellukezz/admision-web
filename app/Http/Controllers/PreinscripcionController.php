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


class PreinscripcionController extends Controller
{
  public function index()
  {
      return Inertia::render('Preinscripcion/index');        
  }

  public function getProcesos(Request $request)
  {
    $query_where = [];
    $res = Proceso::select(
        'procesos.id', 'procesos.nombre','procesos.estado','procesos.anio',
        'filial.id as id_sede', 'filial.nombre as sede',
        'tipo_proceso.id as id_tipo', 'tipo_proceso.nombre as tipo',
        'modalidad_proceso.id as id_modalidad', 'modalidad_proceso.nombre as modalidad'
    )
      ->join ('filial', 'filial.id', '=','procesos.id_sede_filial')
      ->join ('tipo_proceso', 'tipo_proceso.id', '=','procesos.id_tipo_proceso')
      ->join ('modalidad_proceso', 'modalidad_proceso.id', '=','procesos.id_modalidad_proceso')
      ->where($query_where)
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('procesos.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('filial.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('modalidad_proceso.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('procesos.anio', 'LIKE', '%' . $request->term . '%');
      })->orderBy('procesos.id', 'DESC')
      ->paginate(10);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


  public function preinscribir(Request $request)
  {
      $pre = Preinscripcion::create([
        'id_postulante'=> $request->id_postulante,
        'id_programa' => $request->programa,
        'id_proceso' => 4,
        'id_modalidad' => $request->modalidad,
        'estado' => 1,
        'codigo_seguridad' => date('Y')
      ]);

      try{
          if($request->hasFile('img')){
            // $rutaCarpeta = public_path('/documentos/cepre2023-II/'.$res[0]->dni);

            // if (!File::exists($rutaCarpeta)) {
            //     File::makeDirectory($rutaCarpeta, 0755, true, true);
            // }

            $file = $request->file('img');
              $file_name =$file->getClientoriginalName();
              //$file->move(public_path('documentos/cepre2023-II/'.$request->programa.'/'.$request->), time().'-'.'$file_name');

              //2023 
              $doc = Documento::create([
                  'codigo' => 'PRE'.$request->id_postulante, 
                  'nombre' => $file_name,
                  'id_postulante' => $request->id_postulante,
                  'id_tipo_documento' => 2,
                  'estado' => 1,
                  'url' => 'documentos/certificados/'.$request->programa.'/'.time().'-'.$file_name,
                  'fecha' => date('Y-m-d'),
                  'observacion' => $request->tipo_certificado
              ]);
              return response()->json(['menssje'=>'file upload success'], 200);
          }
      }catch(\Exception $e){
          return response()->json([
              'mssage'=>$e->getMessage()
          ]);
      }

  }

  

  public function saveProceso(Request $request ) {

        $proceso = null;
        if (!$request->id) {
            $proceso = Proceso::create([
                'nombre' => $request->nombre,
                'id_tipo_proceso' => $request->tipo,
                'id_modalidad_proceso' => $request->modalidad,
                'anio' => $request->anio,
                'estado' => $request->estado,
                'id_sede_filial' => $request->sede,
                'id_usuario' => auth()->id()
            ]);
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Proceso '.$proceso->nombre.' creado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $proceso;
        } else {

            $proceso = Proceso::find($request->id);
            $proceso->nombre = $request->nombre;
            $proceso->id_tipo_proceso = $request->tipo;
            $proceso->id_modalidad_proceso = $request->modalidad;
            $proceso->anio = $request->anio;
            $proceso->estado = $request->estado;
            $proceso->id_sede_filial = $request->sede;
            $proceso->id_usuario = auth()->id();
            $proceso->save();

            $this->response['titulo'] = '!REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Proceso '.$proceso->nombre.' modificado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $proceso;
        }

    return response()->json($this->response, 200);
  }

  public function deleteProceso($id){
    $proceso = Proceso::find($id);
    $p = $proceso;
    $proceso->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'Proceso '.$p->nombre.' eliminado con exito';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }



  public function savePasos(Request $request ) {

      $pasos = null;
      if (!$request->id) {
          $pasos = Paso::create([
              'nombre' => $request->nombre,
              'nro' => $request->nro,
              'avance' => $request->avance, 
              'anvance_general' => $request->avance_general,
              'postulante' => $request->postulante,
              'proceso' => $request->proceso,
          ]);
          $this->response['tipo'] = 'success';
          $this->response['titulo'] = 'PASO REGISTRADO';
          $this->response['mensaje'] = 'Proceso '.$pasos->nombre.' creado con exito';
          $this->response['estado'] = true;
          $this->response['datos'] = $pasos;
          
      } else {
          $pasos = Paso::find($request->id);
          $pasos->nombre = $request->nombre;
          $pasos->nro = $request->nro;
          $pasos->avance = $request->avance; 
          $pasos->avance_general = $request->avance_general;
          $pasos->postulante = $request->postulante;
          $pasos->proceso = $request->proceso;
          $pasos->save();
            $this->response['tipo'] = 'info';
            $this->response['titulo'] = 'PASO ACTUALIZADO';
            $this->response['mensaje'] = 'Datos del '.$pasos->nombre.' actualizados';
            $this->response['estado'] = true;
            $this->response['datos'] = $pasos;
          }
    
    }

    public function pdf(){

        $data = "";
        $pdf = Pdf::loadView('preinscripcion.pdf', compact('data'));
        
        return $pdf->stream();
        
    }

    public function pdfvocacional( ) {
        $data = "";
        $pdf = Pdf::loadView('vocacional.constanciavocacional', compact('data'));
        return $pdf->stream();
    }

    public function pdfsolicitud( ) {

        $res = Preinscripcion::select(
            'postulante.nro_doc as dni', 
            'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido',
            'postulante.anio_egreso AS egreso',
            'colegios.nombre AS colegio',
            'modalidad.nombre as modalidad', 
            'distritos.nombre AS distrito',
            'procesos.nombre AS proceso',
            'programa.nombre AS programa' 
        )
          ->join ('postulante', 'postulante.id', '=','pre_inscripcion.id_postulante')
          ->join ('procesos', 'procesos.id', '=','pre_inscripcion.id_proceso')
          ->join ('programa', 'programa.id', '=','pre_inscripcion.id_programa')
          ->join ('modalidad', 'modalidad.id', '=','pre_inscripcion.id_modalidad')
          ->join ('colegios', 'colegios.id', '=','postulante.id_colegio')
          ->join ('ubigeo', 'ubigeo.ubigeo', '=','colegios.ubigeo')
          ->join ('distritos', 'distritos.id', '=','ubigeo.id_distrito')
          ->where('postulante.nro_doc','=', '70757838')->get();

        $pos = DB::select('SELECT tipo_documento_identidad.nombre AS tipo_doc, postulante.direccion, distritos.nombre AS distrito_residencia FROM postulante
        JOIN ubigeo ON postulante.ubigeo_residencia = ubigeo.ubigeo
        JOIN distritos ON ubigeo.id_distrito = distritos.id
        JOIN tipo_documento_identidad ON tipo_documento_identidad.id = postulante.tipo_doc
        WHERE postulante.nro_doc = ' .'70757838');

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

        file_put_contents(public_path('/documentos/cepre2023-II/'.$res[0]->dni.'/').'solicitud.pdf', $output);
        return $pdf->stream();

    }





}