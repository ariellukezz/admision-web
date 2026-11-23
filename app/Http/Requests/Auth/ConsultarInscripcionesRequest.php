<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ConsultarInscripcionesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (int) ($this->user()?->id_proceso ?? 0) > 0;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nro_docs' => ['required', 'array', 'min:1', 'max:500'],
            'nro_docs.*' => ['required', 'string', 'regex:/^\d{8}$/', 'distinct'],
        ];
    }
}
