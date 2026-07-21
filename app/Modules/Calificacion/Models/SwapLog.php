<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SwapLog extends Model
{
    protected $table = 'registros_intercambios';
    public $timestamps = false; // Solo usa created_at
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'evento_id',
        'usuario_id',
        'origen_grupo_filtro_id',
        'origen_aula_id',
        'origen_posicion',
        'origen_id_postulante_anterior',
        'origen_codigo_anterior',
        'origen_tipo_examen_anterior',
        'destino_grupo_filtro_id',
        'destino_aula_id',
        'destino_posicion',
        'destino_id_postulante_anterior',
        'destino_codigo_anterior',
        'destino_tipo_examen_anterior',
        'tipo_intercambio',
        'motivo',
        'metadata',
        'estado',
        'revertido_por_intercambio_id',
        'fecha_reversion'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'fecha_reversion' => 'datetime'
    ];

    /**
     * Get the event that owns this swap log
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(RedistributionEvent::class, 'evento_id');
    }

    /**
     * Get the origin classroom
     */
    public function originClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'origen_aula_id');
    }

    /**
     * Get the destination classroom
     */
    public function destinationClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'destino_aula_id');
    }

    /**
     * Get the origin filter group
     */
    public function originFilterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'origen_grupo_filtro_id');
    }

    /**
     * Get the destination filter group
     */
    public function destinationFilterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'destino_grupo_filtro_id');
    }

    /**
     * Scope to get cross-group swaps
     */
    public function scopeCrossGroup($query)
    {
        return $query->where('tipo_intercambio', 'cross_group');
    }

    /**
     * Scope to get completed swaps
     */
    public function scopeCompleted($query)
    {
        return $query->where('estado', 'completed');
    }

    /**
     * Check if this is a cross-group swap
     */
    public function isCrossGroup(): bool
    {
        return $this->origen_grupo_filtro_id !== $this->destino_grupo_filtro_id;
    }
}
