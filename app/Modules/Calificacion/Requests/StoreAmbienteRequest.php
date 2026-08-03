<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAmbienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:20|unique:ambientes,codigo',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'nullable|boolean',
            'programas' => 'nullable|array',
            'programas.*' => 'integer|exists:programa,id',
        ];
    }
}
