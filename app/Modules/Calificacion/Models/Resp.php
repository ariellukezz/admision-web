<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resp extends Model
{
    use HasFactory;

    protected $table = 'res';

    protected $fillable = [
        'id', 'c1', 'n_lectura', 'c3', 'c4', 'c5', 'c6', 'c7',
        'litho', 'tipo', 'respuestas', 'id_archivo', 'id_examen_tipo', 'puntaje', 'calificado',
    ];

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(ArchivoLectura::class, 'id_archivo');
    }
}
