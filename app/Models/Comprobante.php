<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $table = 'comprobante';

    protected $fillable = [
        'nro_operacion',
        'fecha',
        'hora',
        'codigo',
        'monto',
        'estado',
        'ndoc_postulante'
    ];
}