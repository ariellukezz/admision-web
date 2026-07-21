<?php

namespace App\Modules\Pruebas\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PruebaPostulantesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($row) {
            return [
                $row['dni'] ?? '',
                $row['paterno'] ?? '',
                $row['materno'] ?? '',
                $row['nombres'] ?? '',
                $row['litho'] ?? '',
                $row['tipo'] ?? '',
                $row['aula'] ?? '',
                $row['respuestas'] ?? '',
                $row['puntaje'] ?? 0,
                $row['calificado'] ?? 'NO',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'DNI',
            'Apellido Paterno',
            'Apellido Materno',
            'Nombres',
            'Litho',
            'Tipo',
            'Aula',
            'Respuestas',
            'Puntaje',
            'Calificado',
        ];
    }
}
