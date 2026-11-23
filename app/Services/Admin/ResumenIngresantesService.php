<?php

namespace App\Services\Admin;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResumenIngresantesService
{
    private const DIMENSIONES = [
        'area' => 'pro.area',
        'programa' => 'pro.nombre',
        'modalidad' => 'moda.nombre',
        'sexo' => "CASE WHEN pos.sexo = 1 THEN 'M' ELSE 'F' END",
    ];

    /**
     * @param array<int, string> $dimensiones
     * @return Collection<int, object>
     */
    public function porDimensiones(int $idProceso, array $dimensiones): Collection
    {
        $query = $this->consultaBase($idProceso);

        foreach ($dimensiones as $dimension) {
            $expresion = self::DIMENSIONES[$dimension];
            $query->selectRaw("$expresion AS $dimension")
                ->groupByRaw($expresion);
        }

        return $query
            ->selectRaw('COUNT(DISTINCT res.id) AS total')
            ->orderBy($dimensiones[0], 'asc')
            ->get();
    }

    public function total(int $idProceso): int
    {
        return DB::table('resultados')
            ->where('id_proceso', $idProceso)
            ->where('apto', 'SI')
            ->count();
    }

    private function consultaBase(int $idProceso)
    {
        return DB::table('resultados as res')
            ->join('postulante as pos', 'pos.nro_doc', '=', 'res.dni_postulante')
            ->join('inscripciones as ins', function (JoinClause $join) {
                $join->on('ins.id_postulante', '=', 'pos.id')
                    ->on('ins.id_proceso', '=', 'res.id_proceso')
                    ->where('ins.estado', 0);
            })
            ->join('programa as pro', 'pro.id', '=', 'ins.id_programa')
            ->join('modalidad as moda', 'moda.id', '=', 'ins.id_modalidad')
            ->where('res.id_proceso', $idProceso)
            ->where('res.apto', 'SI');
    }
}
