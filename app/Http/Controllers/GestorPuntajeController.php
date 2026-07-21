<?php

namespace App\Http\Controllers;

use App\Models\Puntaje;
use App\Models\Proceso;
use App\Models\Programa;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PuntajePlantillaExport;
use App\Imports\PuntajesImport;

class GestorPuntajeController extends Controller
{
    public function index(Request $request)
    {
        $query = Puntaje::query();

        if ($request->filled('id_proceso')) {
            $query->where('id_proceso', $request->id_proceso);
        }
        if ($request->filled('programa')) {
            $query->where('programa', $request->programa);
        }
        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('dni', 'LIKE', "%{$s}%")
                  ->orWhere('paterno', 'LIKE', "%{$s}%")
                  ->orWhere('nombres', 'LIKE', "%{$s}%");
            });
        }

        $resultados = $query->orderByDesc('puntaje')->paginate(50);

        return response()->json([
            'estado' => true,
            'datos' => $resultados,
        ]);
    }

    public function getSelectores()
    {
        $procesos = Proceso::where('estado', 1)->select('id', 'nombre')->orderByDesc('id')->get();

        $programas = Puntaje::whereNotNull('programa')
            ->where('programa', '!=', '')
            ->select('programa')
            ->distinct()
            ->orderBy('programa')
            ->pluck('programa');

        $modalidades = Puntaje::whereNotNull('modalidad')
            ->where('modalidad', '!=', '')
            ->select('modalidad')
            ->distinct()
            ->orderBy('modalidad')
            ->pluck('modalidad');

        return response()->json([
            'estado' => true,
            'procesos' => $procesos,
            'programas' => $programas->map(fn($p) => ['id' => $p, 'nombre' => $p]),
            'modalidades' => $modalidades->map(fn($m) => ['id' => $m, 'nombre' => $m]),
        ]);
    }

    public function importar(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'id_proceso' => 'required|integer',
        ]);

        try {
            $import = new PuntajesImport($request->id_proceso);
            Excel::import($import, $request->file('file'));

            return response()->json([
                'estado' => true,
                'mensaje' => "Importación completada. Procesados: {$import->getTotal()}. Nuevos: {$import->getInsertados()}. Actualizados: {$import->getActualizados()}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Error al importar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function plantilla()
    {
        return Excel::download(new PuntajePlantillaExport(), 'plantilla_puntajes.xlsx');
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'dni'             => 'required|string',
            'id_proceso'      => 'required|integer',
            'fecha'           => 'nullable|date',
            'paterno'         => 'nullable|string',
            'materno'         => 'nullable|string',
            'nombres'         => 'nullable|string',
            'puntaje'         => 'nullable|numeric',
            'puntaje_vocacional' => 'nullable|numeric',
            'apto'            => 'nullable|string',
            'programa'        => 'nullable|string',
            'area'            => 'nullable|string',
            'modalidad'       => 'nullable|string',
            'id_inscripcion'  => 'nullable|integer',
            'puesto'          => 'nullable|integer',
        ]);

        $data = $request->only([
            'dni', 'id_proceso', 'fecha', 'paterno', 'materno', 'nombres',
            'puntaje', 'puntaje_vocacional', 'apto', 'programa', 'area',
            'modalidad', 'id_inscripcion', 'puesto',
        ]);

        if ($request->filled('id')) {
            $registro = Puntaje::find($request->id);
            if (!$registro) {
                return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 404);
            }
            $registro->update($data);
            return response()->json(['estado' => true, 'mensaje' => 'Registro actualizado correctamente']);
        }

        Puntaje::create($data);
        return response()->json(['estado' => true, 'mensaje' => 'Registro creado correctamente']);
    }

    public function eliminarRegistro(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        Puntaje::where('id', $request->id)->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => 'Registro eliminado correctamente.',
        ]);
    }

    public function eliminarTodo(Request $request)
    {
        $request->validate(['id_proceso' => 'required|integer']);

        $eliminados = Puntaje::where('id_proceso', $request->id_proceso)->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => "Se eliminaron {$eliminados} registros del proceso seleccionado.",
        ]);
    }
}
