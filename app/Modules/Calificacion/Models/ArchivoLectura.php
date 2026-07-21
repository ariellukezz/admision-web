<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivoLectura extends Model
{
    use HasFactory;

    protected $table = 'archivo_lectura';

    protected $fillable = [
        'id_calificacion',
        'nombre',
        'tipo',
        'area',
        'url',
        'estado',
    ];

    public function calificacion(): BelongsTo
    {
        return $this->belongsTo(Calificacion::class, 'id_calificacion');
    }
}
