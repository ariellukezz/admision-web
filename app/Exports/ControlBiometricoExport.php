<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ControlBiometricoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'CÓDIGO',
            'DNI',
            'PATERNO',
            'MATERNO',
            'NOMBRES',
            'PUNTAJE',
            'PUESTO',
            'PROGRAMA',
            'MODALIDAD',
            'ÁREA',
            'PROCESO',
        ];
    }

    public function title(): string
    {
        return 'CONTROL BIOMÉTRICO';
    }
}
