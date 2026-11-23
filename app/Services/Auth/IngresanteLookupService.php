<?php

namespace App\Services\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IngresanteLookupService
{
    /**
     * @param array<int, string> $numerosDocumento
     * @return Collection<int, object>
     */
    public function buscarIngresantes(int $idProceso, array $numerosDocumento): Collection
    {
        return DB::table('resultados as res')
            ->join('procesos as pro', 'res.id_proceso', '=', 'pro.id')
            ->where('res.id_proceso', $idProceso)
            ->where('res.apto', 'SI')
            ->whereIn('res.dni_postulante', $numerosDocumento)
            ->select('res.dni_postulante as nro_doc', 'pro.nombre')
            ->selectRaw("'INGRESANTE' AS estado")
            ->orderBy('pro.nombre', 'asc')
            ->get();
    }
}
