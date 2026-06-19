<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_documento'     => 'required|integer',
            'accion'           => 'required|string|in:apto_revision,valido,desmarcar',
            'fecha_caducidad' => 'nullable|date',
            'observacion'      => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_documento.required' => 'El ID del documento es obligatorio',
            'id_documento.integer'  => 'El ID del documento debe ser un número entero',
            'accion.required'       => 'La acción es obligatoria',
            'accion.in'             => 'La acción debe ser: apto_revision, valido o desmarcar',
            'fecha_caducidad.date'  => 'La fecha de caducidad debe ser una fecha válida',
            'observacion.max'        => 'La observación no puede exceder 1000 caracteres',
        ];
    }
}
