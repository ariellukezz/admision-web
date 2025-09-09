<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ResumenInscripcionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle
{
    protected $data;
    protected $group;

    public function __construct($data, $group)
    {
        $this->data = $data;
        $this->group = $group;
    }

    public function collection()
    {
        $collection = collect($this->data);
        $suma = $collection->sum('total');
        $totalColumns = count($this->group);

        $filaTotal = array_fill(0, $totalColumns, '');
        $filaTotal[$totalColumns - 2] = 'TOTAL';
        $filaTotal[$totalColumns - 1] = $suma;

        return $collection->map(function ($item) {
            return (array) $item;
        })->push($filaTotal);
    }

    public function headings(): array
    {
        return array_merge($this->group);
    }

    public function title(): string
    {
        return 'RESUMEN';
    }
}
