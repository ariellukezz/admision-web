<?php

namespace App\Http\Controllers;
use App\Models\DocumentosBiometrico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DB;

class DocumentosResultadoController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:pdf|max:4096',
            'tipo' => 'required|integer'
        ]);
    
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
                $filePath = $rutaCarpeta . $doc_resultado . '.pdf';
                $file->move(public_path($rutaCarpeta), $doc_resultado . '.pdf');
    
                $doc_resultado = new DocumentosBiometrico();
                $doc_resultado->observacion = $request->observacion;
                $doc_resultado->id_tipo = $request->tipo;
                $doc_resultado->url = $filePath;
                $doc_resultado->save();

                return response()->json(['message' => 'Registrado con exito'], 200);
             } else {
                return response()->json(['error' => 'No file found'], 400);
            }
        } else {
            $doc_resultado = DocumentosBiometrico::find($request->id);
            if (!$doc_resultado) {
                return response()->json(['error' => 'Record not found'], 404);
            }
    
            $doc_resultado->observacion = $request->observacion;
            $doc_resultado->id_tipo = $request->tipo;
    
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $filePath = $rutaCarpeta . $fileName;
                $file->move(public_path($rutaCarpeta), $fileName);
                $doc_resultado->url = $filePath;
            }
    
            $doc_resultado->id_usuario = auth()->id();
            $saved = $doc_resultado->save();
    
            if ($saved) {
                return response()->json(['message' => 'Record updated successfully'], 200);
            } else {
                return response()->json(['error' => 'Failed to update the record'], 500);
            }
        }
    }

    public function getDocumentos( Request $request){
        $res = DB::select("SELECT nombre, id_tipo, fecha, url FROM documentos_resultado 
            WHERE id_proceso = $request->id_proceso");
    
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