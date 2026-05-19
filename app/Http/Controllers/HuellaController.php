<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use App\Models\AvancePostulante;
use Illuminate\Http\Request;
use App\Models\Huella;
use App\Models\Postulante;
use Illuminate\Support\Facades\Redis;

class HuellaController extends Controller
{    

    public function guardar(Request $request)
    {
        $request->validate([
            'dni_postulante' => 'required',
            'dedo'           => 'required|string',
            'template'       => 'required',
            'imagen'         => 'nullable',
            'calidad'        => 'nullable|integer'
        ]);

        try {

            $postulante = Postulante::where(
                'nro_doc',
                $request->dni_postulante
            )->firstOrFail();

            $rutaImagen = null;

            if ($request->imagen) {

                $base64 = preg_replace(
                    '#^data:image/\w+;base64,#i',
                    '',
                    $request->imagen
                );

                $carpeta = public_path('documentos/huellas');

                if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }

                $nombreArchivo =$postulante->id . '_' .$request->dedo . '_' .uniqid() . '.png';

                file_put_contents(
                    $carpeta . '/' . $nombreArchivo,
                    base64_decode($base64)
                );

                $rutaImagen = 'documentos/huellas/' . $nombreArchivo;
            }

            $templateBinario = ctype_xdigit($request->template)
                ? hex2bin($request->template)
                : base64_decode($request->template);

            if (!$templateBinario) {
                throw new \Exception('Template inválido');
            }

            $hash = hash('sha256', $templateBinario);

            $huella = Huella::create([
                'id_postulante' => $postulante->id,
                'dedo'          => $request->dedo,
                'template'      => $templateBinario,
                'imagen'        => $rutaImagen,
                'calidad'       => $request->calidad,
                'hash_template' => $hash,
                'activo'        => 1
            ]);

            Redis::set(
                'huella:' .
                $postulante->id . ':' .
                $request->dedo . ':' .
                $huella->id,
                base64_encode($templateBinario)
            );

            return response()->json([
                'success' => true,
                'message' => 'Huella guardada',
                'data' => [
                    'id'            => $huella->id,
                    'dedo'          => $huella->dedo,
                    'imagen'        => $huella->imagen,
                    'calidad'       => $huella->calidad,
                    'hash_template' => $huella->hash_template
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function upload(Request $request) {
        try {
            $dni = $request->input('dni');
            $etapa = $request->input('etapa');
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');

                $rutaCarpeta = "";
                if($etapa == 'inscripcion'){ 
                     $rutaCarpeta = public_path('documentos/9/inscripciones/huellas/'); 
                } else {  $rutaCarpeta = public_path('documentos/9/control_biometrico/huellas/'); 
                }

                if (!file_exists($rutaCarpeta)) {
                    if (!mkdir($rutaCarpeta, 0777, true)) {
                        return response()->json(['error' => 'No se pudo crear la carpeta para guardar la imagen'], 500);
                    }
                }

                $imageName = $dni;
                $imagen->move($rutaCarpeta, $imageName);
    
                return response()->json(['image_path' => $dni]);
            } else {
                return response()->json(['error' => 'No se proporcionó ningún archivo de imagen'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar la imagen: ' . $e->getMessage()], 500);
        }
    }


    public function uploadcepre(Request $request) {
        try {
            $dni = $request->input('dni');
            $etapa = $request->input('etapa');
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');

                $rutaCarpeta = "";
                if($etapa == 'inscripcion'){ 
                     $rutaCarpeta = public_path('documentos/10/inscripciones/huellas/'); 
                } else {  $rutaCarpeta = public_path('documentos/10/control_biometrico/huellas/'); 
                }

                if (!file_exists($rutaCarpeta)) {
                    if (!mkdir($rutaCarpeta, 0777, true)) {
                        return response()->json(['error' => 'No se pudo crear la carpeta para guardar la imagen'], 500);
                    }
                }

                $imageName = $dni;
                $imagen->move($rutaCarpeta, $imageName);
    
                return response()->json(['image_path' => $dni]);
            } else {
                return response()->json(['error' => 'No se proporcionó ningún archivo de imagen'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar la imagen: ' . $e->getMessage()], 500);
        }
    }


    public function uploadAzangaro(Request $request) {
        try {
            $dni = $request->input('dni');
            $etapa = $request->input('etapa');
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');

                $rutaCarpeta = "";
                if($etapa == 'inscripcion'){ 
                     $rutaCarpeta = public_path('documentos/12/inscripciones/huellas/'); 
                } else {  $rutaCarpeta = public_path('documentos/12/control_biometrico/huellas/'); 
                }

                if (!file_exists($rutaCarpeta)) {
                    if (!mkdir($rutaCarpeta, 0777, true)) {
                        return response()->json(['error' => 'No se pudo crear la carpeta para guardar la imagen'], 500);
                    }
                }

                $imageName = $dni;
                $imagen->move($rutaCarpeta, $imageName);
    
                return response()->json(['image_path' => $dni]);
            } else {
                return response()->json(['error' => 'No se proporcionó ningún archivo de imagen'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar la imagen: ' . $e->getMessage()], 500);
        }
    }


    public function uploadJuli(Request $request) {
        try {
            $dni = $request->input('dni');
            $etapa = $request->input('etapa');
            if ($request->hasFile('imagen')) {
                $imagen = $request->file('imagen');

                $rutaCarpeta = "";
                if($etapa == 'inscripcion'){ 
                     $rutaCarpeta = public_path('documentos/11/inscripciones/huellas/'); 
                } else {  $rutaCarpeta = public_path('documentos/11/control_biometrico/huellas/'); 
                }

                if (!file_exists($rutaCarpeta)) {
                    if (!mkdir($rutaCarpeta, 0777, true)) {
                        return response()->json(['error' => 'No se pudo crear la carpeta para guardar la imagen'], 500);
                    }
                }

                $imageName = $dni;
                $imagen->move($rutaCarpeta, $imageName);
    
                return response()->json(['image_path' => $dni]);
            } else {
                return response()->json(['error' => 'No se proporcionó ningún archivo de imagen'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar la imagen: ' . $e->getMessage()], 500);
        }
    }


public function uploadFotos(Request $request) {
    try {
        $id_proceso = $request->input('id_proceso');
        $dni = $request->input('dni');
        $etapa = Str::lower($request->input('etapa'));

        // Verificar si se enviaron todos los archivos requeridos
        if (!$request->hasFile('Face') || !$request->hasFile('LFinger') || !$request->hasFile('RFinger')) {
            return response()->json([
                'error' => 'No se proporcionaron todos los archivos requeridos (Face, LFinger, RFinger)'
            ], 400);
        }

        $foto = $request->file('Face');
        $h_izquierda = $request->file('LFinger');
        $h_derecha = $request->file('RFinger');

        if($etapa == 'inscripcion'){
            $avance = AvancePostulante::updateOrCreate(
                [
                    'dni_postulante' => $dni,
                    'id_proceso'     => $id_proceso,
                ],
                [
                    'avance' => 2
                ]
            );
        } else {
            $avance = AvancePostulante::updateOrCreate(
                [
                    'dni_postulante' => $dni,
                    'id_proceso'     => $id_proceso,
                ],
                [
                    'avance' => 5
                ]
            );
        }
        // Rutas de las carpetas
        $rutaCarpetaHuellas = public_path("documentos/$id_proceso/$etapa/huellas/");
        $rutaCarpetaFotos = public_path("documentos/$id_proceso/$etapa/fotos/");

        // Crear carpetas si no existen
        if (!is_dir($rutaCarpetaHuellas) && !mkdir($rutaCarpetaHuellas, 0777, true) && !is_dir($rutaCarpetaHuellas)) {
            return response()->json([
                'error' => 'No se pudo crear la carpeta para guardar las huellas'
            ], 500);
        }

        if (!is_dir($rutaCarpetaFotos) && !mkdir($rutaCarpetaFotos, 0777, true) && !is_dir($rutaCarpetaFotos)) {
            return response()->json([
                'error' => 'No se pudo crear la carpeta para guardar las fotos'
            ], 500);
        }

        // Nombres de los archivos
        $fotoName = $dni . ".jpg";
        $hIzqName = $dni . "x.jpg";
        $hDerName = $dni . ".jpg";

        // Mover archivos a las carpetas correspondientes
        $foto->move($rutaCarpetaFotos, $fotoName);
        $h_izquierda->move($rutaCarpetaHuellas, $hIzqName);
        $h_derecha->move($rutaCarpetaHuellas, $hDerName);

        return response()->json([
            'foto_path' => asset("documentos/$id_proceso/$etapa/fotos/$fotoName"),
            'huella_izquierda_path' => asset("documentos/$id_proceso/$etapa/huellas/$hIzqName"),
            'huella_derecha_path' => asset("documentos/$id_proceso/$etapa/huellas/$hDerName"),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error al cargar los archivos: ' . $e->getMessage()
        ], 500);
    }
}





}
