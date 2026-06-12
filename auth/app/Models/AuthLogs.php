<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthLogs extends Model
{
    protected $table = 'auth_logs';
    protected $fillable = [
        'user_id', 'actor_id', 'action', 'ip_address', 'user_agent', 'details'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
