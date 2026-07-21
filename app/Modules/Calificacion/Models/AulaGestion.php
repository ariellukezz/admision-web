<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AulaGestion extends Model
{
    protected $table = 'aulas_gestion';

    public $timestamps = true;

    protected $fillable = [
        'id_pabellon',
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

    public function pabellon(): BelongsTo
    {
        return $this->belongsTo(Pabellon::class, 'id_pabellon');
    }
}
