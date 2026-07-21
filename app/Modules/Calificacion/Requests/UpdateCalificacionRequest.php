<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_proceso' => 'required|integer',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_multiplicador' => 'nullable|integer',
            'id_ponderacion' => 'nullable|integer',
            'estado' => 'nullable|boolean',
        ];
    }
}
