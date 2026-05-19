<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FotoController extends Controller {

    public function guardarFotoInscripcion(Request $request)
    {
        if ($request->has('photo')) {
            $photoData = $request->input('photo');
            $photo = str_replace('data:image/png;base64,', '', $photoData);
            $photo = str_replace(' ', '+', $photo);
            $photoData = base64_decode($photo);
    
            $fileName = $request->dni . '.jpg';
    
            $rutaCarpeta = public_path('documentos/'.auth()->user()->id_proceso.'/inscripciones/fotos/');
            if (!File::exists($rutaCarpeta)) {
                File::makeDirectory($rutaCarpeta, 0755, true, true);
            }
    
            $filePath = $rutaCarpeta . $fileName;
            file_put_contents($filePath, $photoData);
    
            return response()->json(['message' => 'Foto recortada guardada correctamente']);
        }
    
        return response()->json(['error' => 'No se proporcionó ninguna foto recortada'], 400);
    }


    public function guardarFotoBiometrico(Request $request)
    {
        if ($request->has('photo')) {
            $photoData = $request->input('photo');
            $photo = str_replace('data:image/png;base64,', '', $photoData);
            $photo = str_replace(' ', '+', $photo);
            $photoData = base64_decode($photo);
    
            $fileName = $request->dni . '.jpg';
            $rutaCarpeta = public_path('documentos/'.auth()->user()->id_proceso.'/control_biometrico/fotos/');
            if (!File::exists($rutaCarpeta)) {
                File::makeDirectory($rutaCarpeta, 0755, true, true);
            }

            $filePath = $rutaCarpeta . $fileName;
            file_put_contents($filePath, $photoData);
            return response()->json(['message' => 'Foto recortada guardada correctamente']);
        }
    
        return response()->json(['error' => 'No se proporcionó ninguna foto recortada'], 400);
    }

    public function getCodigoConexion()
    {
        return response()->json([
            'status' => true,
            'codigo_conexion' => '8hIcptrxisoqUtpvU1YZa9eYaZgViuS0LqI0pq6RfmT3O1QJnyAqzwKQNjzr'
        ], 200);
    }

}
