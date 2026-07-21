<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    use HasFactory;

    protected $table = 'vacantes';
    protected $fillable = ['id', 'id_programa', 'vacantes', 'id_proceso', 'id_modalidad', 'id_usuario','estado','etapa'];

}


