<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class GuardarFotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto'     => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'dni'      => 'nullable|string|max:15',
            'id_postulante' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => 'La foto es obligatoria',
            'foto.image'    => 'El archivo debe ser una imagen',
            'foto.mimes'    => 'La foto debe ser formato JPEG, PNG o JPG',
            'foto.max'      => 'La foto no puede pesar más de 5MB',
        ];
    }
}
