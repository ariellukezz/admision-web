<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ConsultaPorProcesoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'id_proceso' => ['required', 'integer', 'min:1'],
            'nro_docs' => ['required', 'array', 'min:1', 'max:500'],
            'nro_docs.*' => ['required', 'string', 'regex:/^\d{8}$/', 'distinct'],
        ];
    }
}
