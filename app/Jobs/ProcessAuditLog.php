<?php

namespace App\Jobs;

use App\Models\AuditTrail;
use App\Models\RevisorAlias;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public bool $deleteWhenMissingModels = true;

    protected array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $userId = $this->payload['user_id'] ?? null;
        $procesoId = $this->payload['id_proceso'] ?? null;
        $alias = null;

        // Resolver alias si es revisor (id_rol == 2)
        if ($userId && $procesoId && ($this->payload['is_revisor'] ?? false)) {
            $alias = RevisorAlias::getAlias($userId, $procesoId);
        }

        AuditTrail::create([
            'user_id' => $userId,
            'alias' => $alias ?? ($this->payload['alias'] ?? null),
            'model_type' => $this->payload['model_type'],
            'model_id' => $this->payload['model_id'],
            'action' => $this->payload['action'],
            'old_values' => $this->payload['old_values'] ?? null,
            'new_values' => $this->payload['new_values'] ?? null,
            'description' => $this->payload['description'] ?? null,
            'target_user_id' => $this->payload['target_user_id'] ?? null,
            'ip' => $this->payload['ip'] ?? null,
            'user_agent' => $this->payload['user_agent'] ?? null,
            'id_proceso' => $procesoId,
        ]);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
