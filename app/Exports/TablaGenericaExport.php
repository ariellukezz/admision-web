<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TablaGenericaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $datos;
    protected array $headings;

    public function __construct(Collection $datos, array $headings)
    {
        $this->datos = $datos;
        $this->headings = $headings;
    }

    public function collection()
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return $this->headings;
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
