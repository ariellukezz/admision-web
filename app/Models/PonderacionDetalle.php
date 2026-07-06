<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PonderacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'ponderacion';

    protected $fillable = [
        'asignatura',
        'numero',
        'ponderacion',
        'id_ponderacion_simulacro',
        'id_asignatura',
        'cantidad_preguntas',
        'subtotal',
    ];

    protected $casts = [
        'numero' => 'integer',
        'ponderacion' => 'decimal:3',
        'cantidad_preguntas' => 'integer',
        'subtotal' => 'decimal:3',
    ];

    public function asignaturaRelacion()
    {
        return $this->belongsTo(Asignatura::class, 'id_asignatura');
    }

    public function ponderacionSimulacro()
    {
        return $this->belongsTo(Ponderacion::class, 'id_ponderacion_simulacro');
    }
}
