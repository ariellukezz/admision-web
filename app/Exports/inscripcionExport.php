<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class InscripcionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
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
            'FECHA DE INSCRIPCIÓN',
            'COD PROGRAMA',
            'PROGRAMA DE ESTUDIOS',
            'AREA',
            'COD MODALIDAD',
            'MODALIDAD',
            'N° DOCUMENTO',
            'PATERNO',
            'MATERNO',
            'NOMBRES',
            'SEXO',
            'FEC NACIMIENTO',
            'EDAD',
            'UBIGEO RESIDENCIA',
            'DEPARTAMENTO RESIDENCIA',
            'PROVINCIA RESIDENCIA',
            'DISTRITO RESIDENCIA',
            'EGRESO',
            'COD MODULAR',
            'UBIGEO COLEGIO',
            'DEPARTAMENTO COLEGIO',
            'PROVINCIA COLEGIO',
            'DISTRITO COLEGIO'
        ];
    }


    public function title(): string
    {

        return 'LISTA DE POSTULANTES';
    }

}
