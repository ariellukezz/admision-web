<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sancionado extends Model
{
    use HasFactory;

    protected $table = 'sancionados';

    protected $filable = ['dni','paterno','materno','motivo','observacion'];
    
}
