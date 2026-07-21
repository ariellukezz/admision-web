<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;

class UbicacionAula extends Model
{
    protected $table = 'ubicacion_aula';

    protected $fillable = [
        'codigo',
        'pabellon',
        'piso',
        'capacidad',
        'area',
    ];
}
