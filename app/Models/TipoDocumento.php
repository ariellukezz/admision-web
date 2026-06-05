<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    use HasFactory;

    protected $table = 'tipo_documento';

    protected $fillable = [
        'nombre',
        'codigo',
    ];

    public function requisitosDocumento()
    {
        return $this->belongsToMany(RequisitoDocumento::class, 'requisito_tipo_documento', 'id_tipo_documento', 'id_requisito_documento')
            ->withTimestamps();
    }
}
