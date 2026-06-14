<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'rbac_modules';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function views()
    {
        return $this->hasMany(View::class, 'module_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}
