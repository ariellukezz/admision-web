<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Huella extends Model
{
    use HasFactory;

    protected $table = 'huellas';

    protected $fillable = [
        'id_postulante',
        'dedo',
        'template',
        'imagen',
        'calidad',
        'hash_template',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

}