<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
// ========== FILTER_CONDITIONS ==========
class FilterCondition extends Model
{
    protected $table = 'condiciones_filtro';

    protected $fillable = [
        'grupo_filtro_id',
        'nombre_columna',
        'tipo_condicion',
        'parametros_condicion'
    ];

    protected $casts = [
        'parametros_condicion' => 'array'
    ];

    public function filterGroup()
    {
        return $this->belongsTo(FilterGroup::class, 'grupo_filtro_id', 'id');
    }
}
