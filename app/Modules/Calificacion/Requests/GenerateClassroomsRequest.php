<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateClassroomsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter_group_id' => 'required|integer|exists:grupos_filtro,id',
            'capacity_per_classroom' => 'nullable|integer|min:1|max:50',
            'capacity_exceptions' => 'nullable|array',
            'capacity_exceptions.*.classroom_num' => 'required|integer|min:1',
            'capacity_exceptions.*.capacity' => 'required|integer|min:1|max:50',
            'capacity_exceptions.*.reason' => 'nullable|string|max:255',
        ];
    }
}
