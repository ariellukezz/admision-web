<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    public function getDepartamentos() //all
    {
        return $this->all();
    }

    protected $table = "departamento";

    protected $fillable = [
        'codigo',
        'nombre',
        'cod_pais'
    ];


}
