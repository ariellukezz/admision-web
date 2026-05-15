<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripciones';

    protected $fillable = [
        'codigo',
        'id_postulante',
        'id_proceso',
        'id_programa',
        'id_modalidad',
        'id_usuario',
        'id_pre_inscripcion',
        'id_examen_vocacional',
        'estado',
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
