<?php

namespace App\Http\Resources\Revisor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevisionSolicitudResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'postulante_id'          => $this->id_postulante,
            'proceso_id'             => $this->id_proceso,
            'modalidad_id'           => $this->id_modalidad,
            'programa_id'            => $this->id_programa,
            'estado'                 => $this->estado,
            'apto'                   => $this->apto,
            'apto_at'                => $this->apto_at?->format('d/m/Y H:i'),
            'revisor_id'             => $this->revisor_id,
            'solicitada_at'          => $this->solicitada_at?->format('d/m/Y H:i'),
            'solicitada_at_diff'     => $this->solicitada_at?->diffForHumans(),
            'iniciada_at'            => $this->iniciada_at?->format('d/m/Y H:i'),
            'finalizada_at'          => $this->finalizada_at?->format('d/m/Y H:i'),
            'veces'                  => $this->veces,
            'datos_citacion'         => $this->datos_citacion,
            'documentos_verificados' => $this->documentos_verificados ?? [],
            'documentos_pendientes'  => $this->documentos_pendientes ?? [],
        ];
    }
}
