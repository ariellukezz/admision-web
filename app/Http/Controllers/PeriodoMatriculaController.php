<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PeriodoMatriculaController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Periodos/index');
    }

    public function getPeriodos()
    {
        $periodos = DB::table('periodos_matricula')
            ->select('id', 'nombre', 'descripcion', 'activo', 'created_at', 'updated_at')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($periodos as $p) {
            $p->total_procesos = DB::table('periodo_proceso')
                ->where('id_periodo', $p->id)
                ->count();
        }

        return response()->json(['estado' => true, 'data' => $periodos]);
    }

    public function savePeriodo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
        ]);

        $id = $request->input('id');

        if ($id) {
            DB::table('periodos_matricula')->where('id', $id)->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'updated_at' => now(),
            ]);
        } else {
            $id = DB::table('periodos_matricula')->insertGetId([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['estado' => true, 'mensaje' => 'Periodo guardado correctamente', 'id' => $id]);
    }

    public function toggleActivo($id)
    {
        // Desactivar todos
        DB::table('periodos_matricula')->update(['activo' => 0]);

        // Activar el seleccionado
        DB::table('periodos_matricula')->where('id', $id)->update(['activo' => 1, 'updated_at' => now()]);

        return response()->json(['estado' => true, 'mensaje' => 'Periodo activado']);
    }

    public function deletePeriodo($id)
    {
        DB::table('periodos_matricula')->where('id', $id)->delete();
        return response()->json(['estado' => true, 'mensaje' => 'Periodo eliminado']);
    }

    public function getProcesosPeriodo($id)
    {
        $asignados = DB::table('periodo_proceso as pp')
            ->join('procesos as p', 'p.id', '=', 'pp.id_proceso')
            ->where('pp.id_periodo', $id)
            ->select('p.id', 'p.nombre', 'p.anio', 'p.ciclo', 'p.estado')
            ->get();

        $disponibles = DB::table('procesos')
            ->whereNotIn('id', $asignados->pluck('id'))
            ->select('id', 'nombre', 'anio', 'ciclo', 'estado')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'estado' => true,
            'asignados' => $asignados,
            'disponibles' => $disponibles,
        ]);
    }

    public function saveProcesos(Request $request)
    {
        $request->validate([
            'id_periodo' => 'required|integer',
            'procesos' => 'array',
        ]);

        $idPeriodo = $request->id_periodo;
        $procesos = $request->input('procesos', []);

        // Sincronizar: eliminar los que ya no están y agregar los nuevos
        DB::table('periodo_proceso')->where('id_periodo', $idPeriodo)->delete();

        $now = now();
        $rows = [];
        foreach ($procesos as $idProceso) {
            $rows[] = [
                'id_periodo' => $idPeriodo,
                'id_proceso' => $idProceso,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('periodo_proceso')->insertOrIgnore($rows);
        }

        return response()->json(['estado' => true, 'mensaje' => 'Procesos asignados correctamente']);
    }
}
