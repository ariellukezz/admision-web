<?php

namespace App\Modules\Calificacion\Services;

use App\Modules\Calificacion\Models\ArchivoLectura;
use App\Modules\Calificacion\Models\ArchivoSimulacro;
use App\Modules\Calificacion\Models\Ide;
use App\Modules\Calificacion\Models\Resp;
use Illuminate\Support\Facades\DB;

class FileParsingService
{
    public function cargarArchivoIde($file, int $idCalificacion, string $area): array
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['txt', 'dat'])) {
            throw new \DomainException('El archivo debe ser de tipo txt o dat');
        }

        $nombreArchivo = $file->getClientOriginalName();
        $file->move(storage_path('app/calificar/lecturas/' . $idCalificacion . '/ides/'), $nombreArchivo);

        $archivo = ArchivoLectura::create([
            'id_calificacion' => $idCalificacion,
            'nombre' => $nombreArchivo,
            'tipo' => 'ide',
            'area' => $area,
            'url' => "calificar/lecturas/$idCalificacion/ides/$nombreArchivo",
        ]);

        $this->parsearIde(storage_path('app/' . $archivo->url), $archivo->id);

        return ['nombre' => $nombreArchivo];
    }

    public function cargarArchivoRes($file, int $idCalificacion, ?string $area = null): array
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['txt', 'dat'])) {
            throw new \DomainException('El archivo debe ser de tipo txt o dat');
        }

        $nombreArchivo = $file->getClientOriginalName();
        $file->move(storage_path('app/calificar/lecturas/' . $idCalificacion . '/res/'), $nombreArchivo);

        $archivo = ArchivoLectura::create([
            'id_calificacion' => $idCalificacion,
            'nombre' => $nombreArchivo,
            'tipo' => 'res',
            'area' => $area,
            'url' => "calificar/lecturas/$idCalificacion/res/$nombreArchivo",
        ]);

        $this->parsearRes(storage_path('app/' . $archivo->url), $archivo->id);

        return ['nombre' => $nombreArchivo];
    }

    public function cargarArchivoPat($file, int $proceso, ?string $codExamen = null): array
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['txt', 'dat'])) {
            throw new \DomainException('El archivo debe ser de tipo txt o dat');
        }

        $nombreArchivo = $file->getClientOriginalName();
        $file->move(storage_path('app/calificar/' . $proceso . '/patron/'), $nombreArchivo);

        $archivo = ArchivoSimulacro::create([
            'nombre' => $nombreArchivo,
            'tipo' => $extension,
            'id_simulacro' => $proceso,
            'cod_examen' => $codExamen,
            'fecha' => date('Y-m-d'),
            'categoria' => 'patron',
            'url' => "app/calificar/$proceso/patron/$nombreArchivo",
        ]);

        $this->parsearPat(storage_path($archivo->url), $archivo->id);

        return ['nombre' => $nombreArchivo];
    }

    public function actualizarIde(array $data): Ide
    {
        $ide = Ide::find($data['id']);
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

    private function parsearIde(string $archivo, int $id): void
    {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                    'camp1' => $campo1,
                    'camp2' => $campo2,
                    'camp3' => $campo3,
                    'camp4' => $campo4,
                    'litho' => $litho,
                    'tipo' => $tipo,
                    'dni' => $dni,
                    'aula' => $aula,
                    'id_archivo' => $id,
                ];
            }
        }

        Ide::insert($datos);
    }

    private function parsearRes(string $archivo, int $id): void
    {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $now = now();
        $datos = [];

        foreach ($lineas as $linea) {
            $c1 = substr($linea, 0, 3);
            $lectura = substr($linea, 3, 6);
            $c3 = substr($linea, 9, 3);
            $c4 = substr($linea, 12, 5);
            $c5 = substr($linea, 17, 4);
            $c6 = substr($linea, 24, 4);
            $c7 = trim(substr($linea, 29, 5));
            $c8 = trim(substr($linea, 38, 1));
            $litho = substr($linea, 40, 6);
            $respuestas = substr($linea, 46, 60);

            if (strlen(trim($c1)) > 1) {
                $datos[] = [
                    'c1' => $c1, 'n_lectura' => $lectura, 'c3' => $c3, 'c4' => $c4,
                    'c5' => $c5, 'c6' => $c6, 'c7' => $c7, 'c8' => $c8,
                    'litho' => $litho, 'tipo' => null, 'respuestas' => $respuestas,
                    'id_archivo' => $id, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
        }

        Resp::insert($datos);
    }

    private function parsearPat(string $archivo, int $id): void
    {
        $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $datos = [];

        foreach ($lineas as $linea) {
            $c1 = substr($linea, 0, 3);
            $lectura = substr($linea, 3, 6);
            $c3 = substr($linea, 9, 3);
            $c4 = substr($linea, 12, 5);
            $c5 = substr($linea, 17, 4);
            $c6 = substr($linea, 24, 4);
            $c7 = trim(substr($linea, 29, 5));
            $c8 = trim(substr($linea, 38, 1));
            $litho = substr($linea, 40, 6);
            $respuestas = substr($linea, 46, 60);

            if (strlen(trim($c1)) > 1) {
                $datos[] = [
                    'c1' => $c1, 'n_lectura' => $lectura, 'c3' => $c3, 'c4' => $c4,
                    'c5' => $c5, 'c6' => $c6, 'c7' => $c7, 'c8' => $c8,
                    'litho' => $litho, 'tipo' => null, 'respuestas' => $respuestas,
                    'id_archivo' => $id,
                ];
            }
        }

        Resp::insert($datos);
    }
}
