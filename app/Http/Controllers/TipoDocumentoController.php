<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TipoDocumento;
use Inertia\Inertia;

class TipoDocumentoController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/TiposDocumento/index');
    }

    public function getTiposDocumento(Request $request)
    {
        $query = TipoDocumento::select('id', 'nombre', 'codigo');

        if ($request->filled('term')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'LIKE', '%' . $request->term . '%')
                  ->orWhere('codigo', 'LIKE', '%' . $request->term . '%');
            });
        }

        $res = $query->orderBy('id', 'asc')->paginate(10);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function save(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:30',
            'codigo' => 'required|string|max:12',
        ]);

        try {
            if (!$request->id) {
                $tipo = TipoDocumento::create([
                    'nombre' => $request->nombre,
                    'codigo' => $request->codigo,
                ]);

                return response()->json([
                    'estado' => true,
                    'titulo' => 'REGISTRO NUEVO',
                    'mensaje' => "TIPO DE DOCUMENTO {$tipo->nombre} CREADO CON ÉXITO",
                    'datos' => $tipo
                ], 200);
            }

            $tipo = TipoDocumento::findOrFail($request->id);
            $tipo->update([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
            ]);

            return response()->json([
                'estado' => true,
                'titulo' => 'REGISTRO MODIFICADO',
                'mensaje' => "TIPO DE DOCUMENTO {$tipo->nombre} MODIFICADO CON ÉXITO",
                'datos' => $tipo
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'estado' => false,
                'titulo' => 'ERROR',
                'mensaje' => 'Ocurrió un error al guardar el tipo de documento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $tipo = TipoDocumento::find($id);
        $nombre = $tipo->nombre;
        $tipo->delete();

        $this->response['titulo'] = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'TIPO DE DOCUMENTO ' . $nombre . ' ELIMINADO CON EXITO';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }
}
