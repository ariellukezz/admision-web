<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AulaGestion extends Model
{
    protected $table = 'aulas_gestion';

    public $timestamps = true;

    protected $fillable = [
        'id_ambiente',
        'codigo',
        'piso',
        'capacidad',
        'tipo',
        'estado',
    ];

    protected $casts = [
        'piso' => 'integer',
        'capacidad' => 'integer',
        'estado' => 'boolean',
    ];

    public function ambiente(): BelongsTo
    {
        return $this->belongsTo(Ambiente::class, 'id_ambiente');
    }
}
