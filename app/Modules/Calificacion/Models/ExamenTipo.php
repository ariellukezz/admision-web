<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamenTipo extends Model
{
    use HasFactory;

    protected $table = 'examen_tipos';

    protected $fillable = [
        'id_examen_simulacro',
        'id_archivo',
        'tipo',
        'respuestas',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function examenSimulacro()
    {
        return $this->belongsTo(ExamenSimulacro::class, 'id_examen_simulacro');
    }

    public function archivo()
    {
        return $this->belongsTo(ArchivoSimulacro::class, 'id_archivo');
    }

    public function excepciones()
    {
        return $this->hasMany(Excepciones::class, 'id_examen_tipo');
    }

    public function respuestas()
    {
        return $this->hasMany(Resp::class, 'id_examen_tipo');
    }
}
