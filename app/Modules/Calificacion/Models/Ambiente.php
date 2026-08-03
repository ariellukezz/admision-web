<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambiente extends Model
{
    protected $table = 'ambientes';

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
        return $this->hasMany(AulaGestion::class, 'id_ambiente');
    }

    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Programa::class,
            'ambiente_programa',
            'id_ambiente',
            'id_programa'
        )->withTimestamps();
    }
}
