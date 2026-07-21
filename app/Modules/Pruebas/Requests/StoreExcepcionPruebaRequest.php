<?php

namespace App\Modules\Pruebas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExcepcionPruebaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nro_pregunta' => 'required|integer|min:1|max:60',
            'accion' => 'required|string|in:todas_validas,multiples_validas,anulada,asignar_puntaje',
            'claves_validas' => 'nullable|string|max:50',
            'puntaje' => 'nullable|numeric',
            'observacion' => 'nullable|string|max:200',
            'tipo' => 'nullable|string|max:1',
        ];
    }
}
