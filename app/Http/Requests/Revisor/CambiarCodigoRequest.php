<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class CambiarCodigoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni'           => 'required|string|max:15',
            'codigo_nuevo'  => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required'          => 'El DNI es obligatorio',
            'codigo_nuevo.required' => 'El nuevo código es obligatorio',
        ];
    }
}
