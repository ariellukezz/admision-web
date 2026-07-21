<?php

namespace App\Modules\Pruebas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CargaArchivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|extensions:txt,dat',
            'tipo' => 'required|string|in:ide,res,tipos',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Debe seleccionar un archivo',
            'file.extensions' => 'El archivo debe ser de tipo txt o dat',
            'tipo.required' => 'Debe especificar el tipo de archivo',
            'tipo.in' => 'El tipo debe ser: ide, res o tipos',
        ];
    }
}
