<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cargo;
use Illuminate\Support\Facades\File;

class CargoController extends Controller
{
    public function index()
    {
        return Inertia::render('Cargos/index');
    }

    public function getCargos(Request $request)
    {
        $res = Cargo::select('cargos.*')
            ->when($request->term, function ($q, $term) {
                return $q->where(function ($query) use ($term) {
                    $query->orWhere('cargos.nombre', 'LIKE', '%' . $term . '%')
                          ->orWhere('cargos.descripcion', 'LIKE', '%' . $term . '%');
                });
            })
            ->orderBy('cargos.id', 'DESC')
            ->paginate(50);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function saveCargo(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'estado'      => 'nullable|boolean',
            'file'        => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $directory = public_path('documentos/cargos');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0777, true);
        }

        if (!$request->id) {
            $cargo = new Cargo();
            $cargo->nombre      = $request->nombre;
            $cargo->descripcion = $request->descripcion;
            $cargo->estado      = $request->estado ? 1 : 0;
            $cargo->id_usuario  = auth()->id();

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($directory, $filename);
                $cargo->url = '/documentos/cargos/' . $filename;
            }

            $cargo->save();

            $this->response['titulo']  = 'REGISTRO CREADO';
            $this->response['mensaje'] = 'Cargo "' . $cargo->nombre . '" creado con éxito';
        } else {
            $cargo = Cargo::findOrFail($request->id);
            $cargo->nombre      = $request->nombre;
            $cargo->descripcion = $request->descripcion;
            $cargo->estado      = $request->estado ? 1 : 0;
            $cargo->id_usuario  = auth()->id();

            if ($request->hasFile('file')) {
                if ($cargo->url) {
                    $rutaArchivo = ltrim(str_replace(url('/'), '', $cargo->url), '/');
                    if (File::exists(public_path($rutaArchivo))) {
                        File::delete(public_path($rutaArchivo));
                    }
                }

                $file     = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($directory, $filename);
                $cargo->url = '/documentos/cargos/' . $filename;
            }

            $cargo->save();

            $this->response['titulo']  = '¡REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Cargo "' . $cargo->nombre . '" actualizado con éxito';
        }

        $this->response['estado'] = true;
        $this->response['datos']  = $cargo;
        return response()->json($this->response, 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $cargo = Cargo::findOrFail($id);
        $cargo->estado = $request->estado ? 1 : 0;
        $cargo->save();

        $this->response['estado']  = true;
        $this->response['titulo']  = 'ESTADO ACTUALIZADO';
        $this->response['mensaje'] = 'Cargo ' . $cargo->nombre . ' ' . ($cargo->estado ? 'activado' : 'desactivado');
        return response()->json($this->response, 200);
    }

    public function deleteCargo($id)
    {
        $cargo = Cargo::findOrFail($id);

        if ($cargo->url) {
            $rutaAbsoluta = public_path(parse_url($cargo->url, PHP_URL_PATH));
            if (File::exists($rutaAbsoluta)) {
                File::delete($rutaAbsoluta);
            }
        }

        $nombre = $cargo->nombre;
        $cargo->delete();

        $this->response['titulo']  = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Cargo "' . $nombre . '" eliminado con éxito';
        $this->response['estado']  = true;
        return response()->json($this->response, 200);
    }
}
