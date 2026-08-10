<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteSuneduExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $datos;
    protected bool $incluirIdentidad;

    public function __construct(Collection $datos, bool $incluirIdentidad = false)
    {
        $this->datos = $datos;
        $this->incluirIdentidad = $incluirIdentidad;
    }

    public function collection()
    {
        return $this->datos;
    }

    public function headings(): array
    {
        $base = [
            'CODIGO_SEDE_FILIAL',
            'TIPO_PROCESO',
            'PROCESO_ADMISION',
            'NUMERO_CONVOCATORIA',
            'TIPO_DOCUMENTO',
            'NRO_DOCUMENTO',
            'NOMBRES',
            'PRIMER_APELLIDO',
            'SEGUNDO_APELLIDO',
            'APELLIDO_CASADA',
            'SOLO_UN_APELLIDO',
            'SEXO',
            'FECHA_NACIMIENTO',
            'PAIS_NACIMIENTO',
            'NACIONALIDAD',
            'UBIGEO_NACIMIENTO',
            'UBIGEO_DOMICILIO',
            'CONDICION_DISCAPACIDAD',
            'TIPO_DISCAPACIDAD',
            'CELULAR',
            'CORREO_PERSONAL',
            'CODIGO_FACULTAD_UNIDAD',
            'CODIGO_PROGRAMA_OPCIONES',
            'FECHA_REGISTRO',
            'PUNTAJE_OBTENIDO',
            'MODALIDAD_ADMISION',
            'MODALIDAD_ESTUDIO',
            'ES_INGRESANTE',
            'CODIGO_FACULTAD_UNIDAD_INGRESO',
            'CODIGO_PROGRAMA_INGRESO',
            'FECHA_INGRESO',
            'CORREO_INSTITUCIONAL',
            'CODIGO_ORCID',
        ];

        if ($this->incluirIdentidad) {
            $base[] = 'VALIDACION_CELULAR';
            $base[] = 'VALIDACION_EMAIL';
            $base[] = 'ID_IDENTIDAD_CULTURAL';
            $base[] = 'ID_PUEBLO_INDIGENA';
            $base[] = 'ID_LENGUA_INDIGENA';
            $base[] = 'ID_CONDICION_LENGUA';
            $base[] = 'ID_PERTENENCIA_CULTURAL';
            $base[] = 'FECHA_REGISTRO_IDENTIDAD';
            $base[] = 'FECHA_ACTUALIZACION_IDENTIDAD';
        }

        return $base;
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
