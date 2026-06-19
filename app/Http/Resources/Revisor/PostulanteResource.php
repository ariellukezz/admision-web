<?php

namespace App\Http\Resources\Revisor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostulanteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'dni'              => $this->nro_doc,
            'nombres'          => $this->nombres,
            'primer_apellido'  => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,
            'nombre_completo'  => trim("{$this->primer_apellido} {$this->segundo_apellido} {$this->nombres}"),
            'sexo'             => $this->sexo,
            'revisado'         => $this->revisado,
            'tiene_revision_activa' => $this->tiene_revision_activa,
        ];
    }
}
