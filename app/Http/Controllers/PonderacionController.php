<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ponderacion;
use App\Models\PonderacionDetalle;
use App\Models\Asignatura;

class PonderacionController extends Controller
{

    public function getPonderaciones(Request $request)
    {
        $res = Ponderacion::select(
            'id',
            'nombre',
            'total_preguntas',
            'total_ponderacion',
            'estado'
        )
        ->where(function ($query) use ($request) {
            if ($request->term) {
                $query->where('nombre', 'LIKE', '%' . $request->term . '%');
            }
        })
        ->orderBy('id', 'DESC')
        ->paginate($request->paginasize ?? 10);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function save(Request $request)
    {
        $validator = validator($request->all(), [
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $ponderacion = Ponderacion::updateOrCreate(
            ['id' => $request->id ?? null],
            [
                'nombre' => $request->nombre,
                'estado' => $request->estado ?? true,
            ]
        );

        $this->response['estado'] = true;
        $this->response['datos'] = $ponderacion;
        return response()->json($this->response, 200);
    }

    public function destroy($id)
    {
        $ponderacion = Ponderacion::find($id);
        if (!$ponderacion) {
            return response()->json(['estado' => false, 'mensaje' => 'Ponderación no encontrada'], 404);
        }

        PonderacionDetalle::where('id_ponderacion_simulacro', $id)->delete();
        $ponderacion->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Ponderación eliminada correctamente';
        return response()->json($this->response, 200);
    }

    public function duplicar($id)
    {
        $original = Ponderacion::with('detalles')->find($id);
        if (!$original) {
            return response()->json(['estado' => false, 'mensaje' => 'Ponderación no encontrada'], 404);
        }

        DB::beginTransaction();
        try {
            $nueva = Ponderacion::create([
                'nombre' => $original->nombre . ' (Copia)',
                'estado' => true,
            ]);

            foreach ($original->detalles as $detalle) {
                PonderacionDetalle::create([
                    'asignatura' => $detalle->asignatura,
                    'numero' => $detalle->numero,
                    'ponderacion' => $detalle->ponderacion,
                    'id_ponderacion_simulacro' => $nueva->id,
                    'id_asignatura' => $detalle->id_asignatura,
                    'cantidad_preguntas' => $detalle->cantidad_preguntas,
                    'subtotal' => $detalle->subtotal,
                ]);
            }

            DB::commit();
            $this->response['estado'] = true;
            $this->response['datos'] = $nueva;
            return response()->json($this->response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['estado' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getDetalle($id)
    {
        $detalles = PonderacionDetalle::where('id_ponderacion_simulacro', $id)
            ->orderBy('numero', 'ASC')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $detalles;
        return response()->json($this->response, 200);
    }

    public function saveDetalle(Request $request)
    {
        $validator = validator($request->all(), [
            'id_ponderacion' => 'required|exists:ponderacion_simulacro,id',
            'detalles' => 'required|array',
            'detalles.*.cantidad_preguntas' => 'required|integer|min:0',
            'detalles.*.ponderacion' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $ponderacion = Ponderacion::find($request->id_ponderacion);

        DB::beginTransaction();
        try {
            PonderacionDetalle::where('id_ponderacion_simulacro', $request->id_ponderacion)->delete();

            $numero = 1;
            foreach ($request->detalles as $item) {
                $cantidad = (int) $item['cantidad_preguntas'];
                $pond = (float) $item['ponderacion'];
                $subtotal = $cantidad * $pond;

                $idAsignatura = $item['id_asignatura'] ?? null;
                if (!$idAsignatura) {
                    $asig = Asignatura::where('nombre', $item['asignatura'])->first();
                    $idAsignatura = $asig?->id;
                }

                PonderacionDetalle::create([
                    'asignatura' => $item['asignatura'] ?? '',
                    'numero' => $numero,
                    'ponderacion' => $pond,
                    'id_ponderacion_simulacro' => $request->id_ponderacion,
                    'id_asignatura' => $idAsignatura,
                    'cantidad_preguntas' => $cantidad,
                    'subtotal' => $subtotal,
                ]);
                $numero++;
            }

            $totalPreguntas = collect($request->detalles)->sum('cantidad_preguntas');
            $totalPonderacion = collect($request->detalles)
                ->sum(fn($d) => (int)$d['cantidad_preguntas'] * (float)$d['ponderacion']);

            $ponderacion->update([
                'total_preguntas' => $totalPreguntas,
                'total_ponderacion' => $totalPonderacion,
            ]);

            DB::commit();
            $this->response['estado'] = true;
            $this->response['mensaje'] = 'Detalle guardado correctamente';
            return response()->json($this->response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['estado' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function insertarPonderacion(Request $request)
    {
        $data = $request->input('pesos');
        $id_ponderacion = (int) $request->id_ponderacion['id'];

        $cont = 1;

        foreach ($data as $item) {
            for ($i = 0; $i < (int)$item['n_preguntas']; $i++) {
                PonderacionDetalle::create([
                    'asignatura' => $item['nombre'],
                    'ponderacion' => (float) $item['ponderacion'],
                    'numero' => $cont,
                    'id_ponderacion_simulacro' => $id_ponderacion
                ]);
                $cont++;
            }
        }

        return response()->json(['ok' => true]);
    }

    public function getPonderacionesSelect(Request $request)
    {
        $res = Ponderacion::select(
            'id as key',
            'nombre as value'
        )
        ->where(function ($query) use ($request) {
            if ($request->term) {
                $query->where('nombre', 'LIKE', '%' . $request->term . '%');
            }
        })
        ->orderBy('id', 'DESC')
        ->paginate($request->paginasize ?? 10);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getAsignaturas()
    {
        $asignaturas = Asignatura::where('estado', true)
            ->orderBy('orden', 'ASC')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $asignaturas;
        return response()->json($this->response, 200);
    }

    public function getAreas()
    {
        $areas = \App\Models\Area::where('estado', true)->orderBy('id', 'ASC')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $areas;
        return response()->json($this->response, 200);
    }
}
