<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequisitoDocumento;
use App\Models\ProcesoRequisito;
use App\Models\Proceso;
use App\Models\Modalidad;
use App\Models\Programa;
use App\Models\TipoDocumento;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RequisitoDocumentoController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Requisitos/index');
    }

    public function getAll(Request $request)
    {
        $idProceso = auth()->user()->id_proceso;

        $query = RequisitoDocumento::with([
            'modalidades:id,nombre,codigo',
            'programas:id,nombre,nombre_corto',
            'tiposDocumento:id,nombre,codigo'
        ])
            ->select('id', 'nombre', 'obligatorio', 'orden', 'estado');

        $modalidadIds = DB::table('vacantes')
            ->where('id_proceso', $idProceso)
            ->where('estado', 1)
            ->pluck('id_modalidad')
            ->unique();

        $query->whereHas('modalidades', function ($q) use ($modalidadIds) {
            $q->whereIn('modalidad.id', $modalidadIds);
        });

        $res = $query->orderBy('orden')->orderBy('id')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function save(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'orden' => 'required|integer',
            'modalidades' => 'required|array|min:1',
            'modalidades.*' => 'integer',
            'programas' => 'nullable|array',
            'programas.*' => 'integer',
        ]);

        $programas = $request->programas ?? [];

        if (!$request->id) {
            $requisito = RequisitoDocumento::create([
                'nombre' => $request->nombre,
                'obligatorio' => $request->obligatorio ?? true,
                'orden' => $request->orden,
                'estado' => $request->estado ?? true,
                'id_usuario' => auth()->id(),
            ]);
            $requisito->modalidades()->sync($request->modalidades);
            $requisito->programas()->sync($programas);

            return response()->json([
                'titulo' => 'REGISTRO NUEVO',
                'mensaje' => 'Requisito creado con éxito',
                'estado' => true,
                'datos' => $requisito
            ], 200);
        }

        $requisito = RequisitoDocumento::findOrFail($request->id);
        $requisito->nombre = $request->nombre;
        $requisito->obligatorio = $request->obligatorio ?? true;
        $requisito->orden = $request->orden;
        $requisito->estado = $request->estado ?? true;
        $requisito->id_usuario = auth()->id();
        $requisito->save();
        $requisito->modalidades()->sync($request->modalidades);
        $requisito->programas()->sync($programas);

        return response()->json([
            'titulo' => '¡REGISTRO MODIFICADO!',
            'mensaje' => 'Requisito modificado con éxito',
            'estado' => true,
            'datos' => $requisito
        ], 200);
    }

    public function saveTiposDocumento(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'tipos_documento' => 'required|array',
            'tipos_documento.*' => 'integer',
        ]);

        $requisito = RequisitoDocumento::findOrFail($request->id);
        $requisito->tiposDocumento()->sync($request->tipos_documento);

        return response()->json([
            'titulo' => 'TIPOS ACTUALIZADOS',
            'mensaje' => 'Tipos de documento actualizados con éxito',
            'estado' => true,
        ], 200);
    }

    public function delete($id)
    {
        $requisito = RequisitoDocumento::find($id);
        $requisito->modalidades()->detach();
        $requisito->programas()->detach();
        $requisito->tiposDocumento()->detach();
        $requisito->delete();

        $this->response['titulo'] = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Requisito eliminado con éxito';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
    }

    public function getByProceso(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|integer',
        ]);

        $proceso = Proceso::findOrFail($request->id_proceso);

        $requisitos = RequisitoDocumento::with('tiposDocumento:id,nombre,codigo', 'modalidades:id,nombre,codigo', 'programas:id,nombre,nombre_corto')
            ->select('requisito_documento.id', 'requisito_documento.nombre', 'requisito_documento.obligatorio', 'requisito_documento.orden', 'requisito_documento.estado')
            ->leftJoin('proceso_requisito', function ($join) use ($request) {
                $join->on('proceso_requisito.id_requisito_documento', '=', 'requisito_documento.id')
                    ->where('proceso_requisito.id_proceso', $request->id_proceso);
            })
            ->addSelect('proceso_requisito.id as id_proceso_requisito', 'proceso_requisito.activo')
            ->where('requisito_documento.estado', true)
            ->orderBy('requisito_documento.orden')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $requisitos;
        return response()->json($this->response, 200);
    }

    public function importarRequisitos(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|integer',
            'requisitos' => 'required|array',
            'requisitos.*.id_requisito_documento' => 'required|integer',
            'requisitos.*.activo' => 'required|boolean',
        ]);

        foreach ($request->requisitos as $item) {
            ProcesoRequisito::updateOrCreate(
                [
                    'id_proceso' => $request->id_proceso,
                    'id_requisito_documento' => $item['id_requisito_documento'],
                ],
                [
                    'activo' => $item['activo'],
                ]
            );
        }

        return response()->json([
            'titulo' => 'REQUISITOS IMPORTADOS',
            'mensaje' => 'Requisitos importados al proceso con éxito',
            'estado' => true,
        ], 200);
    }

    public function saveProcesoRequisitos(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|integer',
            'requisitos' => 'required|array',
        ]);

        foreach ($request->requisitos as $item) {
            ProcesoRequisito::updateOrCreate(
                [
                    'id_proceso' => $request->id_proceso,
                    'id_requisito_documento' => $item['id_requisito_documento'],
                ],
                [
                    'activo' => $item['activo'],
                ]
            );
        }

        return response()->json([
            'titulo' => 'CONFIGURACIÓN GUARDADA',
            'mensaje' => 'Requisitos del proceso actualizados con éxito',
            'estado' => true,
        ], 200);
    }

    public function getModalidades(Request $request)
    {
        $idProceso = auth()->user()->id_proceso;

        $res = DB::select("SELECT mo.id AS value, mo.nombre AS label FROM (SELECT DISTINCT id_modalidad FROM vacantes WHERE id_proceso = ? AND estado = 1) AS pp JOIN modalidad mo ON mo.id = pp.id_modalidad ORDER BY mo.nombre", [$idProceso]);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getProgramas(Request $request)
    {
        $idProceso = auth()->user()->id_proceso;

        $query = DB::table('vacantes')
            ->select('programa.id as value', 'programa.nombre as label')
            ->join('programa', 'programa.id', '=', 'vacantes.id_programa')
            ->where('vacantes.id_proceso', $idProceso)
            ->where('vacantes.estado', 1);

        if ($request->filled('id_modalidad')) {
            $query->where('vacantes.id_modalidad', $request->id_modalidad);
        }

        $res = $query->groupBy('programa.id', 'programa.nombre')
            ->orderBy('programa.nombre')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getTiposDocumento()
    {
        $res = TipoDocumento::select('id as value', 'nombre as label')
            ->orderBy('nombre')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function getByModalidad($idModalidad)
    {
        $query = RequisitoDocumento::with([
            'modalidades:id,nombre,codigo',
            'programas:id,nombre,nombre_corto',
            'tiposDocumento:id,nombre,codigo'
        ])
            ->select('id', 'nombre', 'obligatorio', 'orden', 'estado')
            ->whereHas('modalidades', function ($q) use ($idModalidad) {
                $q->where('modalidad.id', $idModalidad);
            });

        $res = $query->orderBy('orden')->orderBy('id')->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }
}
