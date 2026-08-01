<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;
use App\Models\Sorteo;
use App\Models\SorteoSeleccionado;
use App\Models\SorteoCargoConfig;
use App\Models\ParticipantePersonal;
use App\Models\Cargo;
use App\Models\TipoPersonal;
use App\Exports\SorteoSeleccionadoExport;

class SorteoController extends Controller
{
    public function index()
    {
        $tipos = TipoPersonal::select('id as value', 'nombre as label')->orderBy('nombre')->get();
        $cargos = Cargo::select('id as value', 'nombre as label')->orderBy('nombre')->get();

        $sorteos = Sorteo::where('id_proceso', auth()->user()->id_proceso)
            ->with(['tiposPersonal:id'])
            ->select('id as value', 'nombre as label', 'estado', 'descripcion')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'value'       => $s->value,
                    'label'       => $s->label,
                    'estado'      => $s->estado,
                    'descripcion' => $s->descripcion,
                    'tipos'       => $s->tiposPersonal->pluck('id')->toArray(),
                ];
            });

        return Inertia::render('Admin/Participante/Sorteo', [
            'tipos'   => $tipos,
            'cargos'  => $cargos,
            'sorteos' => $sorteos,
        ]);
    }

    // ─── CRUD SORTEOS ───

    public function getSorteos()
    {
        $sorteos = Sorteo::where('id_proceso', auth()->user()->id_proceso)
            ->with(['tiposPersonal:id'])
            ->withCount(['seleccionados as asignados' => function ($q) {
                $q->where('anulado', false);
            }])
            ->select('id', 'nombre', 'descripcion', 'estado', 'created_at')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($s) {
                return [
                    'id'          => $s->id,
                    'nombre'      => $s->nombre,
                    'descripcion' => $s->descripcion,
                    'estado'      => $s->estado,
                    'asignados'   => $s->asignados,
                    'tipos'       => $s->tiposPersonal->pluck('id')->toArray(),
                ];
            });

        $this->response['estado'] = true;
        $this->response['datos']  = $sorteos;
        return response()->json($this->response, 200);
    }

    public function saveSorteo(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipos'       => 'nullable|array',
        ]);

        $data = [
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_proceso'  => auth()->user()->id_proceso,
            'id_usuario'  => auth()->id(),
        ];

        if ($request->filled('id')) {
            $sorteo = Sorteo::where('id', $request->id)
                ->where('id_proceso', auth()->user()->id_proceso)
                ->firstOrFail();
            $sorteo->update($data);
            $mensaje = 'Sorteo actualizado correctamente';
        } else {
            $sorteo = Sorteo::create($data);
            $mensaje = 'Sorteo creado correctamente';
        }

        if ($request->filled('tipos')) {
            $sorteo->tiposPersonal()->sync($request->tipos);
        } else {
            $sorteo->tiposPersonal()->detach();
        }

        $sorteo->load('tiposPersonal:id');
        $sorteo->tipos = $sorteo->tiposPersonal->pluck('id')->toArray();
        unset($sorteo->tiposPersonal);

        $this->response['estado']  = true;
        $this->response['mensaje'] = $mensaje;
        $this->response['datos']   = $sorteo;
        return response()->json($this->response, 200);
    }

    public function cambiarEstadoSorteo($id)
    {
        $sorteo = Sorteo::where('id', $id)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $sorteo->estado = !$sorteo->estado;
        $sorteo->save();

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Estado actualizado: ' . ($sorteo->estado ? 'Activo' : 'Inactivo');
        return response()->json($this->response, 200);
    }

    public function deleteSorteo($id)
    {
        $sorteo = Sorteo::where('id', $id)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        if ($sorteo->seleccionados()->exists()) {
            $this->response['estado']  = false;
            $this->response['mensaje'] = 'No se puede eliminar el sorteo porque ya tiene participantes asignados.';
            return response()->json($this->response, 200);
        }

        $sorteo->delete();

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Sorteo eliminado correctamente';
        return response()->json($this->response, 200);
    }

    // ─── CONFIGURACIÓN DE CARGOS POR SORTEO ───

    public function getConfigCargos($id_sorteo)
    {
        $sorteo = Sorteo::where('id', $id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $configs = SorteoCargoConfig::where('id_sorteo', $id_sorteo)
            ->join('cargos', 'cargos.id', '=', 'sorteo_cargo_config.id_cargo')
            ->select(
                'sorteo_cargo_config.id',
                'sorteo_cargo_config.id_sorteo',
                'sorteo_cargo_config.id_cargo',
                'cargos.nombre as cargo',
                'sorteo_cargo_config.cantidad'
            )
            ->orderBy('cargos.nombre')
            ->get()
            ->map(function ($config) use ($id_sorteo) {
                $asignados = SorteoSeleccionado::where('id_sorteo', $id_sorteo)
                    ->where('id_cargo', $config->id_cargo)
                    ->where('anulado', false)
                    ->count();

                $config->asignados  = $asignados;
                $config->disponibles = max(0, $config->cantidad - $asignados);
                return $config;
            });

        $this->response['estado'] = true;
        $this->response['datos']  = $configs;
        return response()->json($this->response, 200);
    }

    public function saveConfigCantidad(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
            'id_cargo'  => 'required|integer',
            'cantidad'  => 'required|integer|min:1',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $config = SorteoCargoConfig::updateOrCreate(
            [
                'id_sorteo' => $request->id_sorteo,
                'id_cargo'  => $request->id_cargo,
            ],
            [
                'cantidad'  => $request->cantidad,
                'id_usuario' => auth()->id(),
            ]
        );

        $cargo = Cargo::find($request->id_cargo);

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Configuración guardada para el cargo "' . $cargo->nombre . '"';
        $this->response['datos']   = $config;
        return response()->json($this->response, 200);
    }

    public function deleteConfigCantidad($id)
    {
        $config = SorteoCargoConfig::whereHas('sorteo', function ($q) {
            $q->where('id_proceso', auth()->user()->id_proceso);
        })->find($id);

        if (!$config) {
            return response()->json(['estado' => false, 'mensaje' => 'Configuración no encontrada'], 200);
        }

        $config->delete();

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Configuración de cargo eliminada';
        return response()->json($this->response, 200);
    }

    // ─── BÚSQUEDA Y ASIGNACIÓN ───

    public function buscarParticipante(Request $request)
    {
        $request->validate([
            'buscar'    => 'required|string|min:3',
            'id_sorteo' => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->with('tiposPersonal:id')
            ->firstOrFail();

        $tiposIds = $sorteo->tiposPersonal->pluck('id')->toArray();
        $termino = trim($request->buscar);
        $esDni = preg_match('/^\d{8}$/', $termino);

        $query = ParticipantePersonal::select(
            'participantes_personales.id',
            'participantes_personales.dni',
            'participantes_personales.nombres',
            'participantes_personales.paterno',
            'participantes_personales.materno',
            'participantes_personales.condicion',
            'participantes_personales.dependencia',
            'participantes_personales.foto',
            'participantes_personales.id_tipo_personal',
            'tipo_personal.nombre as tipo_personal'
        )
            ->leftJoin('tipo_personal', 'tipo_personal.id', '=', 'participantes_personales.id_tipo_personal')
            ->where('participantes_personales.estado', 1)
            ->when(!empty($tiposIds), function ($q) use ($tiposIds) {
                return $q->whereIn('participantes_personales.id_tipo_personal', $tiposIds);
            });

        if ($esDni) {
            $query->where('participantes_personales.dni', $termino);
        } else {
            $query->where(function ($q) use ($termino) {
                $q->where('participantes_personales.nombres', 'like', "%{$termino}%")
                  ->orWhere('participantes_personales.paterno', 'like', "%{$termino}%")
                  ->orWhere('participantes_personales.materno', 'like', "%{$termino}%");
            });
        }

        $participantes = $query->orderBy('participantes_personales.paterno')->limit(15)->get();

        if ($participantes->isEmpty()) {
            $mensaje = 'No se encontró participante activo';
            if (!empty($tiposIds)) {
                $mensaje .= ' o no pertenece a los tipos de personal configurados';
            }
            $mensaje .= '.';
            return response()->json([
                'estado' => false,
                'mensaje' => $mensaje,
            ], 200);
        }

        // Si es un solo resultado, devolver con estado de selección
        if ($participantes->count() === 1) {
            $participante = $participantes->first();
            $seleccionExistente = SorteoSeleccionado::where('id_participante', $participante->id)
                ->where('id_sorteo', $request->id_sorteo)
                ->leftJoin('cargos', 'cargos.id', '=', 'sorteo_seleccionados.id_cargo')
                ->select('cargos.nombre as cargo_asignado', 'sorteo_seleccionados.observado', 'sorteo_seleccionados.anulado')
                ->orderByRaw('sorteo_seleccionados.anulado ASC')
                ->first();

            $yaSeleccionado = $seleccionExistente && !$seleccionExistente->anulado;

            return response()->json([
                'estado' => true,
                'datos'  => $participante,
                'ya_seleccionado' => $yaSeleccionado,
                'cargo_asignado'  => $seleccionExistente?->cargo_asignado,
                'observado'       => $seleccionExistente && !$seleccionExistente->anulado ? $seleccionExistente->observado : false,
                'fue_anulado'     => $seleccionExistente?->anulado ?? false,
            ], 200);
        }

        // Múltiples resultados: devolver lista
        return response()->json([
            'estado' => true,
            'multiple' => true,
            'datos'  => $participantes,
        ], 200);
    }

    public function registrarSeleccion(Request $request)
    {
        $request->validate([
            'id_participante' => 'required|integer',
            'id_cargo'         => 'required|integer',
            'id_sorteo'        => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        // Buscar si ya existe un registro (activo o anulado) en este sorteo
        $existente = SorteoSeleccionado::where('id_participante', $request->id_participante)
            ->where('id_sorteo', $request->id_sorteo)
            ->orderByRaw('anulado ASC')
            ->first();

        if ($existente && !$existente->anulado) {
            $mensaje = 'Este participante ya está en la lista de seleccionados.';
            if ($existente->observado) {
                $mensaje = 'Este participante está OBSERVADO, no se puede asignar a otro cargo.';
            }
            return response()->json([
                'estado' => false,
                'mensaje' => $mensaje,
            ], 200);
        }

        // Validar cupos disponibles
        $config = SorteoCargoConfig::where('id_sorteo', $request->id_sorteo)
            ->where('id_cargo', $request->id_cargo)
            ->first();

        if (!$config) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Este cargo no tiene configuración de cantidad. Configure la cantidad primero.',
            ], 200);
        }

        $asignados = SorteoSeleccionado::where('id_sorteo', $request->id_sorteo)
            ->where('id_cargo', $request->id_cargo)
            ->where('anulado', false)
            ->count();

        if ($asignados >= $config->cantidad) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Ya no hay espacio, cupos vacantes no disponibles para este cargo.',
            ], 200);
        }

        // Si existe un registro anulado, reactivarlo con el nuevo cargo
        if ($existente && $existente->anulado) {
            $existente->update([
                'id_cargo'         => $request->id_cargo,
                'anulado'           => false,
                'motivo_anulacion'  => null,
                'observado'         => false,
                'observacion'       => null,
                'es_manual'         => false,
                'id_usuario'        => auth()->id(),
            ]);
            $seleccion = $existente;
            $mensaje = 'Participante reasignado correctamente';
        } else {
            $seleccion = SorteoSeleccionado::create([
                'id_sorteo'       => $request->id_sorteo,
                'id_participante' => $request->id_participante,
                'id_cargo'        => $request->id_cargo,
                'id_usuario'      => auth()->id(),
                'es_manual'       => false,
            ]);
            $mensaje = 'Participante agregado a la lista de seleccionados';
        }

        $this->response['estado']  = true;
        $this->response['mensaje'] = $mensaje;
        $this->response['datos']   = $seleccion;
        return response()->json($this->response, 200);
    }

    public function registrarManual(Request $request)
    {
        $request->validate([
            'id_participante' => 'required|integer',
            'id_cargo'         => 'required|integer',
            'id_sorteo'        => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        // Verificar si ya existe un registro activo
        $existente = SorteoSeleccionado::where('id_participante', $request->id_participante)
            ->where('id_sorteo', $request->id_sorteo)
            ->orderByRaw('anulado ASC')
            ->first();

        if ($existente && !$existente->anulado) {
            $mensaje = 'Este participante ya está en la lista de seleccionados.';
            if ($existente->observado) {
                $mensaje = 'Este participante está OBSERVADO, no se puede asignar a otro cargo.';
            }
            return response()->json([
                'estado' => false,
                'mensaje' => $mensaje,
            ], 200);
        }

        // La asignación manual NO valida cupos — puede exceder la cantidad configurada
        if ($existente && $existente->anulado) {
            $existente->update([
                'id_cargo'         => $request->id_cargo,
                'anulado'           => false,
                'motivo_anulacion'  => null,
                'observado'         => false,
                'observacion'       => null,
                'es_manual'         => true,
                'id_usuario'        => auth()->id(),
            ]);
            $seleccion = $existente;
            $mensaje = 'Participante agregado manualmente (exento de cupos)';
        } else {
            $seleccion = SorteoSeleccionado::create([
                'id_sorteo'       => $request->id_sorteo,
                'id_participante' => $request->id_participante,
                'id_cargo'        => $request->id_cargo,
                'id_usuario'      => auth()->id(),
                'es_manual'       => true,
            ]);
            $mensaje = 'Participante agregado manualmente (exento de cupos)';
        }

        $this->response['estado']  = true;
        $this->response['mensaje'] = $mensaje;
        $this->response['datos']   = $seleccion;
        return response()->json($this->response, 200);
    }

    public function getSeleccionados(Request $request)
    {
        $request->validate([
            'id_sorteo'   => 'required|integer',
            'id_cargo'    => 'nullable|integer',
            'incluir_anulados' => 'nullable|boolean',
            'filtro_origen' => 'nullable|string|in:sorteo,manual',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $query = SorteoSeleccionado::select(
            'sorteo_seleccionados.id',
            'sorteo_seleccionados.id_participante',
            'sorteo_seleccionados.id_cargo',
            'sorteo_seleccionados.observacion',
            'sorteo_seleccionados.observado',
            'sorteo_seleccionados.anulado',
            'sorteo_seleccionados.es_manual',
            'sorteo_seleccionados.motivo_anulacion',
            'sorteo_seleccionados.created_at',
            'pp.dni',
            'pp.nombres',
            'pp.paterno',
            'pp.materno',
            'pp.foto',
            'pp.condicion',
            'pp.dependencia',
            'tp.nombre as tipo_personal',
            'cargos.nombre as cargo'
        )
            ->leftJoin('participantes_personales as pp', 'pp.id', '=', 'sorteo_seleccionados.id_participante')
            ->leftJoin('tipo_personal as tp', 'tp.id', '=', 'pp.id_tipo_personal')
            ->leftJoin('cargos', 'cargos.id', '=', 'sorteo_seleccionados.id_cargo')
            ->where('sorteo_seleccionados.id_sorteo', $request->id_sorteo)
            ->when($request->filled('id_cargo'), function ($q) use ($request) {
                return $q->where('sorteo_seleccionados.id_cargo', $request->id_cargo);
            })
            ->when(!$request->boolean('incluir_anulados'), function ($q) {
                return $q->where('sorteo_seleccionados.anulado', false);
            })
            ->when($request->filled('filtro_origen'), function ($q) use ($request) {
                if ($request->filtro_origen === 'manual') {
                    return $q->where('sorteo_seleccionados.es_manual', true);
                } else {
                    return $q->where('sorteo_seleccionados.es_manual', false);
                }
            })
            ->orderBy('sorteo_seleccionados.id', 'DESC');

        $res = $query->get();

        $this->response['estado'] = true;
        $this->response['datos']  = $res;
        return response()->json($this->response, 200);
    }

    public function deleteSeleccionado($id)
    {
        $seleccion = SorteoSeleccionado::whereHas('sorteo', function ($q) {
            $q->where('id_proceso', auth()->user()->id_proceso);
        })->find($id);

        if (!$seleccion) {
            return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 200);
        }

        $seleccion->delete();

        $this->response['titulo']  = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Participante removido de la lista';
        $this->response['estado']  = true;
        return response()->json($this->response, 200);
    }

    // ─── OBSERVAR / ANULAR / RESTABLECER ───

    public function observarParticipante(Request $request)
    {
        $request->validate([
            'id'          => 'required|integer',
            'observacion' => 'required|string',
        ]);

        $seleccion = SorteoSeleccionado::whereHas('sorteo', function ($q) {
            $q->where('id_proceso', auth()->user()->id_proceso);
        })->find($request->id);

        if (!$seleccion) {
            return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 200);
        }

        $seleccion->update([
            'observado'   => true,
            'observacion' => $request->observacion,
        ]);

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Participante observado';
        return response()->json($this->response, 200);
    }

    public function anularParticipante(Request $request)
    {
        $request->validate([
            'id'               => 'required|integer',
            'motivo_anulacion' => 'required|string',
        ]);

        $seleccion = SorteoSeleccionado::whereHas('sorteo', function ($q) {
            $q->where('id_proceso', auth()->user()->id_proceso);
        })->find($request->id);

        if (!$seleccion) {
            return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 200);
        }

        $seleccion->update([
            'anulado'          => true,
            'motivo_anulacion' => $request->motivo_anulacion,
        ]);

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Participante anulado, cupo liberado';
        return response()->json($this->response, 200);
    }

    public function restablecerParticipante(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $seleccion = SorteoSeleccionado::whereHas('sorteo', function ($q) {
            $q->where('id_proceso', auth()->user()->id_proceso);
        })->find($request->id);

        if (!$seleccion) {
            return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 200);
        }

        // Validar cupo si se está restableciendo un anulado
        if ($seleccion->anulado) {
            $asignados = SorteoSeleccionado::where('id_sorteo', $seleccion->id_sorteo)
                ->where('id_cargo', $seleccion->id_cargo)
                ->where('anulado', false)
                ->count();

            $config = SorteoCargoConfig::where('id_sorteo', $seleccion->id_sorteo)
                ->where('id_cargo', $seleccion->id_cargo)
                ->first();

            if ($config && $asignados >= $config->cantidad) {
                return response()->json([
                    'estado' => false,
                    'mensaje' => 'No se puede restablecer: el cargo ya tiene todos los cupos ocupados.',
                ], 200);
            }
        }

        $seleccion->update([
            'observado'        => false,
            'observacion'      => null,
            'anulado'          => false,
            'motivo_anulacion' => null,
        ]);

        $this->response['estado']  = true;
        $this->response['mensaje'] = 'Participante restablecido';
        return response()->json($this->response, 200);
    }

    // ─── EXPORTACIONES ───

    public function exportExcel(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
            'id_cargo'  => 'nullable|integer',
            'filtro_origen' => 'nullable|string|in:sorteo,manual',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $data = $this->getExportData($request->id_sorteo, $request->id_cargo, $request->filtro_origen);

        $nombreArchivo = 'Sorteo_' . str_replace(' ', '_', $sorteo->nombre);
        if ($request->filled('id_cargo')) {
            $cargo = Cargo::find($request->id_cargo);
            $nombreArchivo .= '_' . str_replace(' ', '_', $cargo->nombre ?? '');
        }
        $nombreArchivo .= '_' . date('d-m-Y') . '.xlsx';

        return Excel::download(new SorteoSeleccionadoExport($data), $nombreArchivo);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
            'id_cargo'  => 'nullable|integer',
            'filtro_origen' => 'nullable|string|in:sorteo,manual',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;

        $cargoNombre = null;
        if ($request->filled('id_cargo')) {
            $cargo = Cargo::find($request->id_cargo);
            $cargoNombre = $cargo->nombre ?? null;
        }

        $titulo = 'Lista de Seleccionados';
        if ($cargoNombre) {
            $titulo .= ' — ' . $cargoNombre;
        }
        if ($request->filtro_origen === 'manual') {
            $titulo .= ' (Solo Designación)';
        } elseif ($request->filtro_origen === 'sorteo') {
            $titulo .= ' (Solo Sorteo)';
        }

        $mpdf = $this->initMpdf($proceso, $sorteo, $titulo);
        $headerHtml = View::make('Reportes.sorteo_header', compact('proceso', 'sorteo', 'titulo'))->render();
        $footerHtml = View::make('Reportes.sorteo_footer')->render();
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        // Obtener configuración de cargos para saber el orden y cantidades
        $configs = $this->getCargosConfigData($request->id_sorteo);

        if ($request->filled('id_cargo')) {
            $configs = $configs->filter(function ($c) use ($request) {
                return $c->id == $request->id_cargo;
            })->values();
        }

        // Agrupar activos y anulados por cargo
        $activos = $this->getSeleccionadosData($request->id_sorteo, $request->id_cargo, false, false, $request->filtro_origen);
        $anulados = $this->getSeleccionadosData($request->id_sorteo, $request->id_cargo, true, true, $request->filtro_origen);

        $activosPorCargo = $activos->groupBy('cargo');
        $anuladosPorCargo = $anulados->groupBy('cargo');

        $chunk = View::make('Reportes.sorteo_lista_cargos', [
            'configs'         => $configs,
            'activosPorCargo' => $activosPorCargo,
            'anuladosPorCargo' => $anuladosPorCargo,
        ])->render();
        $chunk = mb_convert_encoding($chunk, 'HTML-ENTITIES', 'UTF-8');
        $mpdf->WriteHTML($chunk);

        $filename = 'Sorteo_' . str_replace(' ', '_', $sorteo->nombre) . '_' . date('d-m-Y') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    public function exportObservadosPdf(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;

        $observados = SorteoSeleccionado::select(
            'sorteo_seleccionados.id',
            'pp.dni',
            'pp.paterno',
            'pp.materno',
            'pp.nombres',
            'tp.nombre as tipo_personal',
            'cargos.nombre as cargo',
            'pp.condicion',
            'pp.dependencia',
            'sorteo_seleccionados.observacion'
        )
            ->leftJoin('participantes_personales as pp', 'pp.id', '=', 'sorteo_seleccionados.id_participante')
            ->leftJoin('tipo_personal as tp', 'tp.id', '=', 'pp.id_tipo_personal')
            ->leftJoin('cargos', 'cargos.id', '=', 'sorteo_seleccionados.id_cargo')
            ->where('sorteo_seleccionados.id_sorteo', $request->id_sorteo)
            ->where('sorteo_seleccionados.observado', true)
            ->where('sorteo_seleccionados.anulado', false)
            ->orderBy('cargos.nombre')
            ->orderBy('pp.paterno')
            ->get();

        $anulados = SorteoSeleccionado::select(
            'sorteo_seleccionados.id',
            'pp.dni',
            'pp.paterno',
            'pp.materno',
            'pp.nombres',
            'tp.nombre as tipo_personal',
            'cargos.nombre as cargo',
            'pp.condicion',
            'pp.dependencia',
            'sorteo_seleccionados.motivo_anulacion'
        )
            ->leftJoin('participantes_personales as pp', 'pp.id', '=', 'sorteo_seleccionados.id_participante')
            ->leftJoin('tipo_personal as tp', 'tp.id', '=', 'pp.id_tipo_personal')
            ->leftJoin('cargos', 'cargos.id', '=', 'sorteo_seleccionados.id_cargo')
            ->where('sorteo_seleccionados.id_sorteo', $request->id_sorteo)
            ->where('sorteo_seleccionados.anulado', true)
            ->orderBy('cargos.nombre')
            ->orderBy('pp.paterno')
            ->get();

        $titulo = 'Observados y Anulados';

        $mpdf = $this->initMpdf($proceso, $sorteo, $titulo);
        $headerHtml = View::make('Reportes.sorteo_header', compact('proceso', 'sorteo', 'titulo'))->render();
        $footerHtml = View::make('Reportes.sorteo_footer')->render();
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        $chunk = View::make('Reportes.sorteo_lista_observados', [
            'items'  => $observados,
            'titulo' => 'Participantes Observados',
        ])->render();
        $chunk = mb_convert_encoding($chunk, 'HTML-ENTITIES', 'UTF-8');
        $mpdf->WriteHTML($chunk);

        if (count($anulados) > 0) {
            $mpdf->AddPage();
            $chunkAnulados = View::make('Reportes.sorteo_lista_anulados', [
                'items'  => $anulados,
                'titulo' => 'Participantes Anulados',
            ])->render();
            $chunkAnulados = mb_convert_encoding($chunkAnulados, 'HTML-ENTITIES', 'UTF-8');
            $mpdf->WriteHTML($chunkAnulados);
        }

        $filename = 'Observados_Amulados_' . str_replace(' ', '_', $sorteo->nombre) . '_' . date('d-m-Y') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    public function exportResumenPdf(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;

        $configs = $this->getCargosConfigData($request->id_sorteo);

        $totalVacantes = $configs->sum('cantidad');
        $totalAsignados = $configs->sum('asignados');
        $totalDisponibles = $configs->sum('disponibles');

        $mpdf = $this->initMpdf($proceso, $sorteo, 'Resumen de Cargos');
        $headerHtml = View::make('Reportes.sorteo_header', [
            'proceso' => $proceso,
            'sorteo'  => $sorteo,
            'titulo'  => 'Resumen de Cargos',
        ])->render();
        $footerHtml = View::make('Reportes.sorteo_footer')->render();
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        $chunk = View::make('Reportes.sorteo_resumen', compact(
            'configs', 'sorteo', 'proceso', 'totalVacantes', 'totalAsignados', 'totalDisponibles'
        ))->render();
        $chunk = mb_convert_encoding($chunk, 'HTML-ENTITIES', 'UTF-8');
        $mpdf->WriteHTML($chunk);

        $filename = 'Resumen_' . str_replace(' ', '_', $sorteo->nombre) . '_' . date('d-m-Y') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    private function initMpdf($proceso, $sorteo, $titulo)
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'dejavusanscondensed',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 38,
            'margin_bottom' => 18,
            'margin_header' => 8,
            'margin_footer' => 8,
        ]);

        $mpdf->SetTitle($titulo . ' — ' . ($proceso->nombre ?? ''));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->SetDisplayMode('fullpage');

        return $mpdf;
    }

    private function getSeleccionadosData($id_sorteo, $id_cargo = null, $soloAnulados = false, $incluirAnulados = false, $filtroOrigen = null)
    {
        $query = SorteoSeleccionado::select(
            'sorteo_seleccionados.id',
            'pp.dni',
            'pp.paterno',
            'pp.materno',
            'pp.nombres',
            'pp.foto',
            'pp.codigo_trabajador',
            'tp.nombre as tipo_personal',
            'cargos.nombre as cargo',
            'pp.condicion',
            'pp.dependencia',
            'sorteo_seleccionados.observacion',
            'sorteo_seleccionados.motivo_anulacion',
            'sorteo_seleccionados.anulado',
            'sorteo_seleccionados.observado',
            'sorteo_seleccionados.es_manual'
        )
            ->leftJoin('participantes_personales as pp', 'pp.id', '=', 'sorteo_seleccionados.id_participante')
            ->leftJoin('tipo_personal as tp', 'tp.id', '=', 'pp.id_tipo_personal')
            ->leftJoin('cargos', 'cargos.id', '=', 'sorteo_seleccionados.id_cargo')
            ->where('sorteo_seleccionados.id_sorteo', $id_sorteo)
            ->when($id_cargo, function ($q) use ($id_cargo) {
                return $q->where('sorteo_seleccionados.id_cargo', $id_cargo);
            });

        if ($soloAnulados) {
            $query->where('sorteo_seleccionados.anulado', true);
        } elseif (!$incluirAnulados) {
            $query->where('sorteo_seleccionados.anulado', false);
        }

        if ($filtroOrigen === 'manual') {
            $query->where('sorteo_seleccionados.es_manual', true);
        } elseif ($filtroOrigen === 'sorteo') {
            $query->where('sorteo_seleccionados.es_manual', false);
        }

        return $query->orderBy('cargos.nombre')->orderBy('pp.paterno')->get();
    }

    private function getCargosConfigData($id_sorteo)
    {
        return SorteoCargoConfig::where('id_sorteo', $id_sorteo)
            ->join('cargos', 'cargos.id', '=', 'sorteo_cargo_config.id_cargo')
            ->select(
                'sorteo_cargo_config.id',
                'cargos.nombre as cargo',
                'sorteo_cargo_config.cantidad'
            )
            ->orderBy('cargos.nombre')
            ->get()
            ->map(function ($config) use ($id_sorteo) {
                $asignados = SorteoSeleccionado::where('id_sorteo', $id_sorteo)
                    ->where('id_cargo', $config->id)
                    ->where('anulado', false)
                    ->count();

                $config->asignados   = $asignados;
                $config->disponibles = max(0, $config->cantidad - $asignados);
                $config->porcentaje  = $config->cantidad > 0 ? round(($asignados / $config->cantidad) * 100) : 0;
                return $config;
            });
    }

    private function getExportData($id_sorteo, $id_cargo = null, $filtroOrigen = null)
    {
        $items = $this->getSeleccionadosData($id_sorteo, $id_cargo, false, true, $filtroOrigen);

        return $items->map(function ($item) {
            $estado = 'Activo';
            if ($item->anulado) {
                $estado = 'ANULADO';
            } elseif ($item->observado) {
                $estado = 'Observado';
            }

            return [
                'DNI'           => $item->dni,
                'PATERNO'       => $item->paterno,
                'MATERNO'       => $item->materno,
                'NOMBRES'       => $item->nombres,
                'TIPO PERSONAL' => $item->tipo_personal ?? '',
                'CARGO'         => $item->cargo ?? '',
                'ORIGEN'        => $item->es_manual ? 'MANUAL' : 'SORTEO',
                'CONDICION'     => $item->condicion ?? '',
                'DEPENDENCIA'   => $item->dependencia ?? '',
                'OBSERVACION'   => $item->observacion ?? '',
                'ESTADO'        => $estado,
            ];
        })->toArray();
    }

    public function exportCargosConfigPdf(Request $request)
    {
        $request->validate([
            'id_sorteo' => 'required|integer',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;
        $configs = $this->getCargosConfigData($request->id_sorteo);

        $mpdf = $this->initMpdf($proceso, $sorteo, 'Configuración de Cargos');
        $headerHtml = View::make('Reportes.sorteo_header', [
            'proceso' => $proceso,
            'sorteo'  => $sorteo,
            'titulo'  => 'Configuración de Cargos',
        ])->render();
        $footerHtml = View::make('Reportes.sorteo_footer')->render();
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        $chunk = View::make('Reportes.sorteo_cargos_config', compact('configs', 'sorteo', 'proceso'))->render();
        $chunk = mb_convert_encoding($chunk, 'HTML-ENTITIES', 'UTF-8');
        $mpdf->WriteHTML($chunk);

        $filename = 'Cargos_' . str_replace(' ', '_', $sorteo->nombre) . '_' . date('d-m-Y') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    public function exportCredencialesPdf(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'id_sorteo' => 'required|integer',
            'id_cargo'  => 'nullable|integer',
            'filtro_origen' => 'nullable|string|in:sorteo,manual',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;

        $seleccionados = $this->getSeleccionadosData($request->id_sorteo, $request->id_cargo, false, true, $request->filtro_origen);

        // Resolver rutas absolutas de fotos y convertir a base64; generar iniciales si no hay foto
        $seleccionados->each(function ($p) {
            if (!empty($p->foto)) {
                $relative = ltrim($p->foto, '/');
                $absPath = public_path($relative);
                $p->foto_base64 = (file_exists($absPath) && is_file($absPath))
                    ? 'data:image/' . (strtolower(pathinfo($absPath, PATHINFO_EXTENSION)) === 'png' ? 'png' : 'jpeg') . ';base64,' . base64_encode(file_get_contents($absPath))
                    : null;
            } else {
                $p->foto_base64 = null;
            }

            // Generar iniciales: primera letra del primer nombre + primera del paterno + primera del materno
            $iniciales = '';
            $primerNombre = explode(' ', trim($p->nombres ?? ''))[0] ?? '';
            if ($primerNombre !== '') {
                $iniciales .= mb_substr($primerNombre, 0, 1);
            }
            if (!empty($p->paterno)) {
                $iniciales .= mb_substr($p->paterno, 0, 1);
            }
            if (!empty($p->materno)) {
                $iniciales .= mb_substr($p->materno, 0, 1);
            }
            $p->iniciales = mb_strtoupper($iniciales) ?: '?';
        });

        // Logos institucionales
        $logoTinyPath = public_path('imagenes/logotiny.png');
        $logoDadPath  = public_path('imagenes/logoDAD.png');

        $logo_tiny_base64 = (file_exists($logoTinyPath) && is_file($logoTinyPath))
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoTinyPath))
            : null;

        $logo_dad_base64 = (file_exists($logoDadPath) && is_file($logoDadPath))
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoDadPath))
            : null;

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusanscondensed',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->SetTitle('Credenciales — ' . ($sorteo->nombre ?? ''));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');

        $html = View::make('Reportes.sorteo_credenciales', [
            'seleccionados'   => $seleccionados,
            'sorteo'          => $sorteo,
            'proceso'         => $proceso,
            'logo_tiny_base64'=> $logo_tiny_base64,
            'logo_dad_base64' => $logo_dad_base64,
        ])->render();

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $mpdf->WriteHTML($html);

        $filename = 'Credenciales_' . str_replace(' ', '_', $sorteo->nombre) . '_' . date('d-m-Y') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    public function getCredencialesData(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'id_sorteo' => 'required|integer',
            'id_cargo'  => 'nullable|integer',
            'filtro_origen' => 'nullable|string|in:sorteo,manual',
        ]);

        $sorteo = Sorteo::where('id', $request->id_sorteo)
            ->where('id_proceso', auth()->user()->id_proceso)
            ->firstOrFail();

        $proceso = $sorteo->proceso;

        $seleccionados = $this->getSeleccionadosData($request->id_sorteo, $request->id_cargo, false, true, $request->filtro_origen);

        $data = $seleccionados->map(function ($p) {
            return [
                'id'               => $p->id,
                'dni'              => $p->dni,
                'nombres'          => $p->nombres,
                'paterno'          => $p->paterno,
                'materno'          => $p->materno,
                'foto'             => $p->foto ? asset($p->foto) : null,
                'codigo_trabajador'=> $p->codigo_trabajador,
                'condicion'        => $p->condicion,
                'dependencia'      => $p->dependencia,
                'tipo_personal'    => $p->tipo_personal,
                'cargo'            => $p->cargo,
                'es_manual'        => $p->es_manual,
            ];
        });

        return response()->json([
            'estado'       => true,
            'seleccionados'=> $data,
            'proceso'      => [
                'nombre'        => $proceso->nombre ?? null,
                'anio'          => $proceso->anio ?? null,
                'fecha_examen'  => $proceso->fecha_examen ?? null,
                'codigo_proceso'=> $proceso->codigo_proceso ?? null,
            ],
            'logo_tiny'    => asset('imagenes/logotiny.png'),
            'logo_dad'     => asset('imagenes/logoDAD.png'),
        ]);
    }
}
