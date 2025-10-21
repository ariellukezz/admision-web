<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ControlBiometrico;
use App\Models\Estudiante;
use App\Models\CarrerasPrevias;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\RegistroEstudiante;
use App\Models\Postulante;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class IngresoController extends Controller {

    public function getDatosIngreso($dni){
        $res = DB::select("SELECT postulante.id,
            postulante.nro_doc,
            postulante.nombres,
            postulante.primer_apellido,
            postulante.segundo_apellido,
            programa.nombre AS programa,
            procesos.nombre AS proceso,
            resultados.puntaje,
            resultados.puesto,
            resultados.puesto_general,
            resultados.fecha
            FROM resultados
            JOIN postulante ON postulante.nro_doc = resultados.dni_postulante
            JOIN inscripciones ON postulante.id = inscripciones.id_postulante
            JOIN programa ON programa.id = inscripciones.id_programa
            JOIN procesos ON procesos.id = inscripciones.id_proceso
            WHERE postulante.nro_doc = $dni");

        $this->response['estado'] = true;
        $this->response['datos'] = $res[0];
        return response()->json($this->response, 200);
    }

    public function getDatosIngresoGeneral($dni)
    {
        $idProceso = auth()->user()->id_proceso;
        $datos = DB::table('resultados as r')
            ->join('postulante as p', 'p.nro_doc', '=', 'r.dni_postulante')
            ->join('inscripciones as i', function($join) {
                $join->on('p.id', '=', 'i.id_postulante')
                    ->on('i.id_proceso', '=', 'r.id_proceso')
                    ->where('i.estado', 0);
            })
            ->join('programa as prg', 'prg.id', '=', 'i.id_programa')
            ->join('facultad as f', 'f.id', '=', 'prg.id_facultad')
            ->join('modalidad as m', 'm.id', '=', 'i.id_modalidad')
            ->join('procesos as prc', 'prc.id', '=', 'i.id_proceso')
            ->where('i.id_proceso', $idProceso)
            ->where('p.nro_doc', $dni)
            ->select([
                'p.id', 'p.nro_doc', 'p.nombres', 'p.primer_apellido', 'p.segundo_apellido',
                'p.correo_institucional',
                'p.tipo_doc', 'p.sexo', 'p.foto_url as foto', 'p.fec_nacimiento',
                'prg.nombre as programa', 'prg.programa_correo',
                'f.nombre_correo as facultad_correo',
                'prc.nombre as proceso',
                'm.nombre as modalidad',
                'r.puntaje', 'r.puesto', 'r.puesto_general', 'r.fecha'
            ])
            ->first();

        if (!$datos) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'No se encontraron datos para el DNI indicado.'
            ], 404);
        }

        $basePath = "/documentos/{$idProceso}";
        $timestamp = '?v=' . time();

        $getFileUrl = function ($relativePath, $default) use ($timestamp) {
        $fullPath = public_path($relativePath);
        return file_exists($fullPath) 
            ? url($relativePath) . $timestamp 
            : url($default) . $timestamp;
        };

        $fotoUrl        = $getFileUrl("{$basePath}/control_biometrico/fotos/{$dni}.jpg", "imagenes/sin_imagen.png");
        $huellaDerecha  = $getFileUrl("{$basePath}/control_biometrico/huellas/{$dni}.jpg", "imagenes/sin_imagen.png");
        $huellaIzquierda= $getFileUrl("{$basePath}/control_biometrico/huellas/{$dni}x.jpg", "imagenes/sin_imagen.png");
        $docDni         = url("{$basePath}/biometrico/dnis/{$dni}.pdf") . $timestamp;
        $docCertificado = url("{$basePath}/biometrico/certificados/{$dni}.pdf") . $timestamp;

        $url = "https://service6.unap.edu.pe/api/crear-correo";
        $secretKey = "unap@2025";

        $data = [
            "apellido_paterno" => $datos->primer_apellido,
            "apellido_materno" => $datos->segundo_apellido,
            "nombres" => $datos->nombres,
            "dni" => $datos->nro_doc,
            "celular" => '999999999',
            "correo_secundario" => 'admision@test.com',
            "facultad" => $datos->facultad_correo,
            "escuela" => $datos->programa_correo,
            "numero_ingresos" => false,
        ];

        $jsonData = json_encode($data);
        $signature = hash_hmac('sha256', $jsonData, $secretKey);

        try {
        $responseCorreo = Http::withHeaders([
            'X-Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->timeout(10)
            ->post($url, $data);

        $correos = $responseCorreo->successful()
            ? $responseCorreo->json('users') ?? []
            : [];

        } catch (\Throwable $e) {
            $correos = [];
        }

        $this->response = [
            'estado' => true,
            'foto' => $fotoUrl,
            'hDerecha' => $huellaDerecha,
            'hIzquierda' => $huellaIzquierda,
            'doc_dni' => $docDni,
            'doc_certificado' => $docCertificado,
            'datos' => $datos,
            'correos' => $correos
        ];

        return response()->json($this->response, 200);
    }


    // public function getDatosIngresoGeneral($dni){
    //     $res = DB::select("SELECT
    //         postulante.id as id,
    //         postulante.nro_doc,
    //         postulante.nombres,
    //         postulante.primer_apellido,
    //         postulante.segundo_apellido,
    //         postulante.tipo_doc,
    //         postulante.sexo, 
    //         postulante.foto_url as foto,
    //         postulante.fec_nacimiento,
    //         programa.nombre AS programa,
    //         programa.programa_correo AS programa_correo,
    //         facultad.nombre_correo AS facultad_correo,
    //         procesos.nombre AS proceso,
    //         modalidad.nombre AS modalidad,
    //         resultados.puntaje,
    //         resultados.puesto,
    //         resultados.puesto_general,
    //         resultados.fecha as fecha
    //     FROM resultados
    //     JOIN postulante ON postulante.nro_doc = resultados.dni_postulante
    //     JOIN inscripciones ON postulante.id = inscripciones.id_postulante
    //     JOIN programa ON programa.id = inscripciones.id_programa
    //     JOIN facultad ON facultad.id = programa.id_facultad
    //     JOIN modalidad ON modalidad.id = inscripciones.id_modalidad
    //     JOIN procesos ON procesos.id = inscripciones.id_proceso AND resultados.id_proceso = inscripciones.id_proceso
    //     WHERE inscripciones.id_proceso = ".auth()->user()->id_proceso."
    //     AND postulante.nro_doc = $dni ");

    //     $fotoUrl = url("/documentos/" . auth()->user()->id_proceso . "/control_biometrico/fotos/" . $dni . ".jpg") . '?v=' . time();
    //     $huellaDerecha = url("/documentos/" . auth()->user()->id_proceso . "/control_biometrico/huellas/" . $dni . ".jpg") . '?v=' . time();
    //     $huellaIzquierda = url("/documentos/" . auth()->user()->id_proceso . "/control_biometrico/huellas/" . $dni . "x.jpg") . '?v=' . time();

    //     $doc_dni = url("/documentos/" . auth()->user()->id_proceso . "/biometrico/dnis/" . $dni . ".pdf") . '?v=' . time();
    //     $doc_certificado = url("/documentos/" . auth()->user()->id_proceso . "/biometrico/certificados/" . $dni . ".pdf") . '?v=' . time();


    //     $url = "https://service6.unap.edu.pe/api/crear-correo";
    //     #$url = "http://10.1.20.30:6060/api/crear-correo";
    //     $secretKey = "unap@2025";
    //     $data = [
    //         "apellido_paterno" => $res[0]->primer_apellido,
    //         "apellido_materno" => $res[0]->segundo_apellido,
    //         "nombres" => $res[0]->nombres,
    //         "dni" => $res[0]->nro_doc,
    //         "celular" => '999999999',
    //         "correo_secundario" => 'solopruebas@test.com',
    //         "facultad" => $res[0]->facultad_correo,
    //         "escuela" => $res[0]->programa_correo,
    //         "numero_ingresos" => false,
    //     ];

    //     $jsonData = json_encode($data);
    //     $signature = hash_hmac('sha256', $jsonData, $secretKey);
    //     $responsecorreo = [];
    //     $responsecorreo = Http::withHeaders([
    //         'X-Signature' => $signature,
    //         'Content-Type' => 'application/json'
    //     ])->post($url, $data);


    //     $this->response['estado'] = true;
    //     $this->response['foto'] = $fotoUrl;
    //     $this->response['hDerecha'] = $huellaDerecha;
    //     $this->response['hIzquierda'] = $huellaIzquierda;
    //     $this->response['doc_dni'] = $doc_dni;
    //     $this->response['doc_certificado'] = $doc_certificado;
    //     $this->response['datos'] = $res[0];
    //     if (!empty($responsecorreo)) {
    //         $this->response['correos'] = is_array($responsecorreo) ? $responsecorreo : $responsecorreo->json('users');
    //     }
    //     //$this->response['correos'] = $responsecorreo->json('users');
    //     return response()->json($this->response, 200);

    // }


    public function biometrico(Request $request)
    {
        $database2 = 'mysql_secondary';

        $re = DB::table('resultados as r')
            ->select([
                'p.anio',
                'p.ciclo_oti',
                'prog.programa_oti',
                'post.primer_apellido as paterno',
                'post.segundo_apellido as materno',
                'post.nombres',
                'tdi.documento_oti as tipo_doc_oti',
                'post.nro_doc as dni',
                'u.name',
                'u.paterno as upaterno',
                'post.fec_nacimiento',
                'post.sexo',
                'post.ubigeo_residencia',
                'post.direccion',
                'post.estado_civil',
                'r.fecha',
                'post.email',
                'post.celular',
                'prog.cod_esp',
                'm.modalidad_oti',
                'r.puntaje',
                'r.puesto',
                'r.puesto_general',
                'post.id as id_postulante',
                'p.id as id_proceso',
                'p.nombre as proceso',
                'm.id as id_modalidad',
                'm.nombre as modalidad',
                'prog.nombre as programa',
                'prog.programa_correo',
                'prog.id as id_programa',
                'f.nombre_correo as facultad_correo',
            ])
            ->join('postulante as post', 'r.dni_postulante', '=', 'post.nro_doc')
            ->join('inscripciones as ins', 'ins.id_postulante', '=', 'post.id')
            ->join('modalidad as m', 'ins.id_modalidad', '=', 'm.id')
            ->join('procesos as p', 'r.id_proceso', '=', 'p.id')
            ->leftJoin('users as u', 'u.id', '=', 'ins.id_usuario')
            ->join('programa as prog', 'ins.id_programa', '=', 'prog.id')
            ->join('facultad as f', 'prog.id_facultad', '=', 'f.id')
            ->join('tipo_documento_identidad as tdi', 'post.tipo_doc', '=', 'tdi.id')
            ->where([
                ['r.apto', '=', 'SI'],
                ['ins.estado', '=', 0],
                ['r.dni_postulante', '=', $request->dni],
                ['r.id_proceso', '=', auth()->user()->id_proceso],
                ['ins.id_proceso', '=', auth()->user()->id_proceso],
            ])
            ->first();

        if (!$re) {
            return response()->json(['error' => 'Postulante no encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $control = ControlBiometrico::where('id_proceso', $re->id_proceso)
                ->where('id_postulante', $re->id_postulante)
                ->first();

            if (!$control) {

                $prefijo = $re->id_programa == 38 ? '26' : '25';

                $registrado = collect(
                    DB::connection($database2)->select(
                        "SELECT num_mat FROM unapnet.estudiante WHERE num_doc = ? AND fch_ing = ?",
                        [$re->dni, $re->fecha]
                    )
                )->first();

                $nuevoCodigo = $registrado->num_mat ?? DB::connection($database2)
                    ->table('unapnet.estudiante as e')
                    ->whereRaw("LEFT(e.num_mat, 2) = ?", [$prefijo])
                    ->max(DB::raw("CAST(SUBSTRING(e.num_mat, 3) AS UNSIGNED)")) + 1;

                $nuevoCodigo = $registrado->num_mat ?? $prefijo . str_pad($nuevoCodigo, 4, '0', STR_PAD_LEFT);

                $control = ControlBiometrico::create([
                    'id_proceso' => $re->id_proceso,
                    'id_postulante' => $re->id_postulante,
                    'codigo_ingreso' => $nuevoCodigo,
                    'estado' => 1,
                    'segunda_carrera' => $request->n_carrera == 1 ? 1 : 0,
                    'id_usuario' => auth()->id(),
                    'tiene_correo' => 0,
                    'correo_institucional' => null
                ]);

                if (!$control) {
                    throw new \Exception('Error al crear el registro en ControlBiometrico.');
                }

                if (!$registrado) {
                    Estudiante::on($database2)->create([
                        'num_mat' => $nuevoCodigo,
                        'cod_car' => $re->programa_oti,
                        'paterno' => $re->paterno,
                        'materno' => $re->materno,
                        'nombres' => $re->nombres,
                        'tip_doc' => $re->tipo_doc_oti,
                        'num_doc' => $re->dni,
                        'num_car' => $request->n_carrera == 1 ? 2 : 1,
                        'fch_nac' => $re->fec_nacimiento,
                        'sexo' => $re->sexo,
                        'ubigeo' => $re->ubigeo_residencia,
                        'mod_ing' => $re->modalidad_oti,
                        'est_civ' => [1 => 2, 2 => 1, 3 => 3, 4 => 6][$re->estado_civil] ?? 1,
                        'fch_ing' => $re->fecha,
                        'direc' => $re->direccion,
                        'email' => $re->email,
                        'emailins' => $control->correo_institucional,
                        'con_est' => 5,
                        'celular' => $re->celular,
                        'cod_esp' => $re->cod_esp,
                        'puntaje' => $re->puntaje,
                        'puesto_escuela' => $re->puesto,
                        'puesto_general' => $re->puesto_general,
                        'ano_ing' => $re->anio,
                        'per_ing' => $re->ciclo_oti
                    ]);
                }
            } else {
                $control->update(['estado' => 2]);
            }

            // if ($request->crear_correo == 1) {
            //     $url = "https://service6.unap.edu.pe/api/crear-correo";
            //     $secretKey = "unap@2025";
            //     $data = [
            //         "apellido_paterno" => $re->paterno,
            //         "apellido_materno" => $re->materno,
            //         "nombres" => $re->nombres,
            //         "dni" => $re->dni,
            //         "celular" => $re->celular,
            //         "correo_secundario" => $re->email,
            //         "facultad" => $re->facultad_correo,
            //         "escuela" => $re->programa_correo,
            //         "numero_ingresos" => $request->crear_correo,
            //     ];
            //     $signature = hash_hmac('sha256', json_encode($data), $secretKey);

            //     $response = Http::withHeaders([
            //         'X-Signature' => $signature,
            //         'Content-Type' => 'application/json'
            //     ])->post($url, $data);

            //     if (!$response->successful()) {
            //         throw new \Exception('Error al crear el correo: ' . $response->body());
            //     }

            //     $control->update([
            //         'tiene_correo' => 1,
            //         'correo_institucional' => $response->json('correo')
            //     ]);
            // }

            DB::commit();

            $this->pdfbiometrico2($re->dni);

            return response()->json(['estado' => true, 'datos' => $request->dni], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error en la transacción: ' . $e->getMessage()
            ], 500);
        }
    }


    


    // public function biometrico(Request $request){

    //     $re = DB::table('resultados as r')
    //         ->select([
    //             'p.anio',
    //             'p.ciclo_oti',
    //             'prog.programa_oti',
    //             'post.primer_apellido as paterno',
    //             'post.segundo_apellido as materno',
    //             'post.nombres',
    //             'tdi.documento_oti as tipo_doc_oti',
    //             'post.nro_doc as dni',
    //             'u.name',
    //             'u.paterno as upaterno',
    //             'post.fec_nacimiento',
    //             'post.sexo',
    //             'post.ubigeo_residencia',
    //             'post.direccion',
    //             'post.estado_civil',
    //             'r.fecha',
    //             'post.email',
    //             'post.celular',
    //             'prog.cod_esp',
    //             'm.modalidad_oti',
    //             'r.puntaje',
    //             'r.puesto',
    //             'r.puesto_general',
    //             'post.id as id_postulante',
    //             'p.id as id_proceso',
    //             'p.nombre as proceso',
    //             'm.id as id_modalidad',
    //             'm.nombre as modalidad',
    //             'prog.nombre as programa',
    //             'prog.programa_correo',
    //             'prog.id as id_programa',
    //             'f.nombre_correo as facultad_correo',
    //         ])
    //         ->join('postulante as post', 'r.dni_postulante', '=', 'post.nro_doc')
    //         ->join('inscripciones as ins', 'ins.id_postulante', '=', 'post.id')
    //         ->join('modalidad as m', 'ins.id_modalidad', '=', 'm.id')
    //         ->join('procesos as p', 'r.id_proceso', '=', 'p.id')
    //         ->leftJoin('users as u', 'u.id', '=', 'ins.id_usuario')
    //         ->join('programa as prog', 'ins.id_programa', '=', 'prog.id')
    //         ->join('facultad as f', 'prog.id_facultad', '=', 'f.id')
    //         ->join('tipo_documento_identidad as tdi', 'post.tipo_doc', '=', 'tdi.id')
    //         ->where([
    //             ['r.apto', '=', 'SI'],
    //             ['ins.estado', '=', 0],
    //             ['r.dni_postulante', '=', $request->dni],
    //             ['r.id_proceso', '=', auth()->user()->id_proceso],
    //             ['ins.id_proceso', '=', auth()->user()->id_proceso],
    //         ])->get();

    //     // $registrado = collect(
    //     // DB::connection($database2)
    //     //     ->select(" SELECT num_mat FROM unapnet.estudiante WHERE num_doc = ?  AND fch_ing = ?", [$re->dni, $re->fecha]))
    //     //     ->first();


    //     try {
    //         DB::beginTransaction();        
    //         $database2 = 'mysql_secondary';
        
    //         $control = ControlBiometrico::where('id_proceso', auth()->user()->id_proceso)
    //             ->where('id_postulante', $re[0]->id_postulante)
    //             ->first();
        
    //         if (!$control) {

    //             $prefijo = $re[0]->id_programa == '38' ? '26' : '25';
    //             $rs = DB::connection($database2)->select("SELECT CONCAT('$prefijo', LPAD(IFNULL(MAX(CAST(SUBSTRING(e.num_mat, 3) AS UNSIGNED)) + 1, 1), 4, '0')) AS siguiente 
    //                 FROM unapnet.estudiante e 
    //                 WHERE LEFT(e.num_mat, 2) = '$prefijo';");
    //             $nuevoCodigo = $rs[0]->siguiente;

    //             // $registrado = collect(DB::connection($database2)
    //             //     ->select(" SELECT num_mat FROM unapnet.estudiante WHERE num_doc = ?  AND fch_ing = ?", [$re->dni, $re->fecha]))
    //             //     ->first();
    //             // if($registrado){
    //             //     $nuevoCodigo = $registrado;
    //             // }

    //             $control = ControlBiometrico::create([
    //                 'id_proceso' => auth()->user()->id_proceso,
    //                 'id_postulante' => $re[0]->id_postulante,
    //                 'codigo_ingreso' => $nuevoCodigo,
    //                 'estado' => 1,
    //                 'segunda_carrera' => $request->n_carrera == 1 ? 1 : 0,
    //                 'id_usuario' => auth()->id(),
    //                 'tiene_correo' => 0,
    //                 'correo_institucional' => null
    //             ]);
        
    //             if (!$control) {
    //                 throw new \Exception('Error al crear el registro en ControlBiometrico.');
    //             }


    //             Estudiante::on($database2)->create([
    //                 'num_mat' => $nuevoCodigo,
    //                 'cod_car' => $re[0]->programa_oti,
    //                 'paterno' => $re[0]->paterno,
    //                 'materno' => $re[0]->materno,
    //                 'nombres' => $re[0]->nombres,
    //                 'tip_doc' => $re[0]->tipo_doc_oti,
    //                 'num_doc' => $re[0]->dni,
    //                 'num_car' => $request->n_carrera == 1 ? 2 : 1,
    //                 'fch_nac' => $re[0]->fec_nacimiento,
    //                 'sexo' => $re[0]->sexo,
    //                 'ubigeo' => $re[0]->ubigeo_residencia,
    //                 'mod_ing' => $re[0]->modalidad_oti,
    //                 'est_civ' => [1 => 2, 2 => 1, 3 => 3, 4 => 6][$re[0]->estado_civil] ?? 1,
    //                 'fch_ing' => $re[0]->fecha,
    //                 'direc' => $re[0]->direccion,
    //                 'email' => $re[0]->email,
    //                 'emailins' => $control->correo_institucional,
    //                 'con_est' => 5,
    //                 'celular' => $re[0]->celular,
    //                 'cod_esp' => $re[0]->cod_esp,
    //                 'puntaje' => $re[0]->puntaje,
    //                 'puesto_escuela' => $re[0]->puesto,
    //                 'puesto_general' => $re[0]->puesto_general,
    //                 'ano_ing' => $re[0]->anio,
    //                 'per_ing' => $re[0]->ciclo_oti
    //             ]);
                    
        

    //         } else {
    //             $control->update(['estado' => 2]);
    //         }
 
    //         if ($request->crear_correo == 1) {
    //             $url = "http://10.1.20.30:6060/api/crear-correo";
    //             $secretKey = "unap@2025";
    //             $data = [
    //                 "apellido_paterno" => $re[0]->paterno,
    //                 "apellido_materno" => $re[0]->materno,
    //                 "nombres" => $re[0]->nombres,
    //                 "dni" => $re[0]->dni,
    //                 "celular" => $re[0]->celular,
    //                 "correo_secundario" => $re[0]->email,
    //                 "facultad" => $re[0]->facultad_correo,
    //                 "escuela" => $re[0]->programa_correo,
    //                 "numero_ingresos" => $request->crear_correo,
    //             ];
    //             $jsonData = json_encode($data);
    //             $signature = hash_hmac('sha256', $jsonData, $secretKey);
    //             $response = Http::withHeaders([
    //                 'X-Signature' => $signature,
    //                 'Content-Type' => 'application/json'
    //             ])->post($url, $data);
        
    //             if (!$response->successful()) {
    //                 throw new \Exception('Error al crear el correo: ' . $response->body());
    //             }
        
    //             $correoGenerado = $response->json('correo');
        
    //             $control->update([
    //                 'tiene_correo' => 1,
    //                 'correo_institucional' => $correoGenerado
    //             ]);
    //         }
        
    //         DB::commit();
    //         $this->pdfbiometrico2($re[0]->dni);
        
    //         return response()->json(['estado' => true, 'datos' => $request->dni], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         //\Log::error('Error en la transacción: ' . $e->getMessage());
    //         return response()->json(['error' => 'Ocurrió un error en la transacción: ' . $e->getMessage()], 500);
    //     }

    //     $this->response['estado'] = true;
    //     $this->response['datos'] = $request->dni;
    //     return response()->json($this->response, 200);

    // }

    public function registrar_biometrico($dni)
    {
        try {

            $postulante = Postulante::select([
                'procesos.anio', 'procesos.ciclo_oti',
                'programa.programa_oti',
                'postulante.primer_apellido AS paterno',
                'postulante.segundo_apellido AS materno', 'postulante.nombres',
                'tipo_documento_identidad.documento_oti AS tipo_doc_oti',
                'postulante.nro_doc AS dni',
                'users.name', 'users.paterno as upaterno',
                'postulante.fec_nacimiento',
                'postulante.sexo',
                'postulante.ubigeo_residencia',
                'postulante.direccion',
                'postulante.estado_civil',
                'resultados.fecha',
                'postulante.email',
                'postulante.celular',
                'programa.cod_esp',
                'modalidad.modalidad_oti',
                'resultados.puntaje',
                'resultados.puesto',
                'resultados.puesto_general',
                'postulante.id AS id_postulante',
                'procesos.id AS id_proceso', 'procesos.nombre AS proceso',
                'modalidad.id AS id_modalidad', 'modalidad.nombre AS modalidad',
                'programa.nombre AS programa', 'programa.id',
                'control_biometrico.codigo_ingreso as codigo'
            ])
            ->join('resultados', 'resultados.dni_postulante', '=', 'postulante.nro_doc')
            ->join('inscripciones', 'inscripciones.id_postulante', '=', 'postulante.id')
            ->join('modalidad', 'inscripciones.id_modalidad', '=', 'modalidad.id')
            ->join('procesos', 'resultados.id_proceso', '=', 'procesos.id')
            ->leftJoin('users', 'users.id', '=', 'inscripciones.id_usuario')
            ->join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->join('tipo_documento_identidad', 'postulante.tipo_doc', '=', 'tipo_documento_identidad.id')
            ->join('control_biometrico', function($join) {
                $join->on('control_biometrico.id_postulante', '=', 'postulante.id')
                     ->on('control_biometrico.id_proceso', '=', DB::raw(auth()->user()->id_proceso));
            })
            ->where([
                ['resultados.apto', '=', 'SI'],
                ['inscripciones.estado', '=', 0],
                ['resultados.dni_postulante', '=', $dni],
                ['resultados.id_proceso', '=', auth()->user()->id_proceso],
                ['inscripciones.id_proceso', '=', auth()->user()->id_proceso]
            ])
            ->first();
    
            // Si no se encuentra el postulante, retornar error
            if (!$postulante) {
                return response()->json(['error' => 'No se encontró el postulante o no cumple los requisitos.'], 404);
            }
    
            // DB::transaction(function () use ($postulante) {
            //     $estadoCivilMap = [
            //         1 => 2,
            //         2 => 1,
            //         3 => 3,
            //         4 => 6
            //     ];
    
            //     $estudiante = Estudiante::on('mysql_secondary')->create([
            //         'num_mat' => $postulante->codigo,
            //         'cod_car' => $postulante->programa_oti,
            //         'paterno' => $postulante->paterno,
            //         'materno' => $postulante->materno,
            //         'nombres' => $postulante->nombres,
            //         'tip_doc' => $postulante->tipo_doc_oti,
            //         'num_doc' => $postulante->dni,
            //         'num_car' => 1, // Ingreso
            //         'fch_nac' => $postulante->fec_nacimiento,
            //         'sexo' => $postulante->sexo,
            //         'ubigeo' => $postulante->ubigeo_residencia,
            //         'mod_ing' => $postulante->modalidad_oti,
            //         'est_civ' => $estadoCivilMap[$postulante->estado_civil] ?? $postulante->estado_civil,
            //         'fch_ing' => $postulante->fecha,
            //         'direc' => $postulante->direccion,
            //         'email' => $postulante->email,
            //         'con_est' => 5,
            //         'celular' => $postulante->celular,
            //         'cod_esp' => $postulante->cod_esp,
            //         'puntaje' => $postulante->puntaje,
            //         'puesto_escuela' => $postulante->puesto,
            //         'puesto_general' => $postulante->puesto_general,
            //         'ano_ing' => $postulante->anio,
            //         'per_ing' => $postulante->ciclo_oti
            //     ]);
            // });
    
            return response()->json(['mensaje' => 'Registro biométrico realizado con éxito'], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error en la transacción: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error en la transacción: ' . $e->getMessage()], 500);
        }
    }


    public function pdf($datos){
        setlocale(LC_TIME, 'es_ES.utf8');
        $date = Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
        $dateI = Carbon::createFromFormat('Y-m-d', $datos->fecha)->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');
        #$dateI = "26 de Junio del 2034";
        $data = $datos;
        $pdf = Pdf::loadView('ingreso.constancia', compact('data','date','dateI'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();
        $rutaCarpeta = public_path('/documentos/cepre2023-II/'.$data->dni);
        if (!File::exists($rutaCarpeta)) { File::makeDirectory($rutaCarpeta, 0755, true, true); }
        file_put_contents(public_path('/documentos/cepre2023-II/'.$data->dni.'/').'constancia-ingreso-1.pdf', $output);
        return $pdf->stream();
    }

    public function pdfbiometrico($datos){

        $data = $datos->dni;
        $pdf = Pdf::loadView('ingreso.datosbiometricos', compact('data'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();

        $rutaCarpeta = public_path('/documentos/'.auth()->user()->id_proceso.'/control_biometrico/constancias/');
        if (!File::exists($rutaCarpeta)) {
            File::makeDirectory($rutaCarpeta, 0755, true, true);
        }
        file_put_contents($rutaCarpeta . $dni . '.pdf', $output);   
        return $pdf->stream();
    }

    public function pdfbiometrico2($dni){

        $datos = DB::select(
            "SELECT procesos.nombre as proceso, postulante.primer_apellido AS paterno,
            postulante.segundo_apellido AS materno, postulante.nombres, tipo_documento_identidad.nombre,
            postulante.nro_doc AS dni, postulante.fec_nacimiento AS fec_nacimiento,
            users.name, users.paterno as upaterno, modalidad.nombre as modalidad,
            resultados.fecha, resultados.puntaje, resultados.puesto,
            resultados.puesto_general, control_biometrico.codigo_ingreso AS cod_ingreso,
            control_biometrico.correo_institucional AS correo_institucional,
            control_biometrico.tiene_correo AS tiene_correo,
            control_biometrico.segunda_carrera AS segunda_carrera,
            programa.nombre AS programa
            FROM resultados
            JOIN postulante ON resultados.dni_postulante =  postulante.nro_doc
            JOIN inscripciones ON inscripciones.id_postulante = postulante.id
            JOIN modalidad ON inscripciones.id_modalidad = modalidad.id
            JOIN procesos ON resultados.id_proceso = procesos.id
            join users on users.id = inscripciones.id_usuario
            JOIN programa ON programa.id = inscripciones.id_programa
            JOIN control_biometrico ON control_biometrico.id_postulante = postulante.id
            LEFT JOIN tipo_documento_identidad ON postulante.tipo_doc = tipo_documento_identidad.id
            WHERE resultados.apto = 'SI' AND inscripciones.estado = 0 AND control_biometrico.id_proceso = "
            . auth()->user()->id_proceso ." AND resultados.dni_postulante = " .$dni. " AND resultados.id_proceso =".
            auth()->user()->id_proceso ." AND inscripciones.id_proceso = ". auth()->user()->id_proceso);

        $data = $datos[0];
        $hinsI = public_path('documentos/'.auth()->user()->id_proceso.'/inscripciones/huellas/').$dni.'x.jpg';
        $hinsD = public_path('documentos/'.auth()->user()->id_proceso.'/inscripciones/huellas/').$dni.'.jpg';
        $hexaI = public_path('documentos/'.auth()->user()->id_proceso.'/examen/huellas/').$dni.'.jpg';
        $hexaD = public_path('documentos/'.auth()->user()->id_proceso.'/examen/huellas/').$dni.'x.jpg';
        $hbioI = public_path('documentos/'.auth()->user()->id_proceso.'/control_biometrico/huellas/').$dni.'.jpg';
        $hbioD = public_path('documentos/'.auth()->user()->id_proceso.'/control_biometrico/huellas/').$dni.'x.jpg';
        $fins = public_path('documentos/'.auth()->user()->id_proceso.'/inscripciones/fotos/').$dni.'.jpg';
        $fbio = public_path('documentos/'.auth()->user()->id_proceso.'/control_biometrico/fotos/').$dni.'.jpg';

        setlocale(LC_TIME, 'es_ES.utf8');
        $fecha = $data->fecha;
        $date = \Carbon\Carbon::createFromFormat('Y-m-d', $fecha)->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');

        $fimp =  Carbon::now()->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');

        $fec_nac = $datos[0]->fec_nacimiento;
        $fnac = \Carbon\Carbon::createFromFormat('Y-m-d', $fec_nac)->locale('es')->isoFormat('DD [de] MMMM [del] YYYY');

        $pdf = Pdf::loadView('ingreso.datosbiometricos', compact('data','hinsI','hinsD','hexaI','hexaD','hbioI','hbioD','fins','fbio','date', 'fimp','fnac'));
        $pdf->setPaper('A4', 'portrait');
        $output = $pdf->output();

        $userIdProceso = auth()->user()->id_proceso;
        $documentoDir = public_path('/documentos/' . $userIdProceso . '/control_biometrico/constancias/');
        $filePath = $documentoDir . $dni . '.pdf';
    
        if (!file_exists($documentoDir)) {
            mkdir($documentoDir, 0755, true);
        }
    
        file_put_contents($filePath, $output);
        return $pdf->stream();

    }
    
    public function getCodigo($dni){
        $res = DB::table('temporal')
        ->where('dni', $dni)
        ->first();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getEstudianteOTI(){
        
        foreach ($ingresantes as $dni) {

            $response = Http::post('https://service2.unap.edu.pe/TieneCarrerasPrevias/', [
                'doc_' => $dni,
                'nom_' => 'sdfasdf',
                'app_' => 'ssdfasd',
                'apm_' => 'sdfs'
            ], [
                'headers' => ['Content-Type' => 'application/json']
            ]);
    
            $data = $response->json();

            foreach ($data as $estudiante) {
                RegistroEstudiante::create([
                    'dni' => $dni,
                    'nombre' => $estudiante['name'],
                    'codigo' => $estudiante['code'],
                    'ciclo' => $estudiante['cycle'],
                    'id_programa' => $estudiante['careerId'],
                    'programa' => $estudiante['career'],
                    'ultimo_ciclo' => $estudiante['lastCycle'],
                    'condicion' => $estudiante['cond1tion'],
                ]);
            }
        }

    }

    public function carrerasPrevias($dni){
        $res = CarrerasPrevias::select('*')->where('dni_postulante',$dni)->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }


    public function crearCorreo(Request $request)
    {
        $url = "https://service6.unap.edu.pe/api/crear-correo";
        $secretKey = "unap@2025";
        $data = [
            "apellido_paterno" => $request->apellido_paterno,
            "apellido_materno" => $request->apellido_materno,
            "nombres" => $request->nombres,
            "dni" => $request->dni,
            "celular" => $request->celular,
            "correo_secundario" => $request->correo_secundario,
            "facultad" => $request->facultad,
            "escuela" => $request->escuela,
            "numero_ingresos" => $request->numero_ingresos
        ];
        $jsonData = json_encode($data);
        $signature = hash_hmac('sha256', $jsonData, $secretKey);
        $response = Http::withHeaders([
            'X-Signature' => $signature,
            'Content-Type' => 'application/json'
        ])->post($url, $data);
        return response()->json($response->json(), $response->status());

    }


    public function actualizarCorreos($actualizar)
    {
        $batchSize = 100;
        $url = "https://service6.unap.edu.pe/api/crear-correo";
        $secretKey = "unap@2025";

        $totalProcesados = [];
        $totalErrores = [];

        $res = DB::table('control_biometrico as cb')
            ->join('postulante as pos', 'cb.id_postulante', '=', 'pos.id')
            ->join('inscripciones as ins', function ($join) {
                $join->on('pos.id', '=', 'ins.id_postulante')
                    ->on('ins.id_proceso', '=', 'cb.id_proceso')
                    ->where('ins.estado', 0);
            })
            ->join('programa as pro', 'ins.id_programa', '=', 'pro.id')
            ->join('facultad as fac', 'fac.id', '=', 'pro.id_facultad')
            ->where('cb.id_proceso', auth()->user()->id_proceso)
            ->whereNull('cb.correo_institucional')
            ->select(
                'cb.id as id_biometrico',
                'pos.primer_apellido',
                'pos.segundo_apellido',
                'pos.nombres',
                'pos.nro_doc',
                'pos.celular',
                'pos.email',
                'fac.nombre_correo',
                'pro.programa_correo',
                DB::raw($actualizar . ' as ingresos')
            )->get();

        $chunks = $res->chunk($batchSize);

        foreach ($chunks as $index => $chunk) {
            $procesados = [];
            $errores = [];

            foreach ($chunk as $r) {
                try {
                    $data = [
                        'apellido_paterno'  => $r->primer_apellido,
                        'apellido_materno'  => $r->segundo_apellido,
                        'nombres'           => $r->nombres,
                        'dni'               => $r->nro_doc,
                        'celular'           => $r->celular,
                        'correo_secundario' => $r->email,
                        'facultad'          => $r->nombre_correo,
                        'escuela'           => $r->programa_correo,
                        'numero_ingresos'   => $r->ingresos
                    ];

                    $jsonData  = json_encode($data);
                    $signature = hash_hmac('sha256', $jsonData, $secretKey);

                    $response = Http::withHeaders([
                        'X-Signature'   => $signature,
                        'Content-Type'  => 'application/json'
                    ])->post($url, $data);

                    if ($response->successful() && isset($response['users'][0]['email'])) {
                        $cb = ControlBiometrico::find($r->id_biometrico);
                        $cb->correo_institucional = $response['users'][0]['email'];
                        $cb->save();

                        $procesados[] = [
                            'id_biometrico' => $r->id_biometrico,
                            'correo' => $response['users'][0]['email']
                        ];
                    } else {
                        $errores[] = [
                            'id_biometrico' => $r->id_biometrico,
                            'error' => $response->json()
                        ];
                    }
                } catch (\Exception $e) {
                    $errores[] = [
                        'id_biometrico' => $r->id_biometrico,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $totalProcesados = array_merge($totalProcesados, $procesados);
            $totalErrores = array_merge($totalErrores, $errores);

            if ($index < $chunks->count() - 1) {
                sleep(60);
            }
        }

        return response()->json([
            'total_procesados' => count($totalProcesados),
            'total_errores' => count($totalErrores),
            'procesados' => $totalProcesados,
            'errores' => $totalErrores
        ]);
    }

  
    // public function crearCorreo(Request $request)
    // {
    //     $url = "https://service6.unap.edu.pe/api/crear-correo";
    //     $secretKey = "unap@2025";

    //     $data = [
    //         "apellido_paterno"   => $request->paterno,
    //         "apellido_materno"   => $request->materno,
    //         "nombres"            => $request->nombres,
    //         "dni"                => $request->dni,
    //         "celular"            => $request->celular,
    //         "correo_secundario"  => $request->email,
    //         "facultad"           => $request->facultad_correo,
    //         "escuela"            => $request->programa_correo,
    //         "numero_ingresos"    => $request->numero_ingresos,
    //     ];

    //     $signature = hash_hmac('sha256', json_encode($data), $secretKey);

    //     try {
    //         $response = Http::withHeaders([
    //             'X-Signature' => $signature,
    //             'Content-Type' => 'application/json'
    //         ])->post($url, $data);

    //         if ($response->failed()) {
    //             throw new \Exception($response->body());
    //         }

    //         return $response->json();

    //     } catch (\Throwable $e) {
    //         Log::error("Error al crear correo: " . $e->getMessage());
    //         throw $e;
    //     }
    // }


}