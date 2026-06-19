<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class CambiarProcesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_proceso' => 'required|integer|exists:procesos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id_proceso.required' => 'El proceso es obligatorio',
            'id_proceso.exists'   => 'El proceso seleccionado no existe',
        ];
    }
}
