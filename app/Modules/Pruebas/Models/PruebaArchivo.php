<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PruebaArchivo extends Model
{
    protected $table = 'prueba_archivos';

    protected $fillable = [
        'id_prueba',
        'nombre',
        'tipo',
        'url',
        'estado',
    ];

    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class, 'id_prueba');
    }

    public function ides(): HasMany
    {
        return $this->hasMany(PruebaIde::class, 'id_archivo');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(PruebaRes::class, 'id_archivo');
    }

    public function tipos(): HasMany
    {
        return $this->hasMany(PruebaTipo::class, 'id_archivo');
    }
}
