<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Mail;
use App\Mail\MensajeCorreo;
use App\Mail\Reprogramacion;
use App\Mail\Comunicado;
use Illuminate\Http\Request;
use DB;

class EmailController extends Controller
{
    public function enviarCorreo($area)
    {

        $destinatarios = DB::select("SELECT * FROM enviar_correos WHERE enviado = 0 AND area = '$area'");
        
        $errores = [];

        foreach ($destinatarios as $destinatario) {
            try {
                Mail::to($destinatario->correo)->send(new MensajeCorreo($destinatario->nombres, $destinatario->correo, $destinatario->programa, $destinatario->puerta));
                DB::table('enviar_correos')
                    ->where('id', $destinatario->id)
                    ->update(['enviado' => 1]);
               
            } catch (\Exception $e) {
            
                $errores[] = [
                    'nombre' => $destinatario->nombres,
                    'correo' => $destinatario->correo,
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Hubo errores al enviar correos.',
                'no_enviados' => $errores
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'mensaje' => 'Correos enviados correctamente a todos los destinatarios.'
            ]);
        }
    }


    public function enviarConfirmacion(Request $request)
    {
        $destinatarios = [
        (object) ['nombre' =>'LUQUE CUSACANI JHON ARIEL', 'correo' => 'arielluqu3@gmail.com'],
        ];
        $errores = [];

        foreach ($destinatarios as $destinatario) {
            try {
                Mail::to($destinatario->correo)->send(new ConfirmacionRegistro($destinatario->nombre, $destinatario->correo, 'mensaje'));
            } catch (\Exception $e) {
                $errores[] = [
                    'nombre' => $destinatario->nombre,
                    'correo' => $destinatario->correo,
                    'error' => $e->getMessage(),
                ];
            }
        }

        if (!empty($errores)) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Hubo errores al enviar correos.',
                'no_enviados' => $errores
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'mensaje' => 'Correos enviados correctamente a todos los destinatarios.'
            ]);
        }
    }


    public function enviarReprogramacion(Request $request)
    {

        $destinatarios = DB::table('reservas as res')
        ->join('disponibilidad as dis', 'dis.id', '=', 'res.id_disponibilidad')
        ->join('inscripcion as ins', 'res.id_inscripcion', '=', 'ins.id')
        ->join('ecomm_comensalhistorico as est', 'est.id', '=', 'ins.id_estudiante')
        ->whereDate('dis.dia', '2025-05-06')
        ->select([
            'dis.dia',
            'dis.hora_inicio as hora',
            DB::raw('CONCAT(est.paterno," ", est.materno," ",est.nombres) AS nombre'),
            'est.correo_electronico as correo_electronico'
        ])
        ->get();


        $errores = [];


        foreach ($destinatarios as $destinatario) {
            try {
                Mail::to($destinatario->correo_electronico)->send(new Reprogramacion(
                    $destinatario->nombre,
                    $destinatario->correo_electronico,
                    $destinatario->dia,
                    $destinatario->hora,
                ));

            } catch (\Exception $e) {
                $errores[] = [
                    'nombre' => $destinatario->nombre,
                    'correo' => $destinatario->correo_electronico,
                    'error' => $e->getMessage(),
                ];
            }
        }

        /*
        try {
            Mail::to('arielluqu3@gmail.com')->send(new Reprogramacion(
                "JHON ARIEL LUQUE CUSACANI",
                "arielluqu3@gmail.com",
                "2025-01-01",
                "10:00:00",
            ));
        } catch (\Exception $e) {
            $errores[] = [
            'error' => $e->getMessage(),
            ];
            }
        */
        if (!empty($errores)) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Hubo errores al enviar correos.',
                'no_enviados' => $errores
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'mensaje' => 'Correos enviados correctamente a todos los destinatarios.'
            ]);
        }
    }


    public function enviarComunicado(Request $request)
    {
        /*
        $destinatarios = DB::table('reservas as res')
            ->join('disponibilidad as dis', 'dis.id', '=', 'res.id_disponibilidad')
            ->join('inscripcion as ins', 'res.id_inscripcion', '=', 'ins.id')
            ->join('ecomm_comensalhistorico as est', 'est.id', '=', 'ins.id_estudiante')
            ->whereDate('dis.dia', '2025-05-06')
            ->select([
                'dis.dia',
                'dis.hora_inicio as hora',
                DB::raw('CONCAT(est.paterno," ", est.materno," ",est.nombres) AS nombre'),
                'est.correo_electronico as correo_electronico'
            ])
            ->get();
        */
        $destinatarios = DB::select("
            SELECT
              concat(est.nombres, ' ', est.paterno, ' ', est.materno) AS 'nombre',
              est.correo_electronico AS correo
            FROM inscripcion ins
            LEFT JOIN atencion_entrevistas ate ON ate.id_inscripcion = ins.id
            JOIN ecomm_comensalhistorico est ON est.id = ins.id_estudiante
            WHERE ins.id_convocatoria = 10
              AND (ate.estado = 'pendiente' OR ate.id IS NULL);
        ");

        $errores = [];


        foreach ($destinatarios as $destinatario) {
            try {
                Mail::to($destinatario->correo)->send(new Comunicado(
                    $destinatario->nombre,
                    $destinatario->correo
                ));

            } catch (\Exception $e) {
                $errores[] = [
                    'nombre' => $destinatario->nombre,
                    'correo' => $destinatario->correo,
                    'error' => $e->getMessage()
                ];
            }
        }

        /*
        try {
            Mail::to('arielluqu3@gmail.com')->send(new Comunicado(
                "JHON ARIEL LUQUE CUSACANI",
                "arielluqu3@gmail.com"
            ));
        } catch (\Exception $e) {
            $errores[] = [
            'error' => $e->getMessage(),
            ];
            }
        */
        if (!empty($errores)) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Hubo errores al enviar correos.',
                'no_enviados' => $errores
            ]);
        } else {
            return response()->json([
                'status' => 'success',
                'mensaje' => 'Correos enviados correctamente a todos los destinatarios.'
            ]);
        }
    }

}
