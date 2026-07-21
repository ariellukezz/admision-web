<?php

namespace App\Modules\Calificacion\Models;

use App\Models\Postulante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomAssignment extends Model
{
    protected $table = 'asignaciones_aulas';
    public $timestamps = true;

    protected $fillable = [
        'aula_id',
        'grupo_filtro_id',
        'id_postulante',
        'codigo',
        'posicion',
        'tipo_examen'
    ];

    protected $casts = [
        'posicion' => 'integer'
    ];

    /**
     * Get the classroom that owns this assignment
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'aula_id');
    }

    /**
     * Get the filter group that owns this assignment
     */
    public function filterGroup(): BelongsTo
    {
        return $this->belongsTo(FilterGroup::class, 'grupo_filtro_id');
    }

    /**
     * Get the postulante that is assigned
     */
    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'id_postulante', 'id_postulante');
    }

    /**
     * Scope to get assignments by classroom
     */
    public function scopeByClassroom($query, int $classroomId)
    {
        return $query->where('aula_id', $classroomId);
    }

    /**
     * Scope to get assignments by filter group
     */
    public function scopeByFilterGroup($query, int $filterGroupId)
    {
        return $query->where('grupo_filtro_id', $filterGroupId);
    }

    /**
     * Get row and column from position (for 8x5 grid)
     */
    public function getRowAttribute(): int
    {
        return (int) floor(($this->posicion - 1) / 5);
    }

    public function getColAttribute(): int
    {
        return (int) (($this->posicion - 1) % 5);
    }
}
