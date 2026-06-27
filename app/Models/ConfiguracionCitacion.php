<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionCitacion extends Model
{
    use HasFactory;

    protected $table = 'configuracion_citacion';

    protected $fillable = [
        'id_proceso',
        'tipo_criterio',
        'valor',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'lugar',
        'instrucciones',
        'estado',
        'id_usuario',
    ];

    protected $casts = [
        'estado' => 'boolean',
        'fecha' => 'date',
    ];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'id_proceso');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
