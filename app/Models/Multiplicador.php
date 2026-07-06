<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multiplicador extends Model
{
    use HasFactory;

    protected $table = 'multiplicadores';

    protected $fillable = [
        'nombre',
        'correcta',
        'incorrecta',
        'blanco',
        'estado',
    ];

    protected $casts = [
        'correcta' => 'decimal:3',
        'incorrecta' => 'decimal:3',
        'blanco' => 'decimal:3',
        'estado' => 'boolean',
    ];
}
