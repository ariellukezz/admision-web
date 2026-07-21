<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePabellonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'codigo' => ['sometimes', 'string', 'max:20', Rule::unique('pabellones', 'codigo')->ignore($id)],
            'nombre' => 'sometimes|string|max:100',
            'descripcion' => 'nullable|string',
            'ubicacion' => 'nullable|string|max:255',
            'estado' => 'nullable|boolean',
            'programas' => 'nullable|array',
            'programas.*' => 'integer|exists:programa,id',
        ];
    }
}
