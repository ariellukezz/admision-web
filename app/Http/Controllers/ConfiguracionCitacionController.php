<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionCitacion;
use App\Models\Proceso;
use App\Models\Modalidad;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracionCitacionController extends Controller
{
    public function index()
    {
        $configuraciones = ConfiguracionCitacion::with(['proceso:id,nombre,anio,ciclo', 'usuario:id,name'])
            ->orderBy('id_proceso')
            ->orderBy('tipo_criterio')
            ->orderBy('fecha')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'id_proceso' => $c->id_proceso,
                    'proceso_nombre' => $c->proceso?->nombre . ' ' . ($c->proceso?->anio ?? ''),
                    'tipo_criterio' => $c->tipo_criterio,
                    'valor' => $c->valor,
                    'fecha' => $c->fecha?->format('Y-m-d'),
                    'hora_inicio' => $c->hora_inicio,
                    'hora_fin' => $c->hora_fin,
                    'lugar' => $c->lugar,
                    'instrucciones' => $c->instrucciones,
                    'estado' => $c->estado,
                    'usuario' => $c->usuario?->name,
                    'created_at' => $c->created_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json(['success' => true, 'datos' => $configuraciones]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|exists:procesos,id',
            'tipo_criterio' => 'required|in:mismo_dia,dni_digito,area,modalidad,programa',
            'valor' => 'nullable|string|max:255',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'lugar' => 'required|string|max:255',
            'instrucciones' => 'nullable|string|max:1000',
            'estado' => 'boolean',
        ]);

        $config = ConfiguracionCitacion::create([
            'id_proceso' => $request->id_proceso,
            'tipo_criterio' => $request->tipo_criterio,
            'valor' => $request->tipo_criterio === 'mismo_dia' ? null : $request->valor,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'lugar' => $request->lugar,
            'instrucciones' => $request->instrucciones,
            'estado' => $request->boolean('estado', true),
            'id_usuario' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Configuración creada', 'datos' => $config]);
    }

    public function update(Request $request, int $id)
    {
        $config = ConfiguracionCitacion::find($id);

        if (!$config) {
            return response()->json(['success' => false, 'mensaje' => 'Configuración no encontrada'], 404);
        }

        $request->validate([
            'id_proceso' => 'sometimes|exists:procesos,id',
            'tipo_criterio' => 'sometimes|in:mismo_dia,dni_digito,area,modalidad,programa',
            'valor' => 'nullable|string|max:255',
            'fecha' => 'sometimes|date',
            'hora_inicio' => 'sometimes',
            'hora_fin' => 'sometimes',
            'lugar' => 'sometimes|string|max:255',
            'instrucciones' => 'nullable|string|max:1000',
            'estado' => 'boolean',
        ]);

        $data = $request->all();
        if (($request->tipo_criterio ?? $config->tipo_criterio) === 'mismo_dia') {
            $data['valor'] = null;
        }
        $data['id_usuario'] = Auth::id();

        $config->update($data);

        return response()->json(['success' => true, 'mensaje' => 'Configuración actualizada', 'datos' => $config]);
    }

    public function destroy(int $id)
    {
        $config = ConfiguracionCitacion::find($id);

        if (!$config) {
            return response()->json(['success' => false, 'mensaje' => 'Configuración no encontrada'], 404);
        }

        $config->delete();

        return response()->json(['success' => true, 'mensaje' => 'Configuración eliminada']);
    }

    public function getProcesos()
    {
        $procesos = Proceso::select('id', 'nombre', 'anio', 'ciclo')
            ->orderBy('anio', 'desc')
            ->orderBy('nombre')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nombre' => "{$p->nombre} {$p->anio}" . ($p->ciclo ? " - {$p->ciclo}" : ''),
            ]);

        return response()->json(['success' => true, 'datos' => $procesos]);
    }

    public function getModalidades()
    {
        $modalidades = Modalidad::select('id', 'nombre', 'codigo')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();

        return response()->json(['success' => true, 'datos' => $modalidades]);
    }

    public function getProgramas()
    {
        $programas = Programa::select('id', 'nombre', 'codigo', 'area')
            ->where('estado', true)
            ->orderBy('area')
            ->orderBy('nombre')
            ->get();

        $areas = $programas->pluck('area')->filter()->unique()->values();

        return response()->json([
            'success' => true,
            'datos' => $programas,
            'areas' => $areas,
        ]);
    }
}
