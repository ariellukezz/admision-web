<?php
namespace App\Modules\Calificacion\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'aula',
        'id_archivo'
    ];

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(ArchivoLectura::class, 'id_archivo');
    }
}
