<?php

namespace App\Services\Auth;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InscripcionLookupService
{
    /**
     * @param array<int, string> $numerosDocumento
     * @return Collection<int, object>
     */
    public function buscarInscritos(int $idProceso, array $numerosDocumento): Collection
    {
        return DB::table('inscripciones as ins')
            ->join('postulante as pos', 'ins.id_postulante', '=', 'pos.id')
            ->join('procesos as pro', 'ins.id_proceso', '=', 'pro.id')
            ->where('ins.id_proceso', $idProceso)
            ->where('ins.estado', 0)
            ->whereIn('pos.nro_doc', $numerosDocumento)
            ->select('pos.nro_doc', 'pro.nombre')
            ->selectRaw("CASE WHEN ins.estado = 0 THEN 'INSCRITO' ELSE 'OBSERVADO' END AS estado")
            ->orderBy('pro.nombre', 'asc')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function buscarInscripcionPorDni(int $idProceso, string $numeroDocumento): Collection
    {
        return DB::table('inscripciones as ins')
            ->join('programa as pro', 'ins.id_programa', '=', 'pro.id')
            ->join('postulante as pos', 'ins.id_postulante', '=', 'pos.id')
            ->join('modalidad as moda', 'ins.id_modalidad', '=', 'moda.id')
            ->where('ins.id_proceso', $idProceso)
            ->where('ins.estado', 0)
            ->where('pos.nro_doc', $numeroDocumento)
            ->select(
                'pos.nro_doc as dni',
                'pro.nombre as programa',
                'moda.nombre as modalidad',
                'pro.area',
            )
            ->selectRaw("CASE WHEN ins.estado = 0 THEN 'INSCRITO' ELSE 'OBSERVADO' END AS estado")
            ->selectRaw("CASE WHEN pos.sexo = 1 THEN 'M' ELSE 'F' END AS sexo")
            ->orderBy('pro.nombre', 'asc')
            ->get();
    }
}
