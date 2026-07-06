<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenSimulacro extends Model
{
    use HasFactory;

    protected $table = 'examen_simulacro';

    protected $fillable = [
        'id_simulacro',
        'area',
        'n_preguntas',
        'n_alternativas',
        'id_ponderacion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'n_preguntas' => 'integer',
        'n_alternativas' => 'integer',
    ];

    public function simulacro()
    {
        return $this->belongsTo(Simulacro::class, 'id_simulacro');
    }

    public function tipos()
    {
        return $this->hasMany(ExamenTipo::class, 'id_examen_simulacro');
    }

    public function ponderacion()
    {
        return $this->belongsTo(Ponderacion::class, 'id_ponderacion');
    }
}
