<?php

namespace App\Http\Controllers;
use App\Models\DocumentosBiometrico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DB;

class CertificadoController extends Controller
{
    public function save(Request $request)
    {
        $request->validate([
            'dni' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:4096',
            'tipo' => 'required|integer'
        ]);
    
        $dni = $request->dni;
        $id_proceso = $request->id_proceso;
        $rutaCarpeta = 'documentos/' . $id_proceso . '/biometrico/certificados/';
    
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
    
                $certificado = new DocumentosBiometrico();
                $certificado->observacion = $request->observacion;
                $certificado->dni= $request->dni;
                $certificado->id_tipo = $request->tipo;
                $certificado->id_proceso = $request->id_proceso;
                $certificado->url = $filePath;
                $certificado->save();

                $certificado->save();
                return response()->json(['message' => 'Registrado con exito'], 200);
             } else {
                return response()->json(['error' => 'No file found'], 400);
            }
        } else {
            $certificado = DocumentosBiometrico::find($request->id);
            if (!$certificado) {
                return response()->json(['error' => 'Record not found'], 404);
            }
    
            $certificado->observacion = $request->observacion;
            $certificado->dni = $request->dni;
            $certificado->id_tipo = $request->tipo;
    
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $rutaCarpeta . $dni . '.pdf';
                $file->move(public_path($rutaCarpeta), $dni . '.pdf');
                $certificado->url = $filePath;
            }
    
            $certificado->id_usuario = auth()->id();
            $saved = $certificado->save();
    
            // Verificar si se guardó correctamente
            if ($saved) {
                return response()->json(['message' => 'Record updated successfully'], 200);
            } else {
                return response()->json(['error' => 'Failed to update the record'], 500);
            }
        }
    }

    public function getCertificados( Request $request){
        $res = DB::select("SELECT crt.id, crt.dni, crt.id_tipo, crt.observacion, crt.url, crt.estado, col.gestion, col.nombre AS colegio
        FROM documentos_biometrico crt
        JOIN postulante pos ON crt.dni = pos.nro_doc 
        JOIN colegios col ON col.id = pos.id_colegio
        WHERE dni = ". $request->dni ." AND crt.id_tipo IN (1,2)");
    
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
