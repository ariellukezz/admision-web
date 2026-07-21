<?php

namespace App\Modules\Pruebas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalificarPruebaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_ponderacion' => 'required|integer|exists:ponderacion_simulacro,id',
            'id_multiplicador' => 'required|integer|exists:multiplicadores,id',
        ];
    }
}
