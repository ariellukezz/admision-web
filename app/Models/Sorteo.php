<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sorteo extends Model
{
    use HasFactory;

    protected $table = 'sorteos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_proceso',
        'estado',
        'id_usuario',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function tiposPersonal()
    {
        return $this->belongsToMany(TipoPersonal::class, 'sorteo_tipo_personal', 'id_sorteo', 'id_tipo_personal');
    }

    public function seleccionados()
    {
        return $this->hasMany(SorteoSeleccionado::class, 'id_sorteo');
    }

    public function cargoConfigs()
    {
        return $this->hasMany(SorteoCargoConfig::class, 'id_sorteo');
    }

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'id_proceso');
    }
}
