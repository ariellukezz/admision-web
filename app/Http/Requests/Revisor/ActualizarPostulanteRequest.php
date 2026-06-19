<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarPostulanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_postulante'     => 'required|integer',
            'nombres'           => 'nullable|string|max:255',
            'primer_apellido'   => 'nullable|string|max:255',
            'segundo_apellido'  => 'nullable|string|max:255',
            'sexo'              => 'nullable|string|max:1',
            'fecha_nacimiento'  => 'nullable|date',
            'telefono'          => 'nullable|string|max:20',
            'correo'            => 'nullable|email|max:255',
            'direccion'         => 'nullable|string|max:500',
        ];
    }
}
