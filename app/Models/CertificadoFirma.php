<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificadoFirma extends Model
{
    use HasFactory;

    protected $table = 'certificados_firma';

    protected $fillable = [
        'departamento',
        'cargo',
        'password_p12',
        'estado',
        'valid_days',
        'fecha_creacion',
        'fecha_expiracion',
        'ruta_p12',
        'id_usuario'
    ];
}
