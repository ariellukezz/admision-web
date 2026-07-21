<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PruebaRes extends Model
{
    protected $table = 'prueba_res';

    protected $fillable = [
        'id_prueba',
        'id_archivo',
        'n_lectura',
        'c1',
        'c3',
        'litho',
        'respuestas',
        'puntaje',
        'calificado',
    ];

    protected $casts = [
        'puntaje' => 'decimal:3',
        'calificado' => 'boolean',
    ];

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(PruebaArchivo::class, 'id_archivo');
    }

    public function prueba(): BelongsTo
    {
        return $this->belongsTo(Prueba::class, 'id_prueba');
    }
}
