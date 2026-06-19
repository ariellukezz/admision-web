<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class IniciarRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitud_id' => 'nullable|integer',
        ];
    }
}
