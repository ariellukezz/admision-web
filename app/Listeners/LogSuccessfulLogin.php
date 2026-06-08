<?php

namespace App\Listeners;

use App\Jobs\ProcessAuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if (!config('audit.enabled')) {
            return;
        }

        $user = $event->user;

        if (!($user instanceof User)) {
            return;
        }

        $method = session('auth_method', 'email');
        session()->forget('auth_method');

        ProcessAuditLog::dispatch([
            'user_id' => $user->id,
            'is_revisor' => $user->id_rol == 2,
            'model_type' => User::class,
            'model_id' => $user->id,
            'action' => 'login',
            'old_values' => null,
            'new_values' => ['method' => $method, 'guard' => $event->guard],
            'description' => "Inicio de sesión ({$method})",
            'target_user_id' => null,
            'ip' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'id_proceso' => $user->id_proceso,
        ]);
    }
}
