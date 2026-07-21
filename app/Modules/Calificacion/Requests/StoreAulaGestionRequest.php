<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAulaGestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_pabellon' => 'required|integer|exists:pabellones,id',
            'codigo' => 'required|string|max:20',
            'piso' => 'nullable|integer|min:0|max:50',
            'capacidad' => 'nullable|integer|min:1|max:9999',
            'tipo' => 'nullable|in:aula,laboratorio,auditorio,otro',
            'estado' => 'nullable|boolean',
        ];
    }
}
