<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Console\Command;

class TwoFactorTestCommand extends Command
{
    protected $signature = 'twofa:test {email} {--skip-send : No enviar correo, solo generar y verificar}';

    protected $description = 'Genera un código 2FA para un usuario, lo envía por correo y prueba la verificación (éxitos, errores y expiración)';

    public function handle(TwoFactorService $twoFactor)
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('Usuario no encontrado con ese correo.');
            return 1;
        }

        if (!$user->email) {
            $this->error('El usuario no tiene correo registrado.');
            return 1;
        }

        $this->info("Usuario: {$user->getFullNameAttribute()} | Correo: {$user->email}");

        $code = $twoFactor->generateCode($user);
        $user->refresh();
        $this->info('Código generado (hash guardado en BD): ' . substr($user->two_factor_otp_hash, 0, 30) . '...');
        $this->info('Expira en: ' . $user->two_factor_otp_expires_at);

        if (!$this->option('skip-send')) {
            try {
                $twoFactor->sendCode($user, $code);
                $this->info('Correo enviado a ' . $user->email);
            } catch (\Exception $e) {
                $this->error('Error al enviar correo: ' . $e->getMessage());
                return 1;
            }
        }

        $wrong = $twoFactor->verifyCode($user, str_pad(((int) $code + 1) % 1000000, 6, '0', STR_PAD_LEFT));
        $this->info('Verificación con código incorrecto: ' . ($wrong['ok'] ? 'ERROR (no debió pasar)' : $wrong['message']));

        $user->update(['two_factor_otp_expires_at' => now()->subMinute()]);
        $expired = $twoFactor->verifyCode($user, $code);
        $this->info('Verificación con código expirado: ' . ($expired['ok'] ? 'ERROR (no debió pasar)' : $expired['message']));

        $code2 = $twoFactor->generateCode($user);
        $ok = $twoFactor->verifyCode($user, $code2);
        $this->info('Verificación con código válido: ' . ($ok['ok'] ? 'OK - código invalidado' : $ok['message']));

        $user->refresh();
        $this->info('Hash en BD tras uso: ' . ($user->two_factor_otp_hash ?? 'NULL (invalidado)'));

        return 0;
    }
}
