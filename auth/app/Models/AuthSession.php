<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthSession extends Model
{
    protected $table = 'auth_sessions';
    protected $fillable = [
        'user_id', 'system_id', 'token', 'last_activity', 'expired_at'
    ];

    protected $dates = ['last_activity', 'expired_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }
}
