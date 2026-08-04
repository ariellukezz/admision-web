<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Cargo;

class AsignacionPersonal extends Model
{
    protected $table = 'asignacion_personal';

    public $timestamps = true;

    protected $fillable = [
        'id_distribucion',
        'id_participante',
        'id_cargo',
        'id_ambiente',
        'id_aula_gestion',
        'id_distribucion_detalle',
        'turno',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'id_distribucion' => 'integer',
        'id_participante' => 'integer',
        'id_cargo' => 'integer',
        'id_ambiente' => 'integer',
        'id_aula_gestion' => 'integer',
        'id_distribucion_detalle' => 'integer',
    ];

    public function distribucion(): BelongsTo
    {
        return $this->belongsTo(DistribucionAmbiente::class, 'id_distribucion');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Calificacion\Models\Participante::class, 'id_participante');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    public function ambiente(): BelongsTo
    {
        return $this->belongsTo(Ambiente::class, 'id_ambiente');
    }

    public function aulaGestion(): BelongsTo
    {
        return $this->belongsTo(AulaGestion::class, 'id_aula_gestion');
    }

    public function detalleDistribucion(): BelongsTo
    {
        return $this->belongsTo(DistribucionAmbienteDetalle::class, 'id_distribucion_detalle');
    }
}
