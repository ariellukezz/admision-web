<?php

namespace App\Http\Resources\Revisor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoRevisorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hoy = now()->startOfDay();

        return [
            'id'                  => $this->id,
            'nombre'              => $this->nombre,
            'url'                 => $this->url,
            'seleccionado'        => $this->seleccionado,
            'apto_revision'       => $this->apto_revision,
            'valido'              => $this->valido,
            'verificado'          => $this->valido,
            'fecha_caducidad'     => $this->fecha_caducidad?->format('Y-m-d'),
            'observacion_revisor' => $this->observacion_revisor,
            'revisado_at'         => $this->revisado_at?->format('d/m/Y H:i'),
            'validado_at'         => $this->validado_at?->format('d/m/Y H:i'),
            'vigente'             => !$this->fecha_caducidad || $this->fecha_caducidad >= $hoy,
            'por_vencer'          => $this->fecha_caducidad
                && $this->fecha_caducidad < $hoy->copy()->addDays(30)
                && $this->fecha_caducidad >= $hoy,
            'caducado'            => $this->fecha_caducidad && $this->fecha_caducidad < $hoy,
        ];
    }
}
