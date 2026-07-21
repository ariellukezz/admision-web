<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Ingresante extends Model
{
    use HasFactory;

    protected $table = 'resultados';

    protected $fillable = [
        'dni_postulante',
        'id_proceso',
        'paterno',
        'materno',
        'nombres',
        'modalidad',
        'puntaje',
        'apto',
        'observacion',
        'programa',
        'id_examen',
        'litho',
        'numlectura',
        'tipo',
        'calificado',
        'aula',
        'respuestas',
        'fecha',
        'puesto',
        'puesto_general'
    ];
}
