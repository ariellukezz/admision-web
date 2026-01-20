<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Anio;

class AnioController extends Controller
{
    public function index()
    {
        return Inertia::render('Anio/index');
    }

    public function getAnios(Request $request)
    {
        $res = Anio::where(function ($query) use ($request) {
            $query->where('anio', 'LIKE', '%' . $request->term . '%')
                  ->orWhere('nombre', 'LIKE', '%' . $request->term . '%');
        })
        ->orderBy('anio', 'DESC')
        ->paginate(10);

        return response()->json([
            'estado' => true,
            'datos' => $res
        ]);
    }

    public function saveAnio(Request $request)
    {
        $request->validate([
            'anio'   => 'required|string|max:10',
            'nombre' => 'required|string|max:255',
            'id'     => 'nullable|integer'
        ]);

        if (!$request->id) {
            $anio = Anio::create([
                'anio'   => $request->anio,
                'nombre' => $request->nombre,
            ]);

            return response()->json([
                'estado' => true,
                'titulo' => 'REGISTRO NUEVO',
                'mensaje' => "AÑO {$anio->anio} CREADO CON ÉXITO",
                'datos' => $anio
            ]);
        }

        $anio = Anio::findOrFail($request->id);
        $anio->update([
            'anio'   => $request->anio,
            'nombre' => $request->nombre,
        ]);

        return response()->json([
            'estado' => true,
            'titulo' => 'REGISTRO MODIFICADO',
            'mensaje' => "AÑO {$anio->anio} MODIFICADO CON ÉXITO",
            'datos' => $anio
        ]);
    }

    public function deleteAnio($id)
    {
        $anio = Anio::findOrFail($id);
        $anio->delete();

        return response()->json([
            'estado' => true,
            'titulo' => 'REGISTRO ELIMINADO',
            'mensaje' => "AÑO {$anio->anio} ELIMINADO CON ÉXITO",
            'datos' => $anio
        ]);
    }
}
