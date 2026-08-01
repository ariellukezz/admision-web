<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParticipantePersonal extends Model
{
    use HasFactory;

    protected $table = 'participantes_personales';

    protected $fillable = [
        'dni',
        'nombres',
        'paterno',
        'materno',
        'id_tipo_personal',
        'codigo_trabajador',
        'condicion',
        'dependencia',
        'foto',
        'estado',
        'id_usuario',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function tipoPersonal()
    {
        return $this->belongsTo(TipoPersonal::class, 'id_tipo_personal');
    }
}
