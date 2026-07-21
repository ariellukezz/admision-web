<?php

namespace App\Modules\Calificacion\Models;

use App\Models\Modalidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterGroup extends Model
{
    protected $table = 'grupos_filtro';

    protected $fillable = [
        'seleccion_id',
        'descripcion',
        'filter_group_id',
        'nivel_filtro',
        'valor_nivel_filtro',
        'id_modalidad',
        'id_modalidades',
        'id_proceso',
        'orden_procesamiento',
        'postulantes_count'
    ];

    protected $casts = [
        'id_modalidades' => 'array',
        'valor_nivel_filtro' => 'array'
    ];

    public function selection(): BelongsTo
    {
        return $this->belongsTo(ColumnSelection::class, 'seleccion_id', 'id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad', 'id_modalidad');
    }
    public function conditions(): HasMany
    {
        return $this->hasMany(FilterCondition::class, 'grupo_filtro_id', 'id');
    }
}
