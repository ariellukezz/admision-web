<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    use HasFactory;

    protected $table = 'postulante';

    protected $casts = [
        'tiene_revision_activa' => 'boolean',
    ];

    protected $fillable = [
        'tipo_doc',
        'nro_doc',
        'nro_doc_opcional',
        'primer_apellido',
        'segundo_apellido',
        'apellido_casada',
        'nombres',
        'sexo',
        'fec_nacimiento',
        'ubigeo_nacimiento',
        'ubigeo_residencia',
        'discapacidad',
        'tipo_discapacidad',
        'tipo_discapacidad_otro',
        'celular',
        'email',
        'estado_civil',
        'direccion',
        'anio_egreso',
        'correo_institucional',
        'cod_orcid',
        'observaciones',
        'id_colegio',
        'foto_url',
        'revisado',
        'tiene_revision_activa',
        'id_usuario',
        'carreras_previas'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'nro_doc', 'dni');
    }

    public function preinscripciones()
    {
        return $this->hasMany(Preinscripcion::class, 'id_postulante');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_postulante');
    }

    public function revisionSolicitudes()
    {
        return $this->hasMany(RevisionSolicitud::class, 'id_postulante');
    }

    public function revisionActiva()
    {
        return $this->hasOne(RevisionSolicitud::class, 'id_postulante')
            ->where('estado', '!=', 'completada')
            ->latest('id');
    }

}