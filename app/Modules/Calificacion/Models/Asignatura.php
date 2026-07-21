<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'orden', 'estado'];

    protected $casts = ['estado' => 'boolean'];

    public function detalles()
    {
        return $this->hasMany(PonderacionDetalle::class, 'id_asignatura');
    }
}
