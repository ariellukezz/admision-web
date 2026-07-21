<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePonderacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'nombre' => 'required|string|max:255',
            'estado' => 'nullable|boolean',
            'id_multiplicador' => 'nullable|integer',
        ];
    }
}
