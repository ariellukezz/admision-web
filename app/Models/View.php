<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;

    protected $table = 'rbac_views';

    protected $fillable = [
        'module_id',
        'code',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'view_id');
    }
}
