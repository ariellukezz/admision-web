<?php

namespace App\Modules\Calificacion\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MultiplicadorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'correcta' => $this->correcta,
            'incorrecta' => $this->incorrecta,
            'blanco' => $this->blanco,
            'estado' => $this->estado,
        ];
    }
}
