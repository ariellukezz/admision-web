<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'dni',
        'nombres',
        'paterno',
        'materno',
        'email',
        'celular',
        'foto',
        'password',
        'role_id',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password', 
        'remember_token',
        'email_verified_at'
    ];

    protected $appends = ['foto_url'];


    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }
        
        return asset('storage/' . $this->foto);
    }

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'systems_users', 'user_id', 'system_id')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withTimestamps()
                    ->dd();
    }
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')
                    ->withPivot('type', 'status')
                    ->withTimestamps();
    }

    public function authLogs()
    {
        return $this->hasMany(AuthLog::class, 'user_id');
    }

    public function authSessions()
    {
        return $this->hasMany(AuthSession::class, 'user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
