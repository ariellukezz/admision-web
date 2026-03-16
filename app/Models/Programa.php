<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    use HasFactory;

    protected $table = 'programa';

    protected $fillable = [
        'nombre',
        'codigo',
        'nivel_academico',
        'tipo_autorizacion',
        'estado',
        'efi',
        'id_facultad',
        'area',
        'nivel',
        'duracion',
        'id_usuario'
    ];
}
