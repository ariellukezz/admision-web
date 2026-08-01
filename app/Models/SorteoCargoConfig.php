<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SorteoCargoConfig extends Model
{
    use HasFactory;

    protected $table = 'sorteo_cargo_config';

    protected $fillable = [
        'id_sorteo',
        'id_cargo',
        'cantidad',
        'id_usuario',
    ];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class, 'id_sorteo');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }
}
