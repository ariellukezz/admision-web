<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'audit_trail';

    protected $fillable = [
        'user_id',
        'alias',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'description',
        'target_user_id',
        'ip',
        'user_agent',
        'id_proceso',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTargetUser($query, int $targetUserId)
    {
        return $query->where('target_user_id', $targetUserId);
    }

    public function scopeForModel($query, string $type, ?int $id = null)
    {
        $query->where('model_type', $type);
        if ($id !== null) {
            $query->where('model_id', $id);
        }
        return $query;
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForProceso($query, int $procesoId)
    {
        return $query->where('id_proceso', $procesoId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
