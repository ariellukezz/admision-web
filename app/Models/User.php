<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Observers\GlobalObserver;

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
        'cellular',
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
    
}