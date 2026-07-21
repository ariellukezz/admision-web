<?php

namespace App\Modules\Pruebas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PruebaIde extends Model
{
    protected $table = 'prueba_ides';

    protected $fillable = [
        'id_prueba',
        'id_archivo',
        'camp1',
        'camp2',
        'camp3',
        'camp4',
        'litho',
        'tipo',
        'dni',
        'aula',
        'estado',
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
