<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMultiplicadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'correcta' => 'required|numeric',
            'incorrecta' => 'required|numeric',
            'blanco' => 'required|numeric',
            'estado' => 'nullable|boolean',
        ];
    }
}
