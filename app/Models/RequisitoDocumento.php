<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoDocumento extends Model
{
    use HasFactory;

    protected $table = 'requisito_documento';

    protected $fillable = [
        'nombre',
        'obligatorio',
        'orden',
        'estado',
        'id_usuario',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'estado' => 'boolean',
    ];

    public function modalidades()
    {
        return $this->belongsToMany(Modalidad::class, 'requisito_modalidad', 'id_requisito_documento', 'id_modalidad')
            ->withTimestamps();
    }

    public function programas()
    {
        return $this->belongsToMany(Programa::class, 'requisito_programa', 'id_requisito_documento', 'id_programa')
            ->withTimestamps();
    }

    public function tiposDocumento()
    {
        return $this->belongsToMany(TipoDocumento::class, 'requisito_tipo_documento', 'id_requisito_documento', 'id_tipo_documento')
            ->withTimestamps();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function procesoRequisitos()
    {
        return $this->hasMany(ProcesoRequisito::class, 'id_requisito_documento');
    }
}
