<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asignatura;

class AsignaturaController extends Controller
{
    public function index()
    {
        $asignaturas = Asignatura::orderBy('orden', 'ASC')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $asignaturas;
        return response()->json($this->response, 200);
    }

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'nombre' => 'required|string|max:255',
            'orden' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $asignatura = Asignatura::create([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? (Asignatura::max('orden') + 1),
            'estado' => $request->estado ?? true,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $asignatura;
        $this->response['mensaje'] = 'Asignatura creada correctamente';
        return response()->json($this->response, 200);
    }

    public function update(Request $request, $id)
    {
        $asignatura = Asignatura::find($id);
        if (!$asignatura) {
            return response()->json(['estado' => false, 'mensaje' => 'Asignatura no encontrada'], 404);
        }

        $validator = validator($request->all(), [
            'nombre' => 'required|string|max:255',
            'orden' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $asignatura->update([
            'nombre' => $request->nombre,
            'orden' => $request->orden ?? $asignatura->orden,
            'estado' => $request->has('estado') ? $request->estado : $asignatura->estado,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $asignatura;
        $this->response['mensaje'] = 'Asignatura actualizada correctamente';
        return response()->json($this->response, 200);
    }

    public function destroy($id)
    {
        $asignatura = Asignatura::find($id);
        if (!$asignatura) {
            return response()->json(['estado' => false, 'mensaje' => 'Asignatura no encontrada'], 404);
        }

        $asignatura->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Asignatura eliminada correctamente';
        return response()->json($this->response, 200);
    }
}
