<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemApiToken extends Model
{
    use HasFactory;

    protected $table = 'system_api_tokens';

    protected $fillable = [
        'system_id',
        'route_id',
        'api_token',
        'status'
    ];

}
