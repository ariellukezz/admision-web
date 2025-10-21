<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resp extends Model
{
    use HasFactory;

    protected $table = 'patron';
    protected $fillable = [ 'id', 'c1', 'n_lectura', 'c3', 'c4', 'c5', 'c6', 'c7',  'litho', 'tipo', 'respuestas','id_archivo'];

}
