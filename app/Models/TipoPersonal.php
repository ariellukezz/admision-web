<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TipoPersonal extends Model
{
    use HasFactory;

    protected $table = 'tipo_personal';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];
}
