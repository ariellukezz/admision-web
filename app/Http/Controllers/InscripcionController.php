<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Postulante;
use App\Models\Inscripcion;
use App\Models\CertificadoFirma;
use App\Models\Proceso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use App\Exports\InscripcionExport;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;

class InscripcionController extends Controller
{
    public function index()
    {
        return Inertia::render('Inscripciones/impresion');
    }

    public function getPostulantes(Request $request)
    {
        $query_where = [];
        $res = Postulante::select(
            'postulante.nro_doc as value',
            DB::raw("CONCAT( postulante.nombres,' ',postulante.primer_apellido, postulante.primer_apellido) as label")
        )
            ->where($query_where)
            ->where(function ($query) use ($request) {
                return $query
                    ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%');
            })->orderBy('postulante.id', 'DESC')
            ->paginate(10);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getPostulanteByDni($dni){

        $sancionados = DB::table('sancionados')
            ->where('dni', $dni)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->exists();

        if( $sancionados ){
            $this->response['estado'] = true;
            $this->response['mensaje'] = "El postulante está observado";
            return response()->json($this->response, 200);
        }
        else {

            $res = DB::select("SELECT postulante.id as id_postulante, postulante.nro_doc AS dni, postulante.nombres,
            postulante.primer_apellido, postulante.segundo_apellido, postulante.sexo, postulante.fec_nacimiento,
            postulante.foto_url AS postulante_foto, postulante.revisado as postulante_revisado,
            programa.id AS id_programa, programa.nombre as programa, programa.codigo as cod_programa,
            colegios.id AS id_colegio, concat(colegios.gestion,".'" - "'.", colegios.nombre) AS colegio,
            colegios.id_gestion as id_gestion,
            modalidad.id AS id_modalidad, modalidad.nombre as modalidad,
            procesos.nombre AS proceso, procesos.id as id_proceso,
            departamento.nombre AS departamento, provincia.nombre AS provincia, distritos.nombre AS distrito
            FROM pre_inscripcion
            JOIN postulante ON pre_inscripcion.id_postulante = postulante.id
            JOIN colegios ON postulante.id_colegio = colegios.id
            JOIN programa ON programa.id = pre_inscripcion.id_programa
            JOIN modalidad ON modalidad.id = pre_inscripcion.id_modalidad
            JOIN procesos ON procesos.id = pre_inscripcion.id_proceso
            JOIN ubigeo ON postulante.ubigeo_residencia = ubigeo.ubigeo
            JOIN departamento ON ubigeo.id_departamento = departamento.id
            JOIN provincia ON ubigeo.id_provincia = provincia.id
            JOIN distritos ON ubigeo.id_distrito = distritos.id
            WHERE postulante.nro_doc = $dni AND postulante.estado = 1
            AND pre_inscripcion.id_proceso = ". auth()->user()->id_proceso);
            if(count($res) > 0 ){
                $edad = Carbon::parse($res[0]->fec_nacimiento)->age;

                if ($edad >= 18) {
                    if ($res[0]->postulante_revisado == 0 || $res[0]->postulante_revisado == null) {
                        $consulta = DB::table('consultas_reniec')
                            ->orderBy('cant', 'asc')
                            ->first();

                        if ($consulta) {
                            if (now()->toDateString() !== Carbon::parse($consulta->updated_at)->toDateString()) {
                                DB::table('consultas_reniec')
                                    ->where('id', $consulta->id)
                                    ->update(['cant' => 0, 'updated_at' => now()]);
                                $consulta->cant = 0;
                            }

                            DB::table('consultas_reniec')->where('id', $consulta->id)->increment('cant');
                            $token = $consulta->token;

                            $response = Http::withHeaders([
                                'Accept' => 'application/json',
                                'Authorization' => "Bearer $token",
                            ])->get("https://service7.unap.edu.pe/api/v1/reniec/consulta/" . $res[0]->dni);

                            if ($response->ok() && isset($response['data'])) {
                                $data = $response['data'];

                               if (!empty($data['foto'])) {
                                    $imageData = base64_decode($data['foto']);
                                    if ($imageData !== false) {
                                        $imageName = $res[0]->dni . '.jpg';
                                        $folderPath = public_path('fotos_postulante');

                                        if (!file_exists($folderPath)) {
                                            mkdir($folderPath, 0777, true);
                                        }

                                        $imagePath = $folderPath . '/' . $imageName;

                                        file_put_contents($imagePath, $imageData);

                                        $postulante = Postulante::find($res[0]->id_postulante);
                                        if ($postulante) {
                                            $postulante->primer_apellido = $data['apPrimer'];
                                            $postulante->segundo_apellido = $data['apSegundo'];
                                            $postulante->nombres = $data['prenombres'];
                                            $postulante->direccion = $data['direccion'];
                                            $postulante->revisado = 1;
                                            $postulante->foto_url = 'fotos_postulante/' . $imageName;
                                            $postulante->save();
                                            $res[0]->postulante_foto = 'fotos_postulante/'.$imageName;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $this->response['estado'] = true;
                $this->response['foto'] = 'documentos/'.auth()->user()->id_proceso.'/inscripciones/fotos/'.$dni.'.jpg';
                $this->response['huellaD'] = 'documentos/'.auth()->user()->id_proceso.'/inscripciones/huellas/'.$dni.'.jpg';
                $this->response['huellaI'] = 'documentos/'.auth()->user()->id_proceso.'/inscripciones/huellas/'.$dni.'x.jpg';
                $this->response['datos'] = $res[0];
                return response()->json($this->response, 200);
            }
            else{
                $this->response['estado'] = true;
                return response()->json($this->response, 200);
            }

        }

    }

    public function getApoderados($dni){
        $res = DB::select('SELECT apoderado.nro_documento, apoderado.paterno,
        apoderado.materno, apoderado.nombres, tipo_apoderado.nombre AS parentesco
        FROM apoderado
        JOIN postulante ON postulante.id = apoderado.id_postulante
        JOIN tipo_apoderado ON tipo_apoderado.id = apoderado.tipo_apoderado
        WHERE postulante.nro_doc = ' . $dni);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getVouchers($dni){
        $res = DB::select('SELECT nro_operacion AS operacion, fecha, hora, codigo, monto FROM comprobante
        WHERE estado = 1 AND ndoc_postulante = ' . $dni);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getDocumentos($dni){
        $res = DB::select('SELECT
        documento.id, documento.codigo, documento.nombre,
        documento.url, documento.estado, documento.verificado,
        tipo_documento.nombre  AS tipo
        FROM documento
        left JOIN tipo_documento ON tipo_documento.id = documento.id_tipo_documento
        JOIN postulante ON documento.id_postulante = postulante.id
        WHERE documento.id_tipo_documento IN (1,4)
        AND documento.codigo IS NOT null
        AND postulante.nro_doc  = ' . $dni);
        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getPreinscipciones($dni){

        $res = DB::select("SELECT
        pre_inscripcion.estado AS estado,
        programa.nombre AS programa,
        procesos.nombre AS proceso,
        modalidad.nombre AS modalidad
        FROM pre_inscripcion
        JOIN programa ON programa.id = pre_inscripcion.id_programa
        JOIN procesos ON procesos.id = pre_inscripcion.id_proceso AND procesos.id = ".auth()->user()->id_proceso ."
        JOIN modalidad ON modalidad.id = pre_inscripcion.id_modalidad
        JOIN postulante ON postulante.id = pre_inscripcion.id_postulante
        WHERE postulante.nro_doc =  $dni AND pre_inscripcion.id_proceso = ". auth()->user()->id_proceso );

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getInscripciones($dni){

        $res = DB::select("SELECT
        inscripciones.estado AS estado,
        programa.codigo as cod_programa,
        programa.nombre AS programa,
        procesos.nombre AS proceso,
        modalidad.nombre AS modalidad
        FROM inscripciones
        JOIN programa ON programa.id = inscripciones.id_programa
        JOIN procesos ON procesos.id = inscripciones.id_proceso
        JOIN modalidad ON modalidad.id = inscripciones.id_modalidad
        JOIN postulante ON postulante.id = inscripciones.id_postulante
        WHERE postulante.nro_doc = $dni AND inscripciones.id_proceso = ". auth()->user()->id_proceso);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function Inscribir(Request $request){

        $proceso = Proceso::find(auth()->user()->id_proceso);

        $prog = $request['postulante']['cod_programa'];
        $res = Inscripcion::where('codigo', 'like', $proceso->codigo_proceso.$prog.'%')
            ->max(\DB::raw('CAST(SUBSTRING(codigo, 7) AS UNSIGNED)')) + 1;
        $res = str_pad($res, 4, '0', STR_PAD_LEFT);

        $dni = $request['postulante']['dni_temp'];
        $fotoPath = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/fotos/' . $dni . '.jpg');
        $huellaIzquierdaPath = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . '.jpg');
        $huellaDerechaPath = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . 'x.jpg');

        $hasFoto = File::exists($fotoPath);
        $hasHuellaIzquierda = File::exists($huellaIzquierdaPath);
        $hasHuellaDerecha = File::exists($huellaDerechaPath);
        $missingFiles = [];

        if (!$hasFoto) { $missingFiles[] = 'foto'; }
        if (!$hasHuellaIzquierda) { $missingFiles[] = 'huella izquierda'; }
        if (!$hasHuellaDerecha) { $missingFiles[] = 'huella derecha'; }

        if (!empty($missingFiles)) {
            $this->response['estado'] = false;
            $this->response['mensaje'] = 'Faltan los siguientes archivos necesarios para completar la inscripción: ' . implode(', ', $missingFiles) . '.';
            return response()->json($this->response, 400);
        }

        $inscripcion = Inscripcion::create([
            'codigo' => $proceso->codigo_proceso . $prog . $res,
            'id_postulante'=> $request['postulante']['id'],
            'id_proceso'=> auth()->user()->id_proceso,
            'id_programa' => $request['postulante']['id_programa'],
            'id_modalidad' => $request['postulante']['id_modalidad'],
            'estado' => 0,
            'id_usuario' => auth()->id()
        ]);

        $this->pdfInscripcion($dni);

        $this->response['estado'] = true;
        $this->response['datos'] = $dni;
        return response()->json($this->response, 200);

    }


    public function Actualizar(Request $request)
    {
        $inscripcion = Inscripcion::find($request->id);
        $proceso = Proceso::find(auth()->user()->id_proceso);

        $cambioPrograma = $inscripcion->id_programa != $request->id_programa;
        $cambioModalidad = $inscripcion->id_modalidad != $request->id_modalidad;
        $solicitaAnulacion = $request->estado;

        if ($cambioPrograma) {
            $inscripcion->estado = 3;
            $inscripcion->observaciones = "Cambio de programa a {$request->id_programa}";
            $inscripcion->save();

            $id_programa = str_pad($request->id_programa, 2, '0', STR_PAD_LEFT);
            $ultimoCodigo = Inscripcion::where('codigo', 'like', $proceso->codigo_proceso . $id_programa . '%')
                ->max(\DB::raw('CAST(SUBSTRING(codigo, 7) AS UNSIGNED)')) + 1;
            $nuevoCorrelativo = str_pad($ultimoCodigo, 4, '0', STR_PAD_LEFT);
            $nuevoCodigo = $proceso->codigo_proceso . $id_programa . $nuevoCorrelativo;


            $inscripcion = Inscripcion::create([
                'codigo' => $nuevoCodigo,
                'id_postulante' => $request->id_postulante,
                'id_proceso' => auth()->user()->id_proceso,
                'id_programa' => $request->id_programa,
                'id_modalidad' => $request->id_modalidad,
                'estado' => 0,
                'id_usuario' => auth()->id(),
            ]);
        }

        if ($cambioModalidad) {
            $observacionActual = $inscripcion->observaciones ?? '';
            $inscripcion->id_modalidad = $request->id_modalidad;
            $inscripcion->observaciones = trim($observacionActual . " Cambio de modalidad a {$request->id_modalidad}");
            $inscripcion->save();
        }

        if (!$cambioPrograma && !$cambioModalidad && $request->estado != $inscripcion->estado) {
            $inscripcion->estado = $request->estado;
            $inscripcion->observaciones = "Estado actualizado manualmente a {$request->estado}.";
            $inscripcion->save();
        }

        // $this->pdfInscripcion($request->dni);

        $this->response = [
            'titulo' => 'REGISTRO MODIFICADO',
            'mensaje' => 'DATOS ACTUALIZADOS CORRECTAMENTE',
            'estado' => true,
        ];
    }



    public function pdfInscripcion($dni) {
        $proceso = Proceso::find(auth()->user()->id_proceso);
        $dia1 = $this->formatDate($proceso->fec_1);
        $dia2 = $this->formatDate($proceso->fec_2 ?? null);

        $carreras_previas = DB::select("SELECT codigo, cod_car, nombre, condicion FROM carreras_previas
            WHERE dni_postulante = ?", [$dni]);

        $datos = DB::select("SELECT
            postulante.nro_doc AS dni,
            inscripciones.codigo as codigo,
            postulante.nombres AS nombre,
            postulante.primer_apellido AS paterno,
            postulante.segundo_apellido AS materno,
            programa.nombre AS programa,
            inscripciones.id_programa as id_programa,
            modalidad.nombre AS modalidad,
            procesos.nombre AS proceso,
            LPAD(codigo_sunedu,3,'0') AS cod_sunedu,
            inscripciones.created_at as fecha,
            users.name, users.paterno as upaterno,
            users.dni as user_dni
        FROM inscripciones
        JOIN postulante ON inscripciones.id_postulante = postulante.id
        JOIN programa ON inscripciones.id_programa = programa.id
        JOIN modalidad ON inscripciones.id_modalidad = modalidad.id
        JOIN procesos ON inscripciones.id_proceso = procesos.id
        JOIN users on inscripciones.id_usuario = users.id
        WHERE postulante.nro_doc = ? AND inscripciones.estado = 0 AND inscripciones.id_proceso = ?", [$dni, auth()->user()->id_proceso]);

        $foto = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/fotos/' . $dni . '.jpg');
        $huellaIzquierda = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . '.jpg');
        $huellaDerecha = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . 'x.jpg');

        $data = $datos[0];

        // Generar PDF temporal
        $pdf = Pdf::loadView('inscripcion.inscripcion', compact('data', 'carreras_previas', 'foto', 'huellaIzquierda', 'huellaDerecha', 'dia1', 'dia2'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();

        $rutaTemp = storage_path('app/temp_' . $dni . '.pdf');
        file_put_contents($rutaTemp, $output);

        // Validar PDF
        if (!File::exists($rutaTemp) || filesize($rutaTemp) === 0) {
            abort(500, "El PDF temporal no se generó correctamente.");
        }

        $certificado = CertificadoFirma::where('id_usuario', Auth::id())->first();
        if (!$certificado) {
            abort(400, 'El usuario no tiene certificado de firma configurado');
        }

        $client = new Client();
        $response = $client->post('https://test-admision.unap.edu.pe/service_firma/firmar-dni/', [
            'multipart' => [
                ['name' => 'dni', 'contents' => auth()->user()->dni],
                ['name' => 'password_p12', 'contents' => $certificado->password_p12],
                ['name' => 'documento', 'contents' => fopen($rutaTemp, 'r'), 'filename' => $dni.'.pdf'],
                ['name' => 'url', 'contents' => 'https://inscripciones.admision.unap.edu.pe/verificacion/'.$data->codigo],
                ['name' => 'x', 'contents' => '435'],
                ['name' => 'y', 'contents' => '205'],
                ['name' => 'width', 'contents' => '120'],
                ['name' => 'height', 'contents' => '120'],
            ]
        ]);

        $pdfFirmado = $response->getBody()->getContents();
        $rutaCarpeta = public_path('/documentos/'.auth()->user()->id_proceso.'/inscripciones/constancias/');
        if (!File::exists($rutaCarpeta)) {
            File::makeDirectory($rutaCarpeta, 0755, true, true);
        }
        $rutaFinal = $rutaCarpeta . $dni . '.pdf';
        file_put_contents($rutaFinal, $pdfFirmado);

        File::delete($rutaTemp);

        return response()->file($rutaFinal);
    }



    // public function pdfInscripcion($dni) {
    //     $proceso = Proceso::find(auth()->user()->id_proceso);
    //     $dateString1 = $proceso->fec_1;
    //     $dateString2 = $proceso->fec_2 ?? null;
    //     $dia1 = $this->formatDate($dateString1);
    //     $dia2 = $this->formatDate($dateString2);

    //     $carreras_previas = DB::select("SELECT codigo, cod_car, nombre, condicion FROM carreras_previas
    //     WHERE dni_postulante = ?", [$dni]);

    //     $datos = DB::select("SELECT
    //         postulante.nro_doc AS dni,
    //         inscripciones.codigo as codigo,
    //         postulante.nombres AS nombre,
    //         postulante.primer_apellido AS paterno,
    //         postulante.segundo_apellido AS materno,
    //         programa.nombre AS programa,
    //         inscripciones.id_programa as id_programa,
    //         modalidad.nombre AS modalidad,
    //         procesos.nombre AS proceso,
    //         LPAD(codigo_sunedu,3,'0') AS cod_sunedu,
    //         inscripciones.created_at as fecha,
    //         users.name, users.paterno as upaterno
    //     FROM inscripciones
    //     JOIN postulante ON inscripciones.id_postulante = postulante.id
    //     JOIN programa ON inscripciones.id_programa = programa.id
    //     JOIN modalidad ON inscripciones.id_modalidad = modalidad.id
    //     JOIN procesos ON inscripciones.id_proceso = procesos.id
    //     JOIN users on inscripciones.id_usuario = users.id
    //     WHERE postulante.nro_doc = ? AND inscripciones.estado = 0 AND inscripciones.id_proceso = ?", [$dni, auth()->user()->id_proceso]);

    //     $foto = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/fotos/' . $dni . '.jpg');
    //     $huellaIzquierda = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . '.jpg');
    //     $huellaDerecha = public_path('/documentos/' . auth()->user()->id_proceso . '/inscripciones/huellas/' . $dni . 'x.jpg');

    //     $data = $datos[0];
    //     $pdf = Pdf::loadView('inscripcion.inscripcion', compact('data', 'carreras_previas', 'foto', 'huellaIzquierda', 'huellaDerecha', 'dia1', 'dia2'));
    //     $pdf->setPaper('A4', 'portrait');
    //     $output = $pdf->output();

    //     $rutaCarpeta = public_path('/documentos/'.auth()->user()->id_proceso.'/inscripciones/constancias/');
    //     if (!File::exists($rutaCarpeta)) {
    //         File::makeDirectory($rutaCarpeta, 0755, true, true);
    //     }
    //     file_put_contents($rutaCarpeta . $dni . '.pdf', $output);
    //     return $pdf->stream();
    // }

    // function getFileUrlWithHash($path) {
    //     if (File::exists($path)) {
    //         $hash = md5_file($path);
    //         return $path . '?v=' . $hash;
    //     }
    //     return $path;
    // }


    public function getInscripcionesAdmin(Request $request) {

        $query_where = [];

        if ($request->programa) array_push($query_where,[DB::raw('inscripciones.id_programa'), '=', $request->programa]);
        array_push($query_where,[DB::raw('inscripciones.id_proceso'), '=', auth()->user()->id_proceso]);

        $res = Inscripcion::select(
            'inscripciones.id as id', 'procesos.id as id_proceso', 'postulante.id as id_postulante', 'postulante.nro_doc AS dni', 'inscripciones.codigo as codigo', 'postulante.nombres AS nombres',
            'postulante.primer_apellido AS paterno', 'postulante.segundo_apellido AS materno', 'programa.nombre as programa', 'inscripciones.id_programa as id_programa',
            'modalidad.id as id_modalidad', 'modalidad.nombre as modalidad', 'procesos.nombre AS proceso', 'inscripciones.created_at as fecha', 'inscripciones.estado'
        )
        ->join('postulante','inscripciones.id_postulante', 'postulante.id')
        ->join('programa','inscripciones.id_programa', 'programa.id')
        ->join('modalidad','inscripciones.id_modalidad', 'modalidad.id')
        ->join('procesos','inscripciones.id_proceso', 'procesos.id')
        ->where($query_where)
        ->where(function ($query) use ($request) {
            return $query
              ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%');
        })
        ->paginate($request->paginashoja);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);

    }


    public function descargarListaExcel(Request $request){

        $data = Inscripcion::select([
            'inscripciones.created_at as FECHA DE INSCRIPCIÓN',
            'pro.id as COD PROGRAMA',
            'pro.nombre as PROGRAMA DE ESTUDIOS',
            'pro.area as AREA',
            'moda.id as COD MODALIDAD',
            'moda.nombre as MODALIDAD',
            'pos.nro_doc as N° DOCUMENTO',
            DB::raw('UPPER(pos.primer_apellido) as PATERNO'),
            DB::raw('UPPER(pos.segundo_apellido) as MATERNO'),
            DB::raw('UPPER(pos.nombres) as NOMBRES'),
            DB::raw('IF(pos.sexo = 1, "M", "F") as SEXO'),
            'pos.fec_nacimiento as FEC NACIMIENTO',
            DB::raw('TIMESTAMPDIFF(YEAR, pos.fec_nacimiento, CURDATE()) as EDAD'),
            'pos.ubigeo_residencia as UBIGEO RESIDENCIA',
            'dep.nombre as DEPARTAMENTO RESIDENCIA',
            'prov.nombre as PROVINCIA RESIDENCIA',
            'dist.nombre as DISTRITO RESIDENCIA',
            'pos.anio_egreso as EGRESO',
            'col.cod_modular as COD MODULAR',
            'col.ubigeo as UBIGEO COLEGIO',
            'depC.nombre as DEPARTAMENTO COLEGIO',
            'provC.nombre as PROVINCIA COLEGIO',
            'distC.nombre as DISTRITO COLEGIO'
        ])
        ->join('postulante as pos', 'inscripciones.id_postulante', '=', 'pos.id')
        ->join('programa as pro', 'pro.id', '=', 'inscripciones.id_programa')
        ->join('modalidad as moda', 'moda.id', '=', 'inscripciones.id_modalidad')
        ->join('colegios as col', 'col.id', '=', 'pos.id_colegio')
        ->join('ubigeo as ub', 'ub.ubigeo', '=', 'pos.ubigeo_residencia')
        ->join('departamento as dep', 'dep.id', '=', 'ub.id_departamento')
        ->join('provincia as prov', 'prov.id', '=', 'ub.id_provincia')
        ->join('distritos as dist', 'dist.id', '=', 'ub.id_distrito')
        ->join('ubigeo as ubc', 'ubc.ubigeo', '=', 'col.ubigeo')
        ->join('departamento as depC', 'depC.id', '=', 'ubc.id_departamento')
        ->join('provincia as provC', 'provC.id', '=', 'ubc.id_provincia')
        ->join('distritos as distC', 'distC.id', '=', 'ubc.id_distrito')
        ->where('inscripciones.id_proceso', auth()->user()->id_proceso)
        ->where('inscripciones.estado', 0)
        ->orderBy('pro.nombre')
        ->orderBy('pos.primer_apellido')
        ->get();

         return Excel::download(new InscripcionExport($data), 'reporte.xlsx');

    }


    private function formatDate($dateString)
    {
        if (!$dateString) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $dateString);
            return $date->locale('es')->translatedFormat('l j \d\e F');
        } catch (\Exception $e) {
            return null;
        }
    }


}
