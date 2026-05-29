<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modalidad extends Model
{
    use HasFactory;

    protected $table = 'modalidad';

    protected $fillable = [
        'nombre',
        'codigo',
        'estado',
        'id_usuario'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    public function requisitosDocumento()
    {
        return $this->belongsToMany(RequisitoDocumento::class, 'requisito_modalidad', 'id_modalidad', 'id_requisito_documento')
            ->withTimestamps();
    }
}
