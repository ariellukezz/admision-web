<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'rbac_permissions';

    protected $fillable = [
        'module_id',
        'view_id',
        'action_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function view()
    {
        return $this->belongsTo(View::class, 'view_id');
    }

    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rbac_role_permissions', 'permission_id', 'role_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'rbac_user_permissions', 'permission_id', 'user_id')
            ->withPivot('type', 'status')
            ->withTimestamps();
    }

    /**
     * Devuelve el código del permiso en formato "viewCode.actionCode".
     */
    public function getCodeAttribute(): string
    {
        return $this->view?->code . '.' . $this->action?->code;
    }
}
