<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PuntajePlantillaExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                '2026-07-21',
                '12345678',
                'GARCIA',
                'LOPEZ',
                'JUAN CARLOS',
                85.500,
                null,
                'SI',
                'Derecho',
                'SOCIALES',
                'CEPREUNA',
                1,
                null,
                1,
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'fecha',
            'dni',
            'paterno',
            'materno',
            'nombres',
            'puntaje',
            'puntaje_vocacional',
            'apto',
            'programa',
            'area',
            'modalidad',
            'id_proceso',
            'id_inscripcion',
            'puesto',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '096dd9']],
            ],
        ];
    }
}
