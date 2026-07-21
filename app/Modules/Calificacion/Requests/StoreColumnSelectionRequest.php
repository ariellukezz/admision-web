<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreColumnSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_name' => 'required|string|max:255',
            'selected_columns' => 'required|array|min:1',
            'selected_columns.*' => 'required|string|max:512',
        ];
    }
}
