<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Observers\GlobalObserver;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'dni',
        'name',
        'paterno',
        'materno',
        'email',
        'celular',
        'password',
        'id_rol',
        'id_usuario',
        'programas',
        'estado',
        'id_proceso',
        'google_id',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::observe(GlobalObserver::class);
    }


    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    public static function findByEmail(string $email)
    {
        return self::where('email', $email)->first();
    }

    public static function updateOrCreateFromGoogle($googleUser)
    {
        $user = self::where('email', $googleUser->email)->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar ?? $user->foto,
                ]);
            }
            return $user;
        }

        return null;
    }

    public function canLoginWithGoogle(): bool
    {
        return !is_null($this->email);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->paterno} {$this->materno} {$this->name}");
    }

    public function isAdmin(): bool
    {
        return $this->id_rol == 1;
    }

    public function isRevisor(): bool
    {
        return $this->id_rol == 2;
    }

    public function hasRolId(int $rolId): bool
    {
        return $this->id_rol == $rolId;
    }

    public function postulante()
    {
        return $this->hasOne(Postulante::class, 'nro_doc', 'dni');
    }

    public function procesoActual()
    {
        return $this->belongsTo(Proceso::class, 'id_proceso');
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    // ─── RBAC ───────────────────────────────────────────────

    public function rbacPermissions()
    {
        return $this->belongsToMany(Permission::class, 'rbac_user_permissions', 'user_id', 'permission_id')
            ->withPivot('type', 'status')
            ->withTimestamps();
    }

    /**
     * Devuelve todos los permisos del usuario como strings "viewCode.actionCode".
     * Combina los del rol con los overrides del usuario.
     */
    public function getAllPermissions(): Collection
    {
        // Si no tiene rol asignado, no hay permisos
        if (!$this->id_rol) {
            return collect();
        }

        // Permisos del rol (status = 1)
        $rolePermissions = Permission::whereHas('roles', function ($q) {
            $q->where('roles.id', $this->id_rol)
              ->where('rbac_role_permissions.status', true);
        })
            ->with(['view:id,code', 'action:id,code'])
            ->where('status', true)
            ->get();

        $codes = $rolePermissions->map(fn ($p) => $p->view?->code . '.' . $p->action?->code)
            ->filter()
            ->values();

        // Overrides del usuario
        $userOverrides = $this->rbacPermissions()
            ->with(['view:id,code', 'action:id,code'])
            ->wherePivot('status', true)
            ->get();

        foreach ($userOverrides as $override) {
            $code = $override->view?->code . '.' . $override->action?->code;
            if (!$code) {
                continue;
            }
            if ($override->pivot->type === 'add') {
                $codes->push($code);
            } elseif ($override->pivot->type === 'remove') {
                $codes = $codes->reject(fn ($c) => $c === $code)->values();
            }
        }

        return $codes->unique()->values();
    }

    /**
     * Verifica si el usuario tiene un permiso específico.
     */
    public function hasPermission(string $code): bool
    {
        return $this->getAllPermissions()->contains($code);
    }

    /**
     * Verifica si tiene alguno de los permisos dados.
     */
    public function hasAnyPermission(array $codes): bool
    {
        $permissions = $this->getAllPermissions();
        return collect($codes)->some(fn ($code) => $permissions->contains($code));
    }

}