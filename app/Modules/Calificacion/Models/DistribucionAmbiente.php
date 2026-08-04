<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DistribucionAmbiente extends Model
{
    protected $table = 'distribucion_ambientes';

    public $timestamps = true;

    protected $fillable = [
        'id_proceso',
        'id_grupo_filtro',
        'total_estudiantes',
        'total_aulas',
        'total_capacidad',
        'estado',
    ];

    protected $casts = [
        'id_proceso' => 'integer',
        'id_grupo_filtro' => 'integer',
        'total_estudiantes' => 'integer',
        'total_aulas' => 'integer',
        'total_capacidad' => 'integer',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DistribucionAmbienteDetalle::class, 'id_distribucion')
            ->orderBy('orden_ambiente')
            ->orderBy('codigo_asignado');
    }

    public function grupoFiltro(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'id_grupo_filtro');
    }

    public function personalAsignado(): HasMany
    {
        return $this->hasMany(AsignacionPersonal::class, 'id_distribucion');
    }
}
