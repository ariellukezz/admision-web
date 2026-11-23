<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResumenIngresantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\In>>
     */
    public function rules(): array
    {
        return [
            'group_by' => ['required', 'array', 'min:1'],
            'group_by.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(['area', 'programa', 'modalidad', 'sexo']),
            ],
        ];
    }
}
