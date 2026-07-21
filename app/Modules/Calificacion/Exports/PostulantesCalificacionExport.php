<?php

namespace App\Modules\Calificacion\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PostulantesCalificacionExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($row) {
            return [
                $row->dni,
                $row->paterno,
                $row->materno,
                $row->nombres,
                $row->puntaje,
                $row->vocacional,
                $row->apto,
                $row->obs,
                $row->desprograma,
                $row->idexamen,
                $row->litho,
                $row->numlectura,
                $row->tipo,
                $row->calificar,
                $row->aula,
                $row->respuestas,
                $row->puesto,
                $row->puesto_general,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'dni',
            'paterno',
            'materno',
            'nombres',
            'puntaje',
            'vocacional',
            'apto',
            'obs',
            'desprograma',
            'idexamen',
            'litho',
            'numlectura',
            'tipo',
            'calificar',
            'aula',
            'respuestas',
            'puesto',
            'puesto_general',
        ];
    }
}
