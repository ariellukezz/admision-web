<?php

namespace App\Http\Resources\Revisor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResumenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'inscritos'                => $this->resource['inscritos'] ?? 0,
            'inscritos_hoy'            => $this->resource['inscritos_hoy'] ?? 0,
            'preinscritos'             => $this->resource['preinscritos'] ?? 0,
            'preinscritos_hoy'         => $this->resource['preinscritos_hoy'] ?? 0,
            'biometricos'              => $this->resource['biometricos'] ?? 0,
            'biometricos_hoy'          => $this->resource['biometricos_hoy'] ?? 0,
            'documentos_pendientes'    => $this->resource['documentos_pendientes'] ?? 0,
            'documentos_verificados'   => $this->resource['documentos_verificados'] ?? 0,
            'comprobantes_pendientes'  => $this->resource['comprobantes_pendientes'] ?? 0,
            'comprobantes_verificados' => $this->resource['comprobantes_verificados'] ?? 0,
        ];
    }
}
