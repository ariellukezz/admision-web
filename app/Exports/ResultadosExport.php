<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ResultadosExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
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
            'COD_PLAZA',
            'PLAZA',
            'DEPENDENCIA',
            'AULA',
            'LITHO_IDE',
            'LECTURA_NRO_IDE',
            'COD_EXAMEN',
            'LITHO_RES',
            'LECTURA_NRO_RES',
            'RESPUESTAS',
            'PUNTAJE'
        ];
    }

    public function title(): string
    {
        return 'LISTA DE PUNTAJES';
    }
}