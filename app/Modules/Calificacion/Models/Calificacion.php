<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'id_proceso',
        'nombre',
        'descripcion',
        'id_multiplicador',
        'id_ponderacion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function proceso()
    {
        return $this->belongsTo(\App\Models\Proceso::class, 'id_proceso');
    }

    public function multiplicador()
    {
        return $this->belongsTo(Multiplicador::class, 'id_multiplicador');
    }

    public function ponderacion()
    {
        return $this->belongsTo(Ponderacion::class, 'id_ponderacion');
    }
}
