<?php

namespace App\Modules\Calificacion\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_proceso' => $this->id_proceso,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'id_multiplicador' => $this->id_multiplicador,
            'id_ponderacion' => $this->id_ponderacion,
            'estado' => $this->estado,
            'proceso_nombre' => $this->whenLoaded('proceso', fn() => $this->proceso->nombre),
            'multiplicador_nombre' => $this->whenLoaded('multiplicador', fn() => $this->multiplicador->nombre),
            'ponderacion_nombre' => $this->whenLoaded('ponderacion', fn() => $this->ponderacion->nombre),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
