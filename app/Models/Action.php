<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    public $timestamps = false;

    protected $table = 'rbac_actions';

    protected $fillable = [
        'code',
        'description',
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'action_id');
    }
}
