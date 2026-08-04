<?php

namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribucionAmbienteDetalle extends Model
{
    protected $table = 'distribucion_ambiente_detalles';

    public $timestamps = true;

    protected $fillable = [
        'id_distribucion',
        'id_ambiente',
        'id_aula_gestion',
        'id_aula',
        'area_nombre',
        'aula_virtual_nombre',
        'orden_ambiente',
        'codigo_asignado',
        'capacidad',
        'estudiantes_asignados',
    ];

    protected $casts = [
        'orden_ambiente' => 'integer',
        'capacidad' => 'integer',
        'estudiantes_asignados' => 'integer',
    ];

    public function distribucion(): BelongsTo
    {
        return $this->belongsTo(DistribucionAmbiente::class, 'id_distribucion');
    }

    public function ambiente(): BelongsTo
    {
        return $this->belongsTo(Ambiente::class, 'id_ambiente');
    }

    public function aulaGestion(): BelongsTo
    {
        return $this->belongsTo(AulaGestion::class, 'id_aula_gestion');
    }

    public function aulaVirtual(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'id_aula');
    }

    public function personalAsignado(): HasMany
    {
        return $this->hasMany(AsignacionPersonal::class, 'id_distribucion_detalle');
    }
}
