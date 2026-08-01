<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SorteoSeleccionado extends Model
{
    use HasFactory;

    protected $table = 'sorteo_seleccionados';

    protected $fillable = [
        'id_sorteo',
        'id_participante',
        'id_cargo',
        'id_usuario',
        'es_manual',
        'observacion',
        'observado',
        'anulado',
        'motivo_anulacion',
    ];

    protected $casts = [
        'observado' => 'boolean',
        'anulado'   => 'boolean',
        'es_manual' => 'boolean',
    ];

    public function sorteo()
    {
        return $this->belongsTo(Sorteo::class, 'id_sorteo');
    }

    public function participante()
    {
        return $this->belongsTo(ParticipantePersonal::class, 'id_participante');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
