<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificarExamenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_calificacion' => 'required|integer|exists:calificaciones,id',
            'id_examen' => 'required|integer|exists:examen_simulacro,id',
            'id_ponderacion' => 'required|integer|exists:ponderacion_simulacro,id',
            'id_multiplicador' => 'required|integer|exists:multiplicadores,id',
        ];
    }
}
