<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class ObservarDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_documento' => 'required|integer',
            'observacion'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_documento.required' => 'El ID del documento es obligatorio',
            'id_documento.integer'  => 'El ID del documento debe ser un número entero',
            'observacion.max'       => 'La observación no puede exceder 1000 caracteres',
        ];
    }
}
