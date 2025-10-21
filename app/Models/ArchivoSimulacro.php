<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoSimulacro extends Model
{
    use HasFactory;
    protected $table = 'archivos_simulacro';

    protected $fillable = [
        'nombre',
        'tipo',
        'id_simulacro',
        'area',
        'fecha',
        'url',
        'cod_examen',
        'unidad',
        'puesto',
        'categoria'
    ];

}
