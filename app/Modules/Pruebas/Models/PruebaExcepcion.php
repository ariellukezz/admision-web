<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PruebaExcepcion extends Model
{
    protected $table = 'prueba_excepciones';

    protected $fillable = [
        'id_prueba_tipo',
        'nro_pregunta',
        'accion',
        'claves_validas',
        'puntaje',
        'observacion',
        'tipo',
    ];

    protected $casts = [
        'puntaje' => 'decimal:3',
    ];

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(PruebaTipo::class, 'id_prueba_tipo');
    }
}
