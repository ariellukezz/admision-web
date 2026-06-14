<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['name','guard_name'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'rbac_role_permissions', 'role_id', 'permission_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}
