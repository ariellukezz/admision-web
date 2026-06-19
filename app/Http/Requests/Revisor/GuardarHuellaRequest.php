<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class GuardarHuellaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'huella_data'    => 'required|string',
            'dni'            => 'required|string|max:15',
            'id_postulante'  => 'nullable|integer',
            'tipo'           => 'nullable|string|in:wsq,raw,iso',
        ];
    }

    public function messages(): array
    {
        return [
            'huella_data.required' => 'Los datos de la huella son obligatorios',
            'dni.required'         => 'El DNI es obligatorio',
        ];
    }
}
