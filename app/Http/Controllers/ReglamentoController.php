<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reglamento;
use Illuminate\Support\Facades\File;

class ReglamentoController extends Controller
{

  public function getSelectReglamentos(){
    $res = Reglamento::select('id as value','nombre as label')->where('estado',1)->get();
    
    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
  }


  public function getReglamentos(Request $request)
  {
    $res = Reglamento::select('reglamento.*')
    ->when($request->term, fn($q, $term) => $q->where('reglamento.nombre', 'LIKE', "%{$term}%"))
    ->orderByDesc('reglamento.id')
    ->paginate(50);

    return response()->json([ 'estado' => true, 'datos' => $res ], 200);
  }

  public function saveReglamento(Request $request)
  {
      $request->validate([
          'nombre' => 'required|string|max:255',
          'estado' => 'required|boolean',
          'inicio_vigencia' => 'nullable|date',
          'fin_vigencia' => 'nullable|date|after_or_equal:inicio_vigencia',
          'file' => 'required|file|mimes:pdf|max:2048'
      ]);

      if ($request->hasFile('file')) {
          $directory = public_path('documentos/reglamentos');

          if (!File::exists($directory)) {
              File::makeDirectory($directory, 0777, true);
          }

          $file = $request->file('file');
          $filename = time() . '_' . $file->getClientOriginalName();
          $file->move($directory, $filename);

          if(!$request->id){
              $reglamento = new Reglamento();
              $reglamento->nombre = $request->nombre;
              $reglamento->version = 1;
              $reglamento->url = '/documentos/reglamentos/' . $filename;
              $reglamento->estado = $request->estado;
              $reglamento->inicio_vigencia = $request->inicio_vigencia;
              $reglamento->fin_vigencia = $request->fin_vigencia;
              $reglamento->id_usuario = auth()->id();
              $reglamento->save();
          }else {
            $reglamento = Reglamento::find($request->id);

            if ($reglamento->url) {
                $rutaArchivo = str_replace(url('/'), '', $reglamento->url);
                $rutaArchivo = ltrim($rutaArchivo, '/');
                if (File::exists(public_path($rutaArchivo))) {
                    File::delete(public_path($rutaArchivo));
                }
            }

            $reglamento->nombre = $request->nombre;
            $reglamento->version = 1;
            $reglamento->url = '/documentos/reglamentos/' . $filename;
            $reglamento->estado = $request->estado;
            $reglamento->inicio_vigencia = $request->inicio_vigencia;
            $reglamento->fin_vigencia = $request->fin_vigencia;
            $reglamento->id_usuario = auth()->id();
            $reglamento->save();
          }

          $this->response['estado'] = true;
          return response()->json($this->response, 200);

      }

      return response()->json(['error' => 'No se subió ningún archivo'], 400);
  }


  public function eliminarReglamento($id)
  {
    $reglamento = Reglamento::findOrFail($id);
    if ($reglamento->url) {
        $rutaAbsoluta = public_path(parse_url($reglamento->url, PHP_URL_PATH));
        
        if (File::exists($rutaAbsoluta)) {
            File::delete($rutaAbsoluta);
        }
    }

    $reglamento->delete();

    return response()->json([
        'success' => true,
        'message' => 'Reglamento y archivo eliminados correctamente'
    ]);
  }



}
