<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class System extends Model
{
    protected $table = 'systems';
    protected $fillable = ['name', 'description','token'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'systems_users', 'system_id', 'user_id')
                    ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'systems_users', 'system_id', 'user_id')
                    ->wherePivot('status', true)
                    ->withTimestamps();
    }
}
