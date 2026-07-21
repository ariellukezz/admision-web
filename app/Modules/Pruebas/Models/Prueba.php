<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prueba extends Model
{
    protected $table = 'pruebas';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_ponderacion',
        'id_multiplicador',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function ponderacion(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Calificacion\Models\Ponderacion::class, 'id_ponderacion');
    }

    public function multiplicador(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Calificacion\Models\Multiplicador::class, 'id_multiplicador');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(PruebaArchivo::class, 'id_prueba');
    }

    public function ides(): HasMany
    {
        return $this->hasMany(PruebaIde::class, 'id_prueba');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(PruebaRes::class, 'id_prueba');
    }

    public function tipos(): HasMany
    {
        return $this->hasMany(PruebaTipo::class, 'id_prueba');
    }
}
