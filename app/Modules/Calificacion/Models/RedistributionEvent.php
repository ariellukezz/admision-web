<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedistributionEvent extends Model
{
    protected $table = 'eventos_redistribucion';
    public $timestamps = true;

    protected $fillable = [
        'grupo_filtro_id',
        'usuario_id',
        'tipo',
        'postulantes_count',
        'aulas_count',
        'capacidad_por_aula',
        'descripcion',
        'motivo',
        'metadata'
    ];

    protected $casts = [
        'postulantes_count' => 'integer',
        'aulas_count' => 'integer',
        'capacidad_por_aula' => 'integer',
        'metadata' => 'array'
    ];

    /**
     * Get the filter group that owns this event
     */
    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'grupo_filtro_id');
    }

    /**
     * Get all swap logs for this event
     */
    public function swapLogs(): HasMany
    {
        return $this->hasMany(SwapLog::class, 'evento_id');
    }

    /**
     * Scope to get manual events
     */
    public function scopeManual($query)
    {
        return $query->where('tipo', 'manual');
    }

    /**
     * Scope to get automatic events
     */
    public function scopeAleatoria($query)
    {
        return $query->where('tipo', 'aleatoria');
    }
}
