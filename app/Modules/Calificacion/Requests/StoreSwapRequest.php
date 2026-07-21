<?php

namespace App\Modules\Calificacion\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSwapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'swaps' => 'required|array|min:1',
            'swaps.*.origin.classroom_id' => 'required|exists:aulas,id',
            'swaps.*.origin.position' => 'required|integer|between:1,50',
            'swaps.*.origin.id_postulante' => 'required|integer',
            'swaps.*.destination.classroom_id' => 'required|exists:aulas,id',
            'swaps.*.destination.position' => 'required|integer|between:1,50',
            'swaps.*.destination.id_postulante' => 'nullable|integer',
            'reason' => 'nullable|string|max:500',
        ];
    }
}
