<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamenTipoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'id_examen_simulacro' => 'required|exists:examen_simulacro,id',
            'tipo' => 'nullable|string|max:1',
            'respuestas' => 'nullable|string|max:90',
            'estado' => 'nullable|boolean',
        ];
    }
}
