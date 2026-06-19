<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class GetPostulantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'busqueda'  => 'nullable|string|max:100',
            'page'      => 'nullable|integer|min:1',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'modalidad' => 'nullable|integer',
            'programa'  => 'nullable|integer',
        ];
    }
}
