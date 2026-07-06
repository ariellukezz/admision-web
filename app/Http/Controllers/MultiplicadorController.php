<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Multiplicador;

class MultiplicadorController extends Controller
{
    public function index()
    {
        $multiplicadores = Multiplicador::orderBy('id', 'ASC')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $multiplicadores;
        return response()->json($this->response, 200);
    }

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'nombre' => 'required|string|max:255',
            'correcta' => 'required|numeric',
            'incorrecta' => 'required|numeric',
            'blanco' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $multiplicador = Multiplicador::create([
            'nombre' => $request->nombre,
            'correcta' => $request->correcta,
            'incorrecta' => $request->incorrecta,
            'blanco' => $request->blanco,
            'estado' => $request->estado ?? true,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $multiplicador;
        $this->response['mensaje'] = 'Multiplicador creado correctamente';
        return response()->json($this->response, 200);
    }

    public function update(Request $request, $id)
    {
        $multiplicador = Multiplicador::find($id);
        if (!$multiplicador) {
            return response()->json(['estado' => false, 'mensaje' => 'Multiplicador no encontrado'], 404);
        }

        $validator = validator($request->all(), [
            'nombre' => 'required|string|max:255',
            'correcta' => 'required|numeric',
            'incorrecta' => 'required|numeric',
            'blanco' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $multiplicador->update([
            'nombre' => $request->nombre,
            'correcta' => $request->correcta,
            'incorrecta' => $request->incorrecta,
            'blanco' => $request->blanco,
            'estado' => $request->has('estado') ? $request->estado : $multiplicador->estado,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $multiplicador;
        $this->response['mensaje'] = 'Multiplicador actualizado correctamente';
        return response()->json($this->response, 200);
    }

    public function destroy($id)
    {
        $multiplicador = Multiplicador::find($id);
        if (!$multiplicador) {
            return response()->json(['estado' => false, 'mensaje' => 'Multiplicador no encontrado'], 404);
        }

        $multiplicador->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Multiplicador eliminado correctamente';
        return response()->json($this->response, 200);
    }
}
