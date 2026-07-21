<?php

namespace App\Modules\Calificacion\Models;

use App\Models\Simulacro;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenSimulacro extends Model
{
    use HasFactory;

    protected $table = 'examen_simulacro';

    protected $fillable = [
        'tipo',
        'id_area',
        'area',
        'n_preguntas',
        'n_alternativas',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'n_preguntas' => 'integer',
        'n_alternativas' => 'integer',
    ];

    public function tipos()
    {
        return $this->hasMany(ExamenTipo::class, 'id_examen_simulacro');
    }
}
