<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ponderacion extends Model
{
    use HasFactory;

    protected $table = 'ponderacion_simulacro';

    protected $fillable = [
        'nombre',
        'total_preguntas',
        'total_ponderacion',
        'estado',
        'id_multiplicador',
    ];

    protected $casts = [
        'total_preguntas' => 'integer',
        'total_ponderacion' => 'decimal:3',
        'estado' => 'boolean',
        'id_multiplicador' => 'integer',
    ];

    public function detalles()
    {
        return $this->hasMany(PonderacionDetalle::class, 'id_ponderacion_simulacro');
    }

    public function asignaturas()
    {
        return $this->belongsToMany(Asignatura::class, 'ponderacion', 'id_ponderacion_simulacro', 'id_asignatura')
            ->withPivot(['numero', 'ponderacion', 'cantidad_preguntas', 'subtotal', 'asignatura']);
    }
}
