<?php

namespace App\Services;

use App\Mail\CodigoTwoFactor;
use App\Models\SmtpAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorService
{
    const OTP_TTL_MINUTES = 10;
    const MAX_ATTEMPTS = 5;

    public function generateCode(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_factor_otp_hash' => Hash::make($code),
            'two_factor_otp_expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'two_factor_otp_attempts' => 0,
        ]);

        return $code;
    }

    public function sendCode(User $user, string $code): void
    {
        if (!$user->email) {
            throw new \RuntimeException('El usuario no tiene correo registrado.');
        }

        $smtp = SmtpAccount::where('is_active', true)
            ->orderByDesc('is_default')
            ->first();

        if ($smtp) {
            Config::set('mail.mailers.smtp_dynamic', [
                'transport' => $smtp->mailer,
                'host' => $smtp->host,
                'port' => $smtp->port,
                'encryption' => $smtp->encryption,
                'username' => $smtp->username,
                'password' => $smtp->password,
            ]);
            Config::set('mail.from.address', $smtp->from_address);
            Config::set('mail.from.name', $smtp->from_name);

            try {
                Mail::mailer('smtp_dynamic')
                    ->to($user->email)
                    ->send(new CodigoTwoFactor($code, $user->getFullNameAttribute()));

                return;
            } catch (\Exception $e) {
                $smtp->update([
                    'is_active' => false,
                    'error_message' => $e->getMessage(),
                    'error_at' => now(),
                ]);

                $fallback = SmtpAccount::where('is_active', true)->inRandomOrder()->first();
                if ($fallback) {
                    Config::set('mail.mailers.smtp_dynamic', [
                        'transport' => $fallback->mailer,
                        'host' => $fallback->host,
                        'port' => $fallback->port,
                        'encryption' => $fallback->encryption,
                        'username' => $fallback->username,
                        'password' => $fallback->password,
                    ]);
                    Config::set('mail.from.address', $fallback->from_address);
                    Config::set('mail.from.name', $fallback->from_name);

                    Mail::mailer('smtp_dynamic')
                        ->to($user->email)
                        ->send(new CodigoTwoFactor($code, $user->getFullNameAttribute()));

                    return;
                }

                throw $e;
            }
        }

        Mail::to($user->email)->send(new CodigoTwoFactor($code, $user->getFullNameAttribute()));
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function verifyCode(User $user, string $code): array
    {
        if (!$user->two_factor_otp_hash || !$user->two_factor_otp_expires_at) {
            return ['ok' => false, 'message' => 'No hay un código activo. Solicita uno nuevo.'];
        }

        if ($user->two_factor_otp_attempts >= self::MAX_ATTEMPTS) {
            return ['ok' => false, 'message' => 'Demasiados intentos. Solicita un nuevo código.'];
        }

        if (Carbon::now()->greaterThan($user->two_factor_otp_expires_at)) {
            return ['ok' => false, 'message' => 'El código ha expirado. Solicita uno nuevo.'];
        }

        if (!Hash::check($code, $user->two_factor_otp_hash)) {
            $user->increment('two_factor_otp_attempts');

            return ['ok' => false, 'message' => 'Código incorrecto.'];
        }

        $user->update([
            'two_factor_otp_hash' => null,
            'two_factor_otp_expires_at' => null,
            'two_factor_otp_attempts' => 0,
        ]);

        return ['ok' => true, 'message' => 'Código verificado.'];
    }
}
