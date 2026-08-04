<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    protected $table = 'participantes';

    public $timestamps = true;

    protected $fillable = [
        'dni',
        'nombres',
        'paterno',
        'materno',
        'observaciones',
        'cod_puesto',
        'puesto',
        'unidad',
        'cod_examen',
        'id_proceso',
    ];

    protected $casts = [
        'id_proceso' => 'integer',
    ];
}
