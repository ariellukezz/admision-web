<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePonderacionDetalleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_ponderacion' => 'required|exists:ponderacion_simulacro,id',
            'detalles' => 'required|array',
            'detalles.*.cantidad_preguntas' => 'required|integer|min:0',
            'detalles.*.ponderacion' => 'required|numeric',
        ];
    }
}
