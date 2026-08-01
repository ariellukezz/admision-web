<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class SorteoSeleccionadoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'DNI',
            'PATERNO',
            'MATERNO',
            'NOMBRES',
            'TIPO PERSONAL',
            'CARGO',
            'ORIGEN',
            'CONDICIÓN',
            'DEPENDENCIA',
            'OBSERVACIÓN',
            'ESTADO',
        ];
    }

    public function title(): string
    {
        return 'LISTA DE SELECCIONADOS';
    }
}
