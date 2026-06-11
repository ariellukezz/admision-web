<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoAudit extends Model
{
    use HasFactory;

    protected $table = 'documento_audit';

    protected $fillable = [
        'user_id',
        'documento_id',
        'action',
        'ip',
        'user_agent',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
