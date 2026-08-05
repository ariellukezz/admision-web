<?php

namespace App\Modules\Calificacion\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimulacionMapeoExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $mapeo;

    public function __construct(array $mapeo)
    {
        $this->mapeo = $mapeo;
    }

    public function array(): array
    {
        $ordinalPiso = function ($p) {
            $n = (int) $p;
            if ($n === 1) return '1er';
            if ($n === 2) return '2do';
            if ($n === 3) return '3er';
            return $n . 'to';
        };

        return array_map(function ($m) use ($ordinalPiso) {
            return [
                $m['aula_virtual'] ?? '',
                $m['area_virtual'] ?? ($m['area'] ?? ''),
                $ordinalPiso($m['piso'] ?? 1),
                $m['estudiantes'] ?? 0,
                $m['capacidad'] ?? 0,
                $m['ambiente_nombre'] ?? '',
                $m['aula_fisica'] ?? '',
            ];
        }, $this->mapeo);
    }

    public function headings(): array
    {
        return [
            'Aula',
            'Área',
            'Piso',
            'Postulantes',
            'Capacidad',
            'Ambiente',
            'Aula Física',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '1e293b']]],
        ];
    }
}
