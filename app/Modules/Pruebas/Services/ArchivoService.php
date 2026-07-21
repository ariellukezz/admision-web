<?php

namespace App\Modules\Pruebas\Services;

use App\Modules\Pruebas\Models\PruebaArchivo;
use App\Modules\Pruebas\Models\PruebaIde;
use App\Modules\Pruebas\Models\PruebaRes;
use App\Modules\Pruebas\Models\PruebaTipo;
use App\Modules\Pruebas\Models\PruebaExcepcion;
use Illuminate\Support\Facades\DB;

class ArchivoService
{
    private const DIR_BASE = 'calificar/pruebas/';

    public function cargarArchivo(int $idPrueba, $file, string $tipo): array
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['txt', 'dat'])) {
            throw new \DomainException('El archivo debe ser de tipo txt o dat');
        }

        $nombreArchivo = $file->getClientOriginalName();
        $directorio = self::DIR_BASE . $idPrueba . '/' . $tipo . '/';
        $file->move(storage_path('app/' . $directorio), $nombreArchivo);

        $archivo = PruebaArchivo::create([
            'id_prueba' => $idPrueba,
            'nombre' => $nombreArchivo,
            'tipo' => $tipo,
            'url' => $directorio . $nombreArchivo,
        ]);

        $rutaCompleta = storage_path('app/' . $archivo->url);
        $registros = match ($tipo) {
            'ide' => $this->parsearIde($rutaCompleta, $idPrueba, $archivo->id),
            'res' => $this->parsearRes($rutaCompleta, $idPrueba, $archivo->id),
            'tipos' => $this->parsearTipos($rutaCompleta, $idPrueba, $archivo->id),
        };

        return [
            'nombre' => $nombreArchivo,
            'tipo' => $tipo,
            'registros' => $registros,
        ];
    }

    public function getArchivos(int $idPrueba, ?string $tipo = null): array
    {
        $query = PruebaArchivo::where('id_prueba', $idPrueba)
            ->select('id', 'nombre', 'tipo', 'url', 'estado', 'created_at');

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->orderBy('id', 'DESC')->get()->map(function ($a) {
            $count = match ($a->tipo) {
                'ide' => PruebaIde::where('id_archivo', $a->id)->count(),
                'res' => PruebaRes::where('id_archivo', $a->id)->count(),
                'tipos' => PruebaTipo::where('id_archivo', $a->id)->count(),
                default => 0,
            };

            return [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'tipo' => $a->tipo,
                'fecha' => $a->created_at?->format('Y-m-d'),
                'registros' => $count,
                'estado' => $a->estado,
            ];
        })->toArray();
    }

    public function getRegistros(int $idArchivo): array
    {
        $archivo = PruebaArchivo::find($idArchivo);
        if (!$archivo) {
            throw new \DomainException('Archivo no encontrado');
        }

        return match ($archivo->tipo) {
            'ide' => $this->getIdes($idArchivo),
            'res' => $this->getRespuestas($idArchivo),
            'tipos' => PruebaTipo::where('id_archivo', $idArchivo)
                ->select('id', 'tipo', 'respuestas')
                ->orderBy('tipo')->get()->toArray(),
            default => [],
        };
    }

    public function getTiposByPrueba(int $idPrueba): array
    {
        return PruebaTipo::where('id_prueba', $idPrueba)
            ->withCount('excepciones')
            ->select('id', 'tipo', 'respuestas')
            ->orderBy('tipo')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'tipo' => $t->tipo,
                'respuestas' => $t->respuestas,
                'excepciones_count' => $t->excepciones_count,
            ])
            ->toArray();
    }

    public function destroyArchivo(int $id): void
    {
        $archivo = PruebaArchivo::find($id);
        if (!$archivo) {
            throw new \DomainException('Archivo no encontrado');
        }

        DB::beginTransaction();
        try {
            match ($archivo->tipo) {
                'ide' => PruebaIde::where('id_archivo', $id)->delete(),
                'res' => PruebaRes::where('id_archivo', $id)->delete(),
                'tipos' => PruebaTipo::where('id_archivo', $id)->delete(),
                default => null,
            };
            $archivo->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarIde(array $data): PruebaIde
    {
        $ide = PruebaIde::find($data['id']);
        if (!$ide) {
            throw new \DomainException('IDE no encontrado');
        }

        $ide->dni = $data['dni'];
        $ide->tipo = $data['tipo'] ?? $ide->tipo;
        $ide->aula = $data['aula'] ?? $ide->aula;
        $ide->estado = $data['estado'] ?? $ide->estado;
        $ide->save();

        return $ide;
    }

    private function parsearIde(string $ruta, int $idPrueba, int $idArchivo): int
    {
        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $datos = [];

        foreach ($lineas as $linea) {
            $campo1 = substr($linea, 0, 21);
            $campo2 = substr($linea, 3, 6);
            $campo3 = substr($linea, 24, 5);
            $campo4 = substr($linea, 38, 1);
            $campo5 = substr($linea, 40);

            $litho = substr($campo5, 0, 6);
            $tipo = substr($campo5, 6, 1);
            $dni = substr($campo5, 7, 8);
            $aula = substr($campo5, 15, 3);

            if (strlen($campo1) > 1) {
                $datos[] = [
                    'id_prueba' => $idPrueba,
                    'id_archivo' => $idArchivo,
                    'camp1' => $campo1,
                    'camp2' => $campo2,
                    'camp3' => $campo3,
                    'camp4' => $campo4,
                    'litho' => $litho,
                    'tipo' => $tipo,
                    'dni' => $dni,
                    'aula' => $aula,
                ];
            }
        }

        PruebaIde::insert($datos);
        return count($datos);
    }

    private function parsearRes(string $ruta, int $idPrueba, int $idArchivo): int
    {
        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $now = now();
        $datos = [];

        foreach ($lineas as $linea) {
            $c1 = substr($linea, 0, 3);
            $nLectura = substr($linea, 3, 6);
            $c3 = substr($linea, 9, 3);
            $litho = substr($linea, 40, 6);
            $respuestas = substr($linea, 46, 60);

            if (strlen(trim($c1)) > 1) {
                $datos[] = [
                    'id_prueba' => $idPrueba,
                    'id_archivo' => $idArchivo,
                    'n_lectura' => $nLectura,
                    'c1' => $c1,
                    'c3' => $c3,
                    'litho' => $litho,
                    'respuestas' => $respuestas,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        PruebaRes::insert($datos);
        return count($datos);
    }

    private function parsearTipos(string $ruta, int $idPrueba, int $idArchivo): int
    {
        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $tiposData = [];

        foreach ($lineas as $linea) {
            if (strlen($linea) < 47) continue;

            $tipoRes = substr($linea, 46, 1);
            $respuestas = str_pad(substr($linea, 47, 60), 60, ' ');

            if (!isset($tiposData[$tipoRes])) {
                $tiposData[$tipoRes] = $respuestas;
            }
        }

        $count = 0;
        foreach ($tiposData as $tipoRes => $respuestas) {
            PruebaTipo::updateOrCreate(
                ['id_prueba' => $idPrueba, 'tipo' => $tipoRes],
                ['id_archivo' => $idArchivo, 'respuestas' => $respuestas]
            );
            $count++;
        }

        return $count;
    }

    private function getIdes(int $idArchivo): array
    {
        return PruebaIde::where('id_archivo', $idArchivo)
            ->select('id', 'camp2', 'dni', 'aula', 'tipo', 'litho', 'estado')
            ->orderBy('id')
            ->get()
            ->map(function ($ide) {
                $obs = [];
                if (!$ide->dni) $obs[] = 'Sin DNI';
                if ($ide->aula === '' || $ide->aula === null) $obs[] = 'Sin aula';
                if ($ide->dni && strlen($ide->dni) !== 8) $obs[] = 'DNI erroneo';
                if ($ide->tipo === '' || $ide->tipo === null) $obs[] = 'Sin tipo';
                if ($ide->estado !== 1) $obs[] = 'No se calificará';
                return [
                    'id' => $ide->id,
                    'camp2' => $ide->camp2,
                    'dni' => $ide->dni,
                    'aula' => $ide->aula,
                    'tipo' => $ide->tipo,
                    'litho' => $ide->litho,
                    'estado' => $ide->estado,
                    'observaciones' => $obs,
                ];
            })
            ->toArray();
    }

    private function getRespuestas(int $idArchivo): array
    {
        return PruebaRes::where('id_archivo', $idArchivo)
            ->select('id', 'n_lectura', 'litho', 'respuestas', 'puntaje', 'calificado')
            ->orderBy('id')
            ->get()
            ->toArray();
    }
}
