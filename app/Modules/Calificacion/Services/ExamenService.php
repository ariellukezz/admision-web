<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\ArchivoSimulacro;
use App\Modules\Calificacion\Models\ExamenSimulacro;
use App\Modules\Calificacion\Models\ExamenTipo;
use App\Modules\Calificacion\Models\Excepciones;
use App\Modules\Calificacion\Models\Resp;
use Illuminate\Support\Facades\DB;

class ExamenService
{
    public function index(?string $term = null, int $perPage = 10, ?int $idArea = null)
    {
        return ExamenSimulacro::select(
            'examen_simulacro.id', 'examen_simulacro.tipo', 'examen_simulacro.id_area',
            'examen_simulacro.area', 'examen_simulacro.n_preguntas', 'examen_simulacro.n_alternativas',
            'examen_simulacro.estado'
        )
            ->withCount('tipos as tipos_count')
            ->when($term, fn($q) => $q->where('examen_simulacro.area', 'LIKE', "%{$term}%"))
            ->when($idArea, fn($q) => $q->where('examen_simulacro.id_area', $idArea))
            ->orderBy('examen_simulacro.id', 'DESC')
            ->paginate($perPage);
    }

    public function store(array $data): ExamenSimulacro
    {
        $examen = ExamenSimulacro::create([
            'tipo' => $data['tipo'] ?? null,
            'id_area' => $data['id_area'] ?? null,
            'area' => $data['area'],
            'n_preguntas' => $data['n_preguntas'] ?? 60,
            'n_alternativas' => $data['n_alternativas'] ?? 5,
            'estado' => $data['estado'] ?? 1,
        ]);

        ExamenTipo::create([
            'id_examen_simulacro' => $examen->id,
            'tipo' => null,
            'respuestas' => null,
            'estado' => true,
        ]);

        return $examen;
    }

    public function update(int $id, array $data): ExamenSimulacro
    {
        $examen = ExamenSimulacro::find($id);
        if (!$examen) {
            throw new \DomainException('Examen no encontrado');
        }

        $examen->update([
            'tipo' => $data['tipo'] ?? $examen->tipo,
            'id_area' => $data['id_area'] ?? $examen->id_area,
            'area' => $data['area'],
            'n_preguntas' => $data['n_preguntas'] ?? 60,
            'n_alternativas' => $data['n_alternativas'] ?? 5,
            'estado' => $data['estado'] ?? $examen->estado,
        ]);

        return $examen;
    }

    public function destroy(int $id): void
    {
        $examen = ExamenSimulacro::find($id);
        if (!$examen) {
            throw new \DomainException('Examen no encontrado');
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
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getTipos(int $idExamen)
    {
        return ExamenTipo::where('id_examen_simulacro', $idExamen)
            ->with('archivo:id,nombre,url')
            ->withCount('excepciones as excepciones_count')
            ->withCount('respuestas as res_count')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function saveTipo(array $data): ExamenTipo
    {
        return ExamenTipo::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'id_examen_simulacro' => $data['id_examen_simulacro'],
                'tipo' => $data['tipo'] ?? null,
                'respuestas' => $data['respuestas'] ?? null,
                'estado' => $data['estado'] ?? true,
            ]
        );
    }

    public function deleteTipo(int $id): void
    {
        $tipo = ExamenTipo::find($id);
        if (!$tipo) {
            throw new \DomainException('Tipo no encontrado');
        }

        DB::beginTransaction();
        try {
            Excepciones::where('id_examen_tipo', $id)->delete();
            Resp::where('id_examen_tipo', $id)->delete();
            $tipo->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function uploadTiposArchivo(int $idExamen, $file): int
    {
        $examen = ExamenSimulacro::find($idExamen);
        if (!$examen) {
            throw new \DomainException('Examen no encontrado');
        }

        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = $file->getClientOriginalName();

        $directorio = 'calificar/examenes/' . $idExamen . '/tipos/';
        $file->move(storage_path('app/' . $directorio), $nombreArchivo);
        $rutaCompleta = storage_path('app/' . $directorio . $nombreArchivo);

        $registro = ArchivoSimulacro::create([
            'nombre' => $nombreArchivo,
            'tipo' => $extension,
            'id_simulacro' => $examen->id_area,
            'id_examen_tipo' => null,
            'fecha' => date('Y-m-d'),
            'categoria' => 'tipo',
            'url' => $directorio . $nombreArchivo,
        ]);

        $lineas = file($rutaCompleta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $tiposData = [];

        foreach ($lineas as $linea) {
            if (strlen($linea) < 47) continue;
            $tipoRes = substr($linea, 46, 1);
            $respuestas = substr($linea, 47, 60);
            // Pad con espacios si la línea terminó antes de 60
            $respuestas = str_pad($respuestas, 60, ' ');
            if (!isset($tiposData[$tipoRes])) {
                $tiposData[$tipoRes] = $respuestas;
            }
        }

        $count = 0;
        foreach ($tiposData as $tipoRes => $respuestas) {
            ExamenTipo::create([
                'id_examen_simulacro' => $idExamen,
                'id_archivo' => $registro->id,
                'tipo' => $tipoRes,
                'respuestas' => $respuestas,
                'estado' => true,
            ]);
            $count++;
        }

        return $count;
    }

    public function verArchivo(int $idArchivo): array
    {
        $archivo = ArchivoSimulacro::find($idArchivo);
        if (!$archivo) {
            throw new \DomainException('Archivo no encontrado');
        }

        $ruta = storage_path('app/' . $archivo->url);
        if (!file_exists($ruta)) {
            throw new \DomainException('Archivo físico no encontrado');
        }

        return [
            'nombre' => $archivo->nombre,
            'tipo' => $archivo->tipo,
            'contenido' => file_get_contents($ruta),
        ];
    }

    public function getRes(int $idTipo, int $perPage = 15)
    {
        return Resp::where('id_examen_tipo', $idTipo)
            ->select('id', 'litho', 'tipo', 'respuestas', 'puntaje')
            ->orderBy('id', 'DESC')
            ->paginate($perPage);
    }

    public function getExcepciones(int $idTipo)
    {
        return Excepciones::where('id_examen_tipo', $idTipo)
            ->orderBy('nro_pregunta', 'ASC')
            ->get();
    }

    public function storeRes(int $idTipo, $file): array
    {
        $tipo = ExamenTipo::find($idTipo);
        if (!$tipo) {
            throw new \DomainException('Tipo no encontrado');
        }

        $extension = $file->getClientOriginalExtension();
        $nombreArchivo = $file->getClientOriginalName();

        $directorio = 'calificar/examenes/tipos/' . $idTipo . '/res/';
        $file->move(storage_path('app/' . $directorio), $nombreArchivo);

        $archivo = ArchivoSimulacro::create([
            'nombre' => $nombreArchivo,
            'tipo' => $extension,
            'id_simulacro' => $tipo->examenSimulacro->id_area ?? null,
            'id_examen_tipo' => $idTipo,
            'fecha' => date('Y-m-d'),
            'categoria' => 'respuesta',
            'url' => $directorio . $nombreArchivo,
        ]);

        $rutaCompleta = storage_path('app/' . $directorio . $nombreArchivo);
        $lineas = file($rutaCompleta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $now = now();
        $datos = [];

        foreach ($lineas as $linea) {
            $litho = substr($linea, 40, 6);
            $respuestas = substr($linea, 46, 60);

            if (strlen(trim($litho)) > 1) {
                $datos[] = [
                    'litho' => $litho,
                    'tipo' => $tipo->tipo,
                    'respuestas' => $respuestas,
                    'id_archivo' => $archivo->id,
                    'id_examen_tipo' => $idTipo,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Resp::insert($datos);

        return ['nombre' => $nombreArchivo, 'registros' => count($datos)];
    }

    public function destroyRes(int $idTipo): void
    {
        $tipo = ExamenTipo::find($idTipo);
        if (!$tipo) {
            throw new \DomainException('Tipo no encontrado');
        }

        Resp::where('id_examen_tipo', $idTipo)->delete();
    }

    public function storeExcepcion(array $data): Excepciones
    {
        return Excepciones::create([
            'id_examen_tipo' => $data['id_examen_tipo'],
            'nro_pregunta' => $data['nro_pregunta'],
            'accion' => $data['accion'],
            'claves_validas' => $data['claves_validas'] ?? null,
            'puntaje' => $data['puntaje'] ?? 0,
            'observacion' => $data['observacion'] ?? null,
            'tipo' => $data['tipo'] ?? null
        ]);
    }

    public function destroyExcepcion(int $id): void
    {
        $excepcion = Excepciones::find($id);
        if (!$excepcion) {
            throw new \DomainException('Excepción no encontrada');
        }

        $excepcion->delete();
    }
}
