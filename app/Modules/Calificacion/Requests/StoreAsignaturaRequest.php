<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignaturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'orden' => 'nullable|integer|min:0',
            'estado' => 'nullable|boolean',
        ];
    }
}
