<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Preinscripcion extends Model
{
    use HasFactory;

    protected $table = 'pre_inscripcion';

    protected $fillable = [
        'id_postulante',
        'id_programa',
        'id_proceso',
        'id_modalidad',
        'estado',
        'codigo_seguridad',
        'id_usuario',
        'observacion',
        'id_anterior'
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante');
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class, 'id_programa');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad');
    }
}
