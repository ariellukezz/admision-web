<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class FinalizarRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitud_id'   => 'nullable|integer',
            'fecha'          => 'required|date',
            'hora_inicio'    => 'required|string|max:10',
            'hora_fin'       => 'required|string|max:10',
            'lugar'          => 'required|string|max:255',
            'instrucciones'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'fecha.required'       => 'La fecha de citación es obligatoria',
            'fecha.date'           => 'La fecha debe ser una fecha válida',
            'hora_inicio.required' => 'La hora de inicio es obligatoria',
            'hora_fin.required'    => 'La hora de fin es obligatoria',
            'lugar.required'       => 'El lugar de citación es obligatorio',
            'lugar.max'            => 'El lugar no puede exceder 255 caracteres',
            'instrucciones.max'    => 'Las instrucciones no pueden exceder 1000 caracteres',
        ];
    }
}
