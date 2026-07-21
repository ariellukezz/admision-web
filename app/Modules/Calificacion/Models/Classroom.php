<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $table = 'aulas';
    public $timestamps = true;

    protected $fillable = [
        'grupo_filtro_id',
        'nombre',
        'capacidad',
        'contador_actual'
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'contador_actual' => 'integer'
    ];

    /**
     * Get the filter group that owns this classroom
     */
    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'grupo_filtro_id');
    }

    /**
     * Get all assignments for this classroom
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ClassroomAssignment::class, 'aula_id');
    }

    /**
     * Check if classroom is full
     */
    public function isFull(): bool
    {
        return $this->contador_actual >= $this->capacidad;
    }

    /**
     * Check if can accommodate more students
     */
    public function hasSpace(int $count = 1): bool
    {
        return ($this->contador_actual + $count) <= $this->capacidad;
    }

    /**
     * Increment current count
     */
    public function incrementCount(int $by = 1): void
    {
        $this->increment('contador_actual', $by);
    }

    /**
     * Decrement current count
     */
    public function decrementCount(int $by = 1): void
    {
        $this->decrement('contador_actual', $by);
    }
}
