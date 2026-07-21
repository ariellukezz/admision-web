<?php

namespace App\Imports;

use App\Models\Puntaje;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PuntajesImport implements ToCollection, WithHeadingRow
{
    protected int $idProceso;
    protected int $total = 0;
    protected int $insertados = 0;
    protected int $actualizados = 0;

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

            $data = [
                'fecha'              => $row['fecha'] ?? null,
                'dni'                => $dni,
                'paterno'            => trim($row['paterno'] ?? ''),
                'materno'            => trim($row['materno'] ?? ''),
                'nombres'            => trim($row['nombres'] ?? ''),
                'puntaje'            => $row['puntaje'] ?? null,
                'puntaje_vocacional' => $row['puntaje_vocacional'] ?? null,
                'apto'               => strtoupper(trim($row['apto'] ?? '')),
                'programa'           => trim($row['programa'] ?? ''),
                'area'               => trim($row['area'] ?? ''),
                'modalidad'          => trim($row['modalidad'] ?? ''),
                'id_proceso'         => $this->idProceso,
                'id_inscripcion'     => $row['id_inscripcion'] ?? null,
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
}
