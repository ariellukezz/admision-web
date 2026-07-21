<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulacro extends Model
{
    use HasFactory;

    protected $table = 'simulacro';

    protected $fillable = [
        'nombre',
        'anio',
        'estado',
        'ubigeo',
        'fecha',
        'id_usuario'
    ];

    protected $casts = [
        'estado' => 'integer',
        'fecha' => 'date',
    ];

    public function examenAreas()
    {
        return $this->hasMany(\App\Modules\Calificacion\Models\ExamenSimulacro::class, 'id_simulacro');
    }

    public function archivos()
    {
        return $this->hasMany(\App\Modules\Calificacion\Models\ArchivoSimulacro::class, 'id_simulacro');
    }
}
