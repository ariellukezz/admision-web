<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pabellon extends Model
{
    protected $table = 'pabellones';

    public $timestamps = true;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'ubicacion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function aulas(): HasMany
    {
        return $this->hasMany(AulaGestion::class, 'id_pabellon');
    }

    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Programa::class,
            'pabellon_programa',
            'id_pabellon',
            'id_programa'
        )->withTimestamps();
    }
}
