<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionUser extends Model
{
    public $table = 'rbac_user_permissions';

    protected $fillable = [
        'user_id',
        'permission_id',
        'type',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
