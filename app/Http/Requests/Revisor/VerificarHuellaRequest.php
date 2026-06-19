<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class VerificarHuellaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'huella_data'   => 'required|string',
            'dni'           => 'nullable|string|max:15',
            'id_postulante' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'huella_data.required' => 'Los datos de la huella son obligatorios',
        ];
    }
}
