<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RevisorAlias extends Model
{
    use HasFactory;

    protected $table = 'revisor_aliases';

    protected $fillable = ['user_id', 'id_proceso', 'alias'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el alias existente para un usuario en un proceso,
     * o crea uno nuevo con numeración automática.
     */
    public static function getAlias(int $userId, int $procesoId): string
    {
        $existing = static::where('user_id', $userId)
            ->where('id_proceso', $procesoId)
            ->first();

        if ($existing) {
            return $existing->alias;
        }

        $maxNumber = static::where('id_proceso', $procesoId)
            ->selectRaw('CAST(SUBSTRING(alias, 9) AS UNSIGNED) as num')
            ->orderByDesc(DB::raw('CAST(SUBSTRING(alias, 9) AS UNSIGNED)'))
            ->value('num');

        $nextNumber = ($maxNumber ?? 0) + 1;
        $alias = 'Revisor ' . $nextNumber;

        static::create([
            'user_id' => $userId,
            'id_proceso' => $procesoId,
            'alias' => $alias,
        ]);

        return $alias;
    }
}
