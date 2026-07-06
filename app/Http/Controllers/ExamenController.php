<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simulacro;
use App\Models\ExamenSimulacro;
use App\Models\ExamenTipo;
use App\Models\Excepciones;
use App\Models\Resp;
use App\Models\ArchivoSimulacro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ExamenController extends Controller
{
    /**
     * Lista exámenes (examen_simulacro) directamente, con count de tipos y res.
     */
    public function index(Request $request)
    {
        $res = ExamenSimulacro::select(
            'examen_simulacro.id',
            'examen_simulacro.id_simulacro',
            'examen_simulacro.area',
            'examen_simulacro.n_preguntas',
            'examen_simulacro.n_alternativas',
            'examen_simulacro.estado'
        )
        ->with('simulacro:id,nombre')
        ->withCount('tipos as tipos_count')
        ->where(function ($query) use ($request) {
            if ($request->term) {
                $query->where('examen_simulacro.area', 'LIKE', '%' . $request->term . '%');
            }
        })
        ->orderBy('examen_simulacro.id', 'DESC')
        ->paginate($request->paginasize ?? 10);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function tiposPage($id_examen)
    {
        $examen = ExamenSimulacro::find($id_examen);
        if (!$examen) {
            return redirect()->route('calificar-examenes');
        }

        return Inertia::render('Simulacro/Calificacion/tipos', [
            'examen' => $examen,
        ]);
    }

    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'area' => 'required|string|max:50',
            'n_preguntas' => 'nullable|integer|min:1',
            'n_alternativas' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $examen = ExamenSimulacro::create([
            'id_simulacro' => $request->id_simulacro,
            'area' => $request->area,
            'n_preguntas' => $request->n_preguntas ?? 60,
            'n_alternativas' => $request->n_alternativas ?? 5,
            'estado' => $request->estado ?? 1,
        ]);

        // Crear un tipo por defecto
        ExamenTipo::create([
            'id_examen_simulacro' => $examen->id,
            'tipo' => null,
            'respuestas' => null,
            'estado' => true,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $examen;
        $this->response['mensaje'] = 'Examen creado correctamente';
        return response()->json($this->response, 200);
    }

    public function update(Request $request, $id)
    {
        $examen = ExamenSimulacro::find($id);
        if (!$examen) {
            return response()->json(['estado' => false, 'mensaje' => 'Examen no encontrado'], 404);
        }

        $validator = validator($request->all(), [
            'area' => 'required|string|max:50',
            'n_preguntas' => 'nullable|integer|min:1',
            'n_alternativas' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $examen->update([
            'id_simulacro' => $request->id_simulacro,
            'area' => $request->area,
            'n_preguntas' => $request->n_preguntas ?? 60,
            'n_alternativas' => $request->n_alternativas ?? 5,
            'estado' => $request->has('estado') ? $request->estado : $examen->estado,
        ]);

        $this->response['estado'] = true;
        $this->response['datos'] = $examen;
        $this->response['mensaje'] = 'Examen actualizado correctamente';
        return response()->json($this->response, 200);
    }

    public function destroy($id)
    {
        $examen = ExamenSimulacro::find($id);
        if (!$examen) {
            return response()->json(['estado' => false, 'mensaje' => 'Examen no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            $tipoIds = ExamenTipo::where('id_examen_simulacro', $id)->pluck('id');
            Excepciones::whereIn('id_examen_tipo', $tipoIds)->delete();
            Resp::whereIn('id_examen_tipo', $tipoIds)->delete();
            ExamenTipo::where('id_examen_simulacro', $id)->delete();
            DB::table('respuestas_simulacro')->where('id_examen', $id)->delete();
            $examen->delete();
            DB::commit();

            $this->response['estado'] = true;
            $this->response['mensaje'] = 'Examen eliminado correctamente';
            return response()->json($this->response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['estado' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // --- TIPOS (examen_tipos) ---

    public function getTipos($id_examen)
    {
        $tipos = ExamenTipo::where('id_examen_simulacro', $id_examen)
            ->with('archivo:id,nombre,url')
            ->withCount('excepciones as excepciones_count')
            ->withCount('respuestas as res_count')
            ->orderBy('id', 'ASC')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $tipos;
        return response()->json($this->response, 200);
    }

    public function saveTipo(Request $request)
    {
        $validator = validator($request->all(), [
            'id_examen_simulacro' => 'required|exists:examen_simulacro,id',
            'tipo' => 'nullable|string|max:1',
            'respuestas' => 'nullable|string|max:90',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $tipo = ExamenTipo::updateOrCreate(
            ['id' => $request->id ?? null],
            [
                'id_examen_simulacro' => $request->id_examen_simulacro,
                'tipo' => $request->tipo,
                'respuestas' => $request->respuestas,
                'estado' => $request->estado ?? true,
            ]
        );

        $this->response['estado'] = true;
        $this->response['datos'] = $tipo;
        $this->response['mensaje'] = $request->id ? 'Tipo actualizado' : 'Tipo creado';
        return response()->json($this->response, 200);
    }

    public function deleteTipo($id)
    {
        $tipo = ExamenTipo::find($id);
        if (!$tipo) {
            return response()->json(['estado' => false, 'mensaje' => 'Tipo no encontrado'], 404);
        }

        DB::beginTransaction();
        try {
            Excepciones::where('id_examen_tipo', $id)->delete();
            Resp::where('id_examen_tipo', $id)->delete();
            $tipo->delete();
            DB::commit();

            $this->response['estado'] = true;
            $this->response['mensaje'] = 'Tipo eliminado correctamente';
            return response()->json($this->response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['estado' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // --- CARGA DE TIPOS DESDE ARCHIVO ---

    public function subirTiposArchivo(Request $request, $id_examen)
    {
        $examen = ExamenSimulacro::find($id_examen);
        if (!$examen) {
            return response()->json(['estado' => false, 'mensaje' => 'Examen no encontrado'], 404);
        }

        $request->validate([
            'file' => 'required|file',
        ]);

        $archivo = $request->file('file');
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $archivo->getClientOriginalName();

        $directorio = 'calificar/examenes/' . $id_examen . '/tipos/';
        $archivo->move(storage_path('app/' . $directorio), $nombreArchivo);
        $rutaCompleta = storage_path('app/' . $directorio . $nombreArchivo);

        $registro = ArchivoSimulacro::create([
            'nombre' => $nombreArchivo,
            'tipo' => $extension,
            'id_simulacro' => $examen->id_simulacro,
            'id_examen_tipo' => null,
            'fecha' => date('Y-m-d'),
            'categoria' => 'tipo',
            'url' => $directorio . $nombreArchivo,
        ]);

        $lineas = file($rutaCompleta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $tiposData = [];

        foreach ($lineas as $linea) {
            $respStr = substr($linea, 46, 60);
            if (strlen($respStr) < 2) continue;
            $tipoRes = substr($respStr, 0, 1);
            $respuestas = substr($respStr, 1);
            if (!isset($tiposData[$tipoRes])) {
                $tiposData[$tipoRes] = $respuestas;
            }
        }

        $count = 0;
        foreach ($tiposData as $tipoRes => $respuestas) {
            ExamenTipo::create([
                'id_examen_simulacro' => $id_examen,
                'id_archivo' => $registro->id,
                'tipo' => $tipoRes,
                'respuestas' => $respuestas,
                'estado' => true,
            ]);
            $count++;
        }

        $this->response['estado'] = true;
        $this->response['mensaje'] = "Tipos creados desde archivo: {$count} tipo(s)";
        return response()->json($this->response, 200);
    }

    public function verArchivo($id_archivo)
    {
        $archivo = ArchivoSimulacro::find($id_archivo);
        if (!$archivo) {
            return response()->json(['estado' => false, 'mensaje' => 'Archivo no encontrado'], 404);
        }

        $ruta = storage_path('app/' . $archivo->url);
        if (!file_exists($ruta)) {
            return response()->json(['estado' => false, 'mensaje' => 'Archivo físico no encontrado'], 404);
        }

        $contenido = file_get_contents($ruta);

        $this->response['estado'] = true;
        $this->response['datos'] = [
            'nombre' => $archivo->nombre,
            'tipo' => $archivo->tipo,
            'contenido' => $contenido,
        ];
        return response()->json($this->response, 200);
    }

    // --- CARGA DE RES ---

    public function subirRes(Request $request, $id_tipo)
    {
        $tipo = ExamenTipo::with('examenSimulacro')->find($id_tipo);
        if (!$tipo) {
            return response()->json(['estado' => false, 'mensaje' => 'Tipo no encontrado'], 404);
        }

        $request->validate([
            'file' => 'required|file',
        ]);

        $archivo = $request->file('file');
        $extension = $archivo->getClientOriginalExtension();
        $nombreArchivo = $archivo->getClientOriginalName();

        $directorio = 'calificar/examenes/' . $tipo->examenSimulacro->id . '/res/';
        $archivo->move(storage_path('app/' . $directorio), $nombreArchivo);
        $rutaCompleta = storage_path('app/' . $directorio . $nombreArchivo);

        $registro = ArchivoSimulacro::create([
            'nombre' => $nombreArchivo,
            'tipo' => $extension,
            'id_simulacro' => $tipo->examenSimulacro->id_simulacro,
            'id_examen_tipo' => $id_tipo,
            'fecha' => date('Y-m-d'),
            'categoria' => 'respuesta',
            'url' => $directorio . $nombreArchivo,
        ]);

        $count = $this->parsearRes($rutaCompleta, $registro->id, $tipo);

        $this->response['estado'] = true;
        $this->response['mensaje'] = "Archivo cargado: $nombreArchivo ($count registros)";
        return response()->json($this->response, 200);
    }

    public function getRes($id_tipo)
    {
        $res = Resp::where('id_examen_tipo', $id_tipo)
            ->select('id', 'litho', 'tipo', 'respuestas', 'puntaje')
            ->orderBy('id', 'DESC')
            ->paginate(15);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function deleteRes($id_tipo)
    {
        Resp::where('id_examen_tipo', $id_tipo)->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Registros RES eliminados';
        return response()->json($this->response, 200);
    }

    /**
     * Parsea un archivo RES de formato fijo.
     * Agrupa por tipo, crea/actualiza ExamenTipos y inserta en la tabla res.
     * Retorna el total de registros insertados.
     */
    private function parsearRes($archivo, $idArchivo, $tipo)
    {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $now = now();
        $grouped = [];

        foreach ($lineas as $linea) {
            $c1       = substr($linea, 0, 3);
            $lectura  = substr($linea, 3, 6);
            $c3       = substr($linea, 9, 3);
            $c4       = substr($linea, 12, 5);
            $c5       = substr($linea, 17, 4);
            $c6       = substr($linea, 24, 4);
            $c7       = trim(substr($linea, 29, 5));
            $c8       = trim(substr($linea, 38, 1));
            $litho    = substr($linea, 40, 6);
            $respStr  = substr($linea, 46, 60);
            $tipoRes  = substr($respStr, 0, 1);
            $respuestas = substr($respStr, 1);

            if (strlen(trim($c1)) > 1) {
                $grouped[$tipoRes][] = [
                    'c1' => $c1,
                    'n_lectura' => $lectura,
                    'c3' => $c3,
                    'c4' => $c4,
                    'c5' => $c5,
                    'c6' => $c6,
                    'c7' => $c7,
                    'c8' => $c8,
                    'litho' => $litho,
                    'tipo' => $tipoRes,
                    'respuestas' => $respuestas,
                    'id_archivo' => $idArchivo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $total = 0;
        $first = true;

        foreach ($grouped as $tipoRes => $records) {
            if ($first) {
                // Actualizar el ExamenTipo original con el tipo y respuestas del primer registro
                $tipo->update([
                    'tipo' => $tipoRes,
                    'respuestas' => $records[0]['respuestas'],
                    'id_archivo' => $idArchivo,
                ]);
                $examenTipoId = $tipo->id;
                $first = false;
            } else {
                // Crear nuevo ExamenTipo para tipos adicionales
                $nuevoTipo = ExamenTipo::create([
                    'id_examen_simulacro' => $tipo->id_examen_simulacro,
                    'id_archivo' => $idArchivo,
                    'tipo' => $tipoRes,
                    'respuestas' => $records[0]['respuestas'],
                    'estado' => true,
                ]);
                $examenTipoId = $nuevoTipo->id;
            }

            // Asignar id_examen_tipo correcto a cada registro e insertar
            $datos = array_map(function ($r) use ($examenTipoId) {
                $r['id_examen_tipo'] = $examenTipoId;
                return $r;
            }, $records);

            Resp::insert($datos);
            $total += count($datos);
        }

        return $total;
    }

    // --- EXCEPCIONES ---

    public function getExcepciones($id_tipo)
    {
        $excepciones = Excepciones::where('id_examen_tipo', $id_tipo)
            ->orderBy('nro_pregunta', 'ASC')
            ->get();

        $this->response['estado'] = true;
        $this->response['datos'] = $excepciones;
        return response()->json($this->response, 200);
    }

    public function saveExcepcion(Request $request)
    {
        $validator = validator($request->all(), [
            'id_examen_tipo' => 'required|exists:examen_tipos,id',
            'nro_pregunta' => 'required|integer|min:1',
            'accion' => 'required|in:todas_validas,multiples_validas,anulada,asignar_puntaje',
            'claves_validas' => 'nullable|string|max:9',
            'observacion' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['estado' => false, 'errores' => $validator->errors()], 422);
        }

        $excepcion = Excepciones::updateOrCreate(
            ['id' => $request->id ?? null],
            [
                'id_examen_tipo' => $request->id_examen_tipo,
                'nro_pregunta' => $request->nro_pregunta,
                'accion' => $request->accion,
                'claves_validas' => $request->claves_validas,
                'observacion' => $request->observacion,
                'cod_examen' => '',
            ]
        );

        $this->response['estado'] = true;
        $this->response['datos'] = $excepcion;
        $this->response['mensaje'] = $request->id ? 'Excepción actualizada' : 'Excepción creada';
        return response()->json($this->response, 200);
    }

    public function deleteExcepcion($id)
    {
        $excepcion = Excepciones::find($id);
        if (!$excepcion) {
            return response()->json(['estado' => false, 'mensaje' => 'Excepción no encontrada'], 404);
        }

        $excepcion->delete();

        $this->response['estado'] = true;
        $this->response['mensaje'] = 'Excepción eliminada correctamente';
        return response()->json($this->response, 200);
    }
}
