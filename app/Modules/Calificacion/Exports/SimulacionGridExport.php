<?php

namespace App\Modules\Calificacion\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimulacionGridExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $aulas;

    public function __construct(array $aulas)
    {
        $this->aulas = $aulas;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->aulas as $aula) {
            // Fila separadora con el nombre del aula
            $rows[] = [
                $aula['nombre'] ?? '',
                $aula['area'] ?? '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];

            foreach ($aula['assignments'] ?? [] as $a) {
                $rows[] = [
                    $aula['nombre'] ?? '',
                    $aula['area'] ?? '',
                    $a['posicion'] ?? '',
                    $a['codigo'] ?? '',
                    $a['nombre'] ?? '',
                    $a['n_documento'] ?? '',
                    $a['programa'] ?? '',
                    $a['tipo_examen'] ?? '',
                ];
            }

            // Fila vacía separadora
            $rows[] = ['', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Aula',
            'Área',
            'Posición',
            'Código',
            'Postulante',
            'N° Documento',
            'Programa',
            'Tipo Examen',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '1e293b']]],
        ];
    }
}
