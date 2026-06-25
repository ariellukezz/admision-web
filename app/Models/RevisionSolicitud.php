<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionSolicitud extends Model
{
    use HasFactory;

    protected $table = 'revision_solicitudes';

    protected $casts = [
        'datos_citacion' => 'array',
        'documentos_verificados' => 'array',
        'documentos_pendientes' => 'array',
        'documentos_seleccionados' => 'array',
        'apto' => 'boolean',
        'solicitada_at' => 'datetime',
        'iniciada_at' => 'datetime',
        'finalizada_at' => 'datetime',
        'apto_at' => 'datetime',
    ];

    protected $fillable = [
        'id_postulante',
        'id_proceso',
        'id_modalidad',
        'id_programa',
        'veces',
        'solicitada_at',
        'iniciada_at',
        'finalizada_at',
        'revisor_id',
        'apto',
        'apto_at',
        'datos_citacion',
        'documentos_verificados',
        'documentos_pendientes',
        'documentos_seleccionados',
        'estado',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }
}
