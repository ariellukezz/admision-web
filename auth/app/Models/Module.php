<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'system_id',
        'code', 
        'name',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function system()
    {
        return $this->belongsTo(System::class);
    }
}