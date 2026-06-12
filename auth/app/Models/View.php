<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
{
    protected $table = 'views';
    
    protected $fillable = [
        'module_id',
        'code', 
        'name',
        'description',
        'status'
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];
    
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}