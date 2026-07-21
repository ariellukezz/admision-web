<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PruebaTipo extends Model
{
    protected $table = 'prueba_tipos';

    protected $fillable = [
        'id_prueba',
        'id_archivo',
        'tipo',
        'respuestas',
    ];

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(PruebaArchivo::class, 'id_archivo');
    }

    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class, 'id_prueba');
    }

    public function excepciones(): HasMany
    {
        return $this->hasMany(PruebaExcepcion::class, 'id_prueba_tipo');
    }
}
