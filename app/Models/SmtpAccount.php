<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpAccount extends Model
{
    protected $table = 'smtp_accounts';

    protected $fillable = [
        'name',
        'mailer',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'is_active',
        'is_default',
        'error_message',
        'error_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'port' => 'integer',
        'error_at' => 'datetime',
    ];
}
