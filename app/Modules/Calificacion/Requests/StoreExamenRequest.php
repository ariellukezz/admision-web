<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => 'nullable|string|max:1',
            'id_area' => 'nullable|integer',
            'area' => 'required|string|max:50',
            'n_preguntas' => 'nullable|integer|min:1',
            'n_alternativas' => 'nullable|integer|min:1',
            'estado' => 'nullable|boolean',
        ];
    }
}
