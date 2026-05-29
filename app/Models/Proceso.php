<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proceso extends Model
{
    use HasFactory;

    protected $table = 'procesos';

    protected $fillable = [
        'nombre',
        'slug',
        'id_sede_filial',
        'id_tipo_proceso',
        'ciclo',
        'ciclo_oti',
        'id_modalidad_proceso',
        'anio',
        'estado',
        'fecha_examen',
        'fec_inicio',
        'fec_fin',
        'id_reglamento',
        'codigo_proceso',
        'nro_convocatoria',
        'id_modalidad_estudio',
        'id_usuario',
        'observaciones',
        'fec_1',
        'fec_2',
        'url',
        'nivel'
    ];

    public function procesoRequisitos()
    {
        return $this->hasMany(ProcesoRequisito::class, 'id_proceso');
    }

    public function requisitosActivos()
    {
        return $this->hasMany(ProcesoRequisito::class, 'id_proceso')->where('activo', true);
    }
}
