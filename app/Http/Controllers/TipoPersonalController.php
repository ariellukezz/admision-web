<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TipoPersonal;

class TipoPersonalController extends Controller
{
    public function index()
    {
        return Inertia::render('TipoPersonal/index');
    }

    public function getTipos(Request $request)
    {
        $res = TipoPersonal::select('tipo_personal.*')
            ->when($request->term, function ($q, $term) {
                return $q->where(function ($query) use ($term) {
                    $query->orWhere('tipo_personal.nombre', 'LIKE', '%' . $term . '%')
                          ->orWhere('tipo_personal.descripcion', 'LIKE', '%' . $term . '%');
                });
            })
            ->orderBy('tipo_personal.id', 'DESC')
            ->paginate(50);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function saveTipo(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        if (!$request->id) {
            $tipo = TipoPersonal::create([
                'nombre'      => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            $this->response['titulo']  = 'REGISTRO CREADO';
            $this->response['mensaje'] = 'Tipo de personal "' . $tipo->nombre . '" creado con éxito';
        } else {
            $tipo = TipoPersonal::findOrFail($request->id);
            $tipo->nombre      = $request->nombre;
            $tipo->descripcion = $request->descripcion;
            $tipo->save();

            $this->response['titulo']  = '¡REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Tipo de personal "' . $tipo->nombre . '" actualizado con éxito';
        }

        $this->response['estado'] = true;
        $this->response['datos']  = $tipo;
        return response()->json($this->response, 200);
    }

    public function deleteTipo($id)
    {
        $tipo = TipoPersonal::findOrFail($id);
        $nombre = $tipo->nombre;
        $tipo->delete();

        $this->response['titulo']  = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Tipo de personal "' . $nombre . '" eliminado con éxito';
        $this->response['estado']  = true;
        return response()->json($this->response, 200);
    }
}
