<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ide extends Model
{
    use HasFactory;

    protected $table = 'ides';

    protected $fillable = [
        'camp1',
        'camp2',
        'camp3',
        'camp4',
        'litho',
        'tipo',
        'dni',
        'aula'
    ];
}
