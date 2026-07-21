<?php

namespace App\Modules\Calificacion\Models;

use App\Models\Modalidad;
use App\Models\Postulante;
use Illuminate\Database\Eloquent\Model;
class PostulanteCode extends Model
{
    protected $table = 'codigos_postulantes';

    protected $fillable = [
        'grupo_filtro_id',
        'id_postulante',
        'id_modalidad',
        'id_proceso',
        'codigo'
    ];

    public function filterGroup()
    {
        return $this->belongsTo(FilterGroup::class, 'grupo_filtro_id', 'id');
    }

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante', 'id_postulante');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad', 'id_modalidad');
    }
}
