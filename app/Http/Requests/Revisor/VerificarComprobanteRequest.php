<?php

namespace App\Http\Requests\Revisor;

use Illuminate\Foundation\Http\FormRequest;

class VerificarComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nro_operacion' => 'nullable|string|max:50',
            'dni'           => 'nullable|string|max:15',
            'monto'         => 'nullable|numeric|min:0',
            'id_comprobante' => 'nullable|integer',
        ];
    }
}
