<?php
namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Model;

class ColumnSelection extends Model
{
    protected $table = 'selecciones_columnas';

    protected $fillable = [
        'usuario_id',
        'nombre_tabla',
        'columnas_seleccionadas',
        'hash_columnas'
    ];

    protected $casts = [
        'columnas_seleccionadas' => 'array'
    ];

    public function filterGroups()
    {
        return $this->hasMany(FilterGroup::class, 'seleccion_id', 'id');
    }
}
