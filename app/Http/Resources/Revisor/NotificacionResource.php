<?php

namespace App\Http\Resources\Revisor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'tipo'               => $this->data['tipo'] ?? 'otro',
            'mensaje'            => $this->data['mensaje'] ?? '',
            'postulante_nombre'  => $this->data['postulante_nombre'] ?? '',
            'postulante_dni'     => $this->data['postulante_dni'] ?? '',
            'veces'              => $this->data['veces'] ?? 1,
            'url'                => $this->data['url'] ?? '',
            'leida'              => $this->read_at !== null,
            'created_at'         => $this->created_at->format('d/m/Y H:i'),
            'created_at_diff'    => $this->created_at->diffForHumans(),
        ];
    }
}
