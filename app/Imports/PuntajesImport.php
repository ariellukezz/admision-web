<?php

namespace App\Imports;

use App\Models\Puntaje;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PuntajesImport implements ToCollection, WithHeadingRow
{
    protected int $idProceso;
    protected int $total = 0;
    protected int $insertados = 0;
    protected int $actualizados = 0;
    protected int $noEncontrados = 0;

    public function __construct(int $idProceso)
    {
        $this->idProceso = $idProceso;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $dni = trim($row['dni'] ?? '');
            if (!$dni) continue;

            $this->total++;

            // Buscar inscripción del postulante en el proceso seleccionado con estado = 0
            $inscripcion = DB::table('inscripciones as i')
                ->join('postulante as p', 'p.id', '=', 'i.id_postulante')
                ->leftJoin('programa as prog', 'prog.id', '=', 'i.id_programa')
                ->leftJoin('modalidad as mod', 'mod.id', '=', 'i.id_modalidad')
                ->where('p.nro_doc', $dni)
                ->where('i.id_proceso', $this->idProceso)
                ->where('i.estado', 0)
                ->select('i.id', 'prog.nombre as programa', 'prog.area', 'mod.nombre as modalidad')
                ->first();

            $idInscripcion = $inscripcion?->id;

            if (!$idInscripcion) {
                $this->noEncontrados++;
            }

            $data = [
                'fecha'              => $row['fecha'] ?? null,
                'dni'                => $dni,
                'paterno'            => trim($row['paterno'] ?? ''),
                'materno'            => trim($row['materno'] ?? ''),
                'nombres'            => trim($row['nombres'] ?? ''),
                'puntaje'            => $row['puntaje'] ?? null,
                'puntaje_vocacional' => $row['puntaje_vocacional'] ?? null,
                'apto'               => strtoupper(trim($row['apto'] ?? '')),
                'programa'           => $inscripcion?->programa,
                'area'               => $inscripcion?->area,
                'modalidad'          => $inscripcion?->modalidad,
                'id_proceso'         => $this->idProceso,
                'id_inscripcion'     => $idInscripcion,
                'puesto'             => $row['puesto'] ?? null,
            ];

            $existente = Puntaje::where('dni', $dni)
                ->where('id_proceso', $this->idProceso)
                ->first();

            if ($existente) {
                $existente->update($data);
                $this->actualizados++;
            } else {
                Puntaje::create($data);
                $this->insertados++;
            }
        }
    }

    public function getTotal(): int { return $this->total; }
    public function getInsertados(): int { return $this->insertados; }
    public function getActualizados(): int { return $this->actualizados; }
    public function getNoEncontrados(): int { return $this->noEncontrados; }
}
