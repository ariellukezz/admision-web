<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Excepciones extends Model
{
    use HasFactory;

    protected $table = 'excepciones';

    protected $fillable = [
        'id_examen_tipo',
        'nro_pregunta',
        'accion',
        'cod_examen',
        'observacion',
        'claves_validas',
        'puntaje',
        'tipo',
        'id_proceso'
    ];

    public function examenTipo()
    {
        return $this->belongsTo(ExamenTipo::class, 'id_examen_tipo');
    }
}
