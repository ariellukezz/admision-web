<?php

namespace App\Modules\Pruebas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePruebaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'id_ponderacion' => 'nullable|integer|exists:ponderacion_simulacro,id',
            'id_multiplicador' => 'nullable|integer|exists:multiplicadores,id',
            'estado' => 'nullable|boolean',
        ];
    }
}
