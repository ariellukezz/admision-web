<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'permission_id',
        'view_id',
        'method',
        'path',
        'service_path',
        'audit',
        'auth',  
        'service',  
        'status',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

}
