<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'total_preguntas' => 'integer',
        'total_ponderacion' => 'decimal:3',
        'estado' => 'boolean',
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
