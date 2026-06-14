<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    public $table = 'rbac_role_permissions';

    protected $fillable = [
        'role_id',
        'permission_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
