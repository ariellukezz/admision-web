<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puntaje extends Model
{
    protected $table = 'puntajes';

    protected $fillable = [
        'fecha',
        'dni',
        'paterno',
        'materno',
        'nombres',
        'puntaje',
        'puntaje_vocacional',
        'apto',
        'programa',
        'area',
        'modalidad',
        'id_proceso',
        'id_inscripcion',
        'puesto',
    ];
}
