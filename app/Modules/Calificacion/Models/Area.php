<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo', 'numero_base', 'estado'];

    protected $casts = ['estado' => 'boolean'];
}
