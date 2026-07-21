<?php

namespace App\Http\Controllers;
use App\Models\DocumentosBiometrico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DB;

class DniController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'dni' => 'required|string',
            #'file' => 'nullable|file|mimes:pdf|max:4096',
            'tipo' => 'required|integer'
        ]);
    
        $dni = $request->dni;
        $id_proceso = $request->id_proceso;
        $rutaCarpeta = 'documentos/' . $id_proceso . '/biometrico/dnis/';
    
        if (!File::exists(public_path($rutaCarpeta))) {
            if (!File::makeDirectory(public_path($rutaCarpeta), 0755, true)) {
                return response()->json(['error' => 'Unable to create directory'], 500);
            }
        }
    
        if (!$request->has('id')) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $rutaCarpeta . $dni . '.pdf';
                $file->move(public_path($rutaCarpeta), $dni . '.pdf');
    
                $dni = new DocumentosBiometrico();
                $dni->observacion = $request->observacion;
                $dni->dni = $request->dni;
                $dni->id_tipo = $request->tipo;
                $dni->id_proceso = $request->id_proceso;
                $dni->url = $filePath;
                $dni->save();

                return response()->json(['message' => 'Registrado con exito'], 200);
             } else {
                return response()->json(['error' => 'No file found'], 400);
            }
        } else {
            $dni = DocumentosBiometrico::find($request->id);
            if (!$dni) {
                return response()->json(['error' => 'Record not found'], 404);
            }
    
            $dni->observacion = $request->observacion;
            $dni->dni = $request->dni;
            $dni->id_tipo = $request->tipo;
    
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $rutaCarpeta . $dni . '.pdf';
                $file->move(public_path($rutaCarpeta), $dni . '.pdf');
                $dni->url = $filePath;
            }
    
            $dni->id_usuario = auth()->id();
            $saved = $dni->save();
    
            if ($saved) {
                return response()->json(['message' => 'Record updated successfully'], 200);
            } else {
                return response()->json(['error' => 'Failed to update the record'], 500);
            }
        }
    }

    public function getDnis( Request $request){
        $res = DB::select("SELECT crt.id, crt.dni, crt.id_tipo, crt.observacion, crt.url, crt.estado FROM documentos_biometrico crt
        WHERE dni = ". $request->dni . " AND crt.id_tipo IN (3,4,5,6,7)");
    
        $this->response['estado'] = !empty($res);
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }


    public function delete($id) {
         
        $file = DocumentosBiometrico::find($id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }
        if (Storage::disk('public')->exists($file->url)) {
            Storage::disk('public')->delete($file->url);
        }
        $file->delete();

        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    
    }


}
