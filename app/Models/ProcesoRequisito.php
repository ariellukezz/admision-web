<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcesoRequisito extends Model
{
    use HasFactory;

    protected $table = 'proceso_requisito';

    protected $fillable = [
        'id_proceso',
        'id_requisito_documento',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function proceso()
    {
        return $this->belongsTo(Proceso::class, 'id_proceso');
    }

    public function requisitoDocumento()
    {
        return $this->belongsTo(RequisitoDocumento::class, 'id_requisito_documento');
    }
}
