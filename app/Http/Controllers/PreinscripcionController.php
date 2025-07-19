<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\TipoProceso;
use App\Models\Preinscripcion;
use App\Models\Documento;
use App\Models\Postulante;
use App\Models\AvancePostulante;
use App\Models\Paso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\CarbonInterface;
use Inertia\Inertia;
use Carbon\Carbon;

use setasign\Fpdi\Fpdi;

class PreinscripcionController extends Controller
{
  private $fondo;

  public function __construct()
  {
      $this->fondo = public_path('imagenes/general-agua.jpg');
  }

  public function index()
  {
      return Inertia::render('Preinscripcion/index');
  }

  public function getProcesos(Request $request)
  {
    $proceso = 0;
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
    try {
        DB::beginTransaction();
            if($request->id_anterior != 'null'){
                $pre = Preinscripcion::create([
                    'id_postulante'=> $request->id_postulante,
                    'id_programa' => $request->programa,
                    'id_proceso' => $request->id_proceso,
                    'id_modalidad' => $request->modalidad,
                    'id_anterior' => $request->id_anterior,
                    'estado' => 1
                ]);
            }else{
                $pre = Preinscripcion::create([
                    'id_postulante'=> $request->id_postulante,
                    'id_programa' => $request->programa,
                    'id_proceso' => $request->id_proceso,
                    'id_modalidad' => $request->modalidad,
                    'estado' => 1
                ]);
            }


            $doc = [];
            $dooc = Documento::where('id_postulante', $request->id_postulante)->first();

            if ($dooc == []) {
                $doc = Documento::create([
                    'codigo' => $request->codigo_certificado,
                    'nombre' => 'CERT. DE ESTUDIOS',
                    'numero' => 1,
                    'observacion' => $request->tipo_certificado,
                    'id_postulante' => $request->id_postulante,
                    'id_tipo_documento' => 1,
                    'estado' => 1,
                    'url' => '',
                    'fecha' => date('Y-m-d')
                ]);

             }else{

                if($dooc->verificado == 0 )
                {
                    $dooc->codigo = $request->codigo_certificado;
                    $dooc->nombre = 'CERT. DE ESTUDIOS';
                    $dooc->numero = 1;
                    $dooc->observacion = $request->tipo_certificado;
                    $dooc->id_postulante = $request->id_postulante;
                    $dooc->id_tipo_documento = 1;
                    $dooc->estado = 1;
                    $dooc->verificado = 0;
                    $dooc->url = '';
                    $dooc->fecha = date('Y-m-d');
                    $dooc->save();
                }
            }

            if($request->codigo_medico != null ){
                $d = Documento::create([
                    'codigo' => $request->codigo_medico,
                    'nombre' => 'EX MEDICO',
                    'numero' => 1,
                    'observacion' => 'EX MEDICO',
                    'id_postulante' => $request->id_postulante,
                    'id_tipo_documento' => 2,
                    'estado' => 1,
                    'url' => '',
                    'fecha' => date('Y-m-d')
                ]);
            }

            $resultado = AvancePostulante::where('id_proceso', $request->id_proceso)
            ->where('dni_postulante', $request->dni)
            ->exists();

            if($resultado){
                print("Avance ya registrado");
            }else{
                $ava = AvancePostulante::create([
                    'dni_postulante'=> $request->dni,
                    'id_proceso' => $request->id_proceso,
                    'avance' => 1,
                ]);
            }

        DB::commit();
        return response()->json(['message' => 'Preinscripción exitosa'], 200);

    }
    catch (\Exception $e) {
        DB::rollBack();
        // Obtener información completa del error
        $errorMessage = $e->getMessage();
        $errorFile = $e->getFile();
        $errorLine = $e->getLine();

        // Crear una respuesta con los detalles del error
        $errorResponse = [
            'error' => $errorMessage,
            'file' => $errorFile,
            'line' => $errorLine
        ];

        // Devolver la respuesta con los detalles del error
        return response()->json($errorResponse, 500);
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

  public function savePasos(Request $request) {
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

    public function pdfvocacional($dni) {
        $res = Preinscripcion::select(
            'postulante.id as idP',
            'postulante.nro_doc', 'postulante.primer_apellido', 'postulante.segundo_apellido',
            'postulante.nombres', 'programa.nombre AS programa', 'procesos.nombre AS proceso',
            'pre_inscripcion.id_proceso as id_proceso'
            )
        ->join ('postulante','postulante.id','pre_inscripcion.id_postulante')
        ->join ('procesos','procesos.id','pre_inscripcion.id_proceso')
        ->join ('programa','programa.id','pre_inscripcion.id_programa')
        ->where('postulante.nro_doc','=', $dni)
        ->get();

        //$name = "cepre2023-II";
        $name = "general2023-II";
        // if($res[0]->id_proceso == 5 ){ $name = "general2023-II"; }
        $data = $res[0];
        $pdf = Pdf::loadView('vocacional.constanciavocacional', compact('data'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();


        $rutaCarpeta = public_path('/documentos/'.$name.'/'.$res[0]->nro_doc);

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
            'url' => 'documentos/'.$name.'/'.$res[0]->nro_doc.'/'.'constancia vocacional-1.pdf',
            'fecha' => date('Y-m-d')
        ]);

        file_put_contents(public_path('/documentos/'.$name.'/'.$res[0]->nro_doc.'/').'constancia vocacional-1.pdf', $output);
        return $pdf->stream();
    }


    public function pdfsolicitud($pro, $dni) {

            $carreras_previas = DB::select("SELECT codigo, cod_car, nombre, condicion FROM carreras_previas
            WHERE dni_postulante = $dni");


            $preinscrito = DB::select("SELECT COUNT(*) AS cont FROM pre_inscripcion
            JOIN postulante ON postulante.id = pre_inscripcion.id_postulante
            WHERE postulante.nro_doc = $dni AND pre_inscripcion.id_proceso = $pro");

            $res = Preinscripcion::select(
                'tipo_documento_identidad.nombre AS tipo_doc',
                'postulante.direccion',
                'postulante.id as idP',
                'postulante.nro_doc as dni',
                'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido',
                'postulante.anio_egreso AS egreso',
                'modalidad.id as id_modalidad',
                'modalidad.nombre as modalidad',
                'distritos.nombre AS distrito',
                'procesos.id as id_proceso',
                'procesos.nombre AS proceso',
                'procesos.id_modalidad_proceso',
                'procesos.fecha_examen AS fecha_examen',
                'programa.nombre AS programa',
                'carreras_previas.codigo as cod_car',
                'carreras_previas.nombre as nom_car',
                'anios.nombre as nombre_anio'
            )
            ->leftjoin ('postulante', 'postulante.id', '=','pre_inscripcion.id_postulante')
            ->join ('procesos', 'procesos.id', '=','pre_inscripcion.id_proceso')
            ->join ('programa', 'programa.id', '=','pre_inscripcion.id_programa')
            ->join ('modalidad', 'modalidad.id', '=','pre_inscripcion.id_modalidad')
            ->join ('ubigeo', 'ubigeo.ubigeo', '=','postulante.ubigeo_residencia')
            ->join ('distritos', 'distritos.id', '=','ubigeo.id_distrito')
            ->join ('tipo_documento_identidad','tipo_documento_identidad.id', '=', 'postulante.tipo_doc')
            ->leftjoin('anios','anios.anio','=','procesos.anio')
            ->leftjoin ('carreras_previas','carreras_previas.id', '=', 'pre_inscripcion.id_anterior')
            ->where('pre_inscripcion.id_proceso','=', $pro)
            ->where('postulante.nro_doc','=', $dni)->get();

            if (count($res) === 0) {
                return "No registrado";
            }else {
                $data = $res[0];

                setlocale(LC_TIME, 'es_ES.utf8');
                $date = Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
                $pdf = Pdf::loadView('solicitud.solicitud', ['data'=>$data, 'date'=>$date,'carreras_previas'=>$carreras_previas, 'fondo'=>$this->fondo]);
                $pdf->setPaper('A4', 'portrait');
                $output = $pdf->output();

                $rutaCarpeta = public_path('/documentos/'.$pro.'/preinscripcion/solicitudes/');

                if (!File::exists($rutaCarpeta)) {
                    File::makeDirectory($rutaCarpeta, 0755, true, true);
                }

                if($preinscrito[0]->cont == 0){

                    $doc = Documento::create([
                        'codigo' => '23-2-SOL-'.$res[0]->dni.'-1',
                        'nombre' => 'SOLICITUD DE POSTULACIÓN',
                        'numero' => 1,
                        'id_postulante' => $res[0]->idP,
                        'id_tipo_documento' => 6,
                        'estado' => 1,
                        'url' => 'documentos/'.$pro.'/'.'/preinscripcion/solicitudes/'.$res[0]->dni.'.pdf',
                        'fecha' => date('Y-m-d')
                    ]);
                }

                file_put_contents(public_path('/documentos/'.$pro.'/preinscripcion/solicitudes/').$res[0]->dni.'.pdf', $output);
                return $pdf->download('solicitud-postulante.pdf');

            }

        }


        public function pdfsolicitudAdmin($dni) {

            $carreras_previas = DB::select("SELECT codigo, cod_car, nombre, condicion FROM carreras_previas
            WHERE dni_postulante = $dni");


            $preinscrito = DB::select("SELECT COUNT(*) AS cont FROM pre_inscripcion
            JOIN postulante ON postulante.id = pre_inscripcion.id_postulante
            WHERE postulante.nro_doc = $dni AND pre_inscripcion.id_proceso = ".auth()->user()->id_proceso);

            $res = Preinscripcion::select(
                'tipo_documento_identidad.nombre AS tipo_doc',
                'postulante.direccion',
                'postulante.id as idP',
                'postulante.nro_doc as dni',
                'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido',
                'postulante.anio_egreso AS egreso',
                'modalidad.id as id_modalidad',
                'modalidad.nombre as modalidad',
                'distritos.nombre AS distrito',
                'procesos.id as id_proceso',
                'procesos.nombre AS proceso',
                'procesos.id_modalidad_proceso',
                'procesos.fecha_examen AS fecha_examen',
                'programa.nombre AS programa',
                'carreras_previas.codigo as cod_car',
                'carreras_previas.nombre as nom_car',
                'anios.nombre as nombre_anio'
            )
            ->leftjoin ('postulante', 'postulante.id', '=','pre_inscripcion.id_postulante')
            ->join ('procesos', 'procesos.id', '=','pre_inscripcion.id_proceso')
            ->join ('programa', 'programa.id', '=','pre_inscripcion.id_programa')
            ->join ('modalidad', 'modalidad.id', '=','pre_inscripcion.id_modalidad')
            ->join ('ubigeo', 'ubigeo.ubigeo', '=','postulante.ubigeo_residencia')
            ->join ('distritos', 'distritos.id', '=','ubigeo.id_distrito')
            ->join ('tipo_documento_identidad','tipo_documento_identidad.id', '=', 'postulante.tipo_doc')
            ->leftjoin('anios','anios.anio','=','procesos.anio')
            ->leftjoin ('carreras_previas','carreras_previas.id', '=', 'pre_inscripcion.id_anterior')
            ->where('pre_inscripcion.id_proceso','=', auth()->user()->id_proceso)
            ->where('postulante.nro_doc','=', $dni)->get();

            if (count($res) === 0) {
                return "No registrado";
            }else {
                $data = $res[0];

                setlocale(LC_TIME, 'es_ES.utf8');
                $date = Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
                $pdf = Pdf::loadView('solicitud.solicitud', ['data'=>$data, 'date'=>$date,'carreras_previas'=>$carreras_previas, 'fondo'=>$this->fondo]);
                $pdf->setPaper('A4', 'portrait');
                $output = $pdf->output();

                $rutaCarpeta = public_path('/documentos/'.auth()->user()->id_proceso.'/preinscripcion/solicitudes/');

                if (!File::exists($rutaCarpeta)) {
                    File::makeDirectory($rutaCarpeta, 0755, true, true);
                }

                if($preinscrito[0]->cont == 0){

                    $doc = Documento::create([
                        'codigo' => '23-2-SOL-'.$res[0]->dni.'-1',
                        'nombre' => 'SOLICITUD DE POSTULACIÓN',
                        'numero' => 1,
                        'id_postulante' => $res[0]->idP,
                        'id_tipo_documento' => 6,
                        'estado' => 1,
                        'url' => 'documentos/'.auth()->user()->id_proceso.'/'.'/preinscripcion/solicitudes/'.$res[0]->dni.'.pdf',
                        'fecha' => date('Y-m-d')
                    ]);
                }

                file_put_contents(public_path('/documentos/'.auth()->user()->id_proceso.'/preinscripcion/solicitudes/').$res[0]->dni.'.pdf', $output);
                return $pdf->stream('solicitud-postulante.pdf');

            }

        }




    public function getPreinscripcionesAdmin(Request $request) {

        $query_where = [];

        if ($request->programa) array_push($query_where,[DB::raw('pre_inscripcion.id_programa'), '=', $request->programa]);
        array_push($query_where,[DB::raw('pre_inscripcion.id_proceso'), '=', auth()->user()->id_proceso]);


        $res = Preinscripcion::select(
            'pre_inscripcion.id as id', 'postulante.id as id_postulante', 'postulante.nro_doc AS dni',
            'postulante.nombres AS nombres', 'procesos.id as id_proceso',
            'postulante.primer_apellido AS paterno', 'postulante.segundo_apellido AS materno',
            'programa.nombre as programa', 'pre_inscripcion.id_programa as id_programa',
            'modalidad.id as id_modalidad', 'modalidad.nombre as modalidad', 'procesos.nombre AS proceso',
            'pre_inscripcion.created_at as fecha', 'postulante.sexo',
            'inscripciones.estado'
        )
        ->join('postulante','pre_inscripcion.id_postulante', 'postulante.id')
        ->leftJoin('inscripciones', function($join) {
            $join->on('inscripciones.id_postulante', '=', 'postulante.id')
                 ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso);
        })
        ->join('programa','pre_inscripcion.id_programa', 'programa.id')
        ->join('modalidad','pre_inscripcion.id_modalidad', 'modalidad.id')
        ->join('procesos','pre_inscripcion.id_proceso', 'procesos.id')
        ->where($query_where)
        ->where(function ($query) use ($request) {
            return $query
              ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%')
              ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%');
        })
        ->paginate($request->paginashoja);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);

    }


    public function Actualizar(Request $request){

        $preinscripcion = Preinscripcion::find($request->id);

        if( $preinscripcion->id_programa != $request->id_programa) {
            $preinscripcion->observacion = "$preinscripcion->observacion - Cambio de programa de $preinscripcion->id_programa a $request->id_programa ";
            $preinscripcion->id_programa = $request->id_programa;
        }
        if ( $preinscripcion->id_modalidad != $request->id_modalidad ) {
            $preinscripcion->observacion = "$preinscripcion->observacion, Cambio de modalidad de $preinscripcion->id_modalidad a $request->id_modalidad";
            $preinscripcion->id_modalidad = $request->id_modalidad;
        }
        if($request->observacion != ''){
            $preinscripcion->observacion = "$preinscripcion->observacion, ( $request->observacion )";
        }
        $preinscripcion->save();
        $this->pdfsolicitud(auth()->user()->id_proceso,$request->dni);

        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }


    public function actualizarSexo(Request $request ) {

        $postulante = Postulante::find($request->id_postulante);
        $postulante->sexo = $request->sexo;
        $postulante->save();

        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);

    }



    public function generarCaptcha()
    {
        $caracteresPermitidos = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123';
        $captchaText = substr(str_shuffle($caracteresPermitidos), 0, 6);
        session(['captcha' => $captchaText]);
        return response()->json(['captcha' => $captchaText]);
    }


    public function estaPreinscrito($id_proceso, $dni){
        $preinscripcion = Preinscripcion::where('postulante.nro_doc', $dni)
        ->where('id_proceso', $id_proceso)
        ->join('postulante','postulante.id','pre_inscripcion.id_postulante')
        ->first();

        if($preinscripcion){ $this->response['estado'] = true;
        }else{ $this->response['estado'] = false; }

        return response()->json($this->response, 200);
    }

    public function pasoRegistrado ($id_proceso, $dni){
        $paso = Paso::join('postulante', 'paso.postulante', '=', 'postulante.id')
        ->where('paso.proceso', $id_proceso)
        ->where('postulante.nro_doc', $dni)
        ->orderByDesc('paso.nro')
        ->select('paso.nro', 'paso.avance', 'paso.postulante')
        ->first();

        if($paso){
            $this->response['estado'] = true;
            $this->response['datos'] = $paso;
            return response()->json($this->response, 200);
        }
        else{
            $this->response['estado'] = false;
            return response()->json($this->response, 200);
        }
    }



    public function pdfsolicitudExtranjeros($pro, $dni) {

        $carreras_previas = DB::select("SELECT codigo, cod_car, nombre, condicion FROM carreras_previas
        WHERE dni_postulante = $dni");

        $preinscrito = DB::select("SELECT COUNT(*) AS cont FROM pre_inscripcion
        JOIN postulante ON postulante.id = pre_inscripcion.id_postulante
        WHERE postulante.nro_doc = $dni AND pre_inscripcion.id_proceso = $pro");

        $res = Preinscripcion::select(
            'tipo_documento_identidad.nombre AS tipo_doc',
            'postulante.direccion',
            'postulante.id as idP',
            'postulante.nro_doc as dni',
            'postulante.nombres', 'postulante.primer_apellido', 'postulante.segundo_apellido',
            'postulante.anio_egreso AS egreso',
            'modalidad.id as id_modalidad',
            'modalidad.nombre as modalidad',
            'distritos.nombre AS distrito',
            'procesos.id as id_proceso',
            'procesos.nombre AS proceso',
            'procesos.id_modalidad_proceso',
            'procesos.fecha_examen AS fecha_examen',
            'programa.nombre AS programa',
            'carreras_previas.codigo as cod_car',
            'carreras_previas.nombre as nom_car',
            'paises.nombre AS pais'
        )
          ->leftjoin ('postulante', 'postulante.id', '=','pre_inscripcion.id_postulante')
          ->join ('procesos', 'procesos.id', '=','pre_inscripcion.id_proceso')
          ->join ('programa', 'programa.id', '=','pre_inscripcion.id_programa')
          ->join ('modalidad', 'modalidad.id', '=','pre_inscripcion.id_modalidad')
          ->leftjoin ('ubigeo', 'ubigeo.ubigeo', '=','postulante.ubigeo_residencia')
          ->leftjoin ('distritos', 'distritos.id', '=','ubigeo.id_distrito')
          ->join ('paises', 'paises.id', '=','postulante.id_pais')
          ->join ('tipo_documento_identidad','tipo_documento_identidad.id', '=', 'postulante.tipo_doc')
          ->leftjoin ('carreras_previas','carreras_previas.id', '=', 'pre_inscripcion.id_anterior')
          ->where('pre_inscripcion.id_proceso','=', $pro)
          ->where('postulante.nro_doc','=', $dni)->get();

        if (count($res) === 0) {
            return "No registrado";
        }else {
            $data = $res[0];

            setlocale(LC_TIME, 'es_ES.utf8');
            $date = Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
            $pdf = Pdf::loadView('solicitud.solicitud', ['data'=>$data, 'date'=>$date,'carreras_previas'=>$carreras_previas, 'fondo'=>$this->fondo]);
            $pdf->setPaper('A4', 'portrait');
            $output = $pdf->output();

            $rutaCarpeta = public_path('/documentos/'.$pro.'/preinscripcion/solicitudes/');

            if (!File::exists($rutaCarpeta)) {
                File::makeDirectory($rutaCarpeta, 0755, true, true);
            }

            if($preinscrito[0]->cont == 0){

                $doc = Documento::create([
                    'codigo' => '24-2-SOL-'.$res[0]->dni.'-1',
                    'nombre' => 'SOLICITUD DE POSTULACIÓN',
                    'numero' => 1,
                    'id_postulante' => $res[0]->idP,
                    'id_tipo_documento' => 6,
                    'estado' => 1,
                    'url' => 'documentos/'.$pro.'/'.'/preinscripcion/solicitudes/'.$res[0]->dni.'.pdf',
                    'fecha' => date('Y-m-d')
                ]);
            }

            file_put_contents(public_path('/documentos/'.$pro.'/preinscripcion/solicitudes/').$res[0]->dni.'.pdf', $output);
            return $pdf->download('solicitud-postulante.pdf');
        }

    }



    public function Eliminar(Request $request){

        $preinscripcion = Preinscripcion::find($request->id);
        $preinscripcion->delete();

        $this->response['titulo'] = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }






}
