<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documento';

    protected $fillable = [
        'codigo',
        'nombre',
        'id_postulante',
        'id_tipo_documento',
        'estado',
        'url',
        'numero',
        'observacion',
        'extension',
        'mime',
        'size',
        'path',
        'hash',
        'is_deleted',
        'version',
        'verificado',
        'id_usuario',
        'apto_revision',
        'valido',
        'fecha_caducidad',
        'id_revisor',
        'observacion_revisor',
        'revisado_at',
        'validado_at',
        'seleccionado',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'size' => 'integer',
        'version' => 'integer',
        'verificado' => 'integer',
        'estado' => 'integer',
        'apto_revision' => 'boolean',
        'valido' => 'boolean',
        'seleccionado' => 'boolean',
        'fecha_caducidad' => 'date',
        'revisado_at' => 'datetime',
        'validado_at' => 'datetime',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'id_tipo_documento');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'id_revisor');
    }

    public function audits()
    {
        return $this->hasMany(DocumentoAudit::class, 'documento_id');
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
}
