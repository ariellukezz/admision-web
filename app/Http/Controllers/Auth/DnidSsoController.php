<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DnidSsoController extends Controller
{
    private const SESSION_KEY = 'dnid.oauth';

    public function redirect(Request $request): RedirectResponse
    {
        $state = Str::random(48);
        $verifier = Str::random(64);
        $challenge = rtrim(strtr(
            base64_encode(hash('sha256', $verifier, true)),
            '+/',
            '-_',
        ), '=');

        $request->session()->put(self::SESSION_KEY, [
            'state' => $state,
            'verifier' => $verifier,
        ]);

        $query = http_build_query([
            'client_id' => config('dnid.client_id'),
            'redirect_uri' => config('dnid.callback_url'),
            'state' => $state,
            'code_challenge' => $challenge,
        ]);

        return redirect()->away(
            rtrim(config('dnid.auth_url'), '/').'/login/dnid?'.$query
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        $pending = $request->session()->pull(self::SESSION_KEY);
        $state = $request->query('state');

        if (
            ! is_array($pending)
            || ! is_string($pending['state'] ?? null)
            || ! is_string($pending['verifier'] ?? null)
            || ! is_string($state)
            || ! hash_equals($pending['state'], $state)
        ) {
            return $this->loginError('La respuesta de DNId no es válida.');
        }

        if ($request->filled('error')) {
            return $this->loginError('No se pudo completar la verificación DNId. Inténtelo nuevamente.');
        }

        $code = $request->query('code');
        if (! is_string($code) || ! preg_match('/^[A-Za-z0-9_-]{20,128}$/D', $code)) {
            return $this->loginError('El servicio DNId no devolvió un código válido.');
        }

        try {
            $response = Http::timeout(config('dnid.timeout'))
                ->acceptJson()
                ->asJson()
                ->post(rtrim(config('dnid.auth_url'), '/').'/oauth/token', [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('dnid.client_id'),
                    'code' => $code,
                    'redirect_uri' => config('dnid.callback_url'),
                    'code_verifier' => $pending['verifier'],
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('DNId authorization code exchange failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->loginError('No se pudo conectar con el servicio DNId.');
        }

        if ($response->failed()) {
            Log::warning('DNId authorization code was rejected', [
                'status' => $response->status(),
            ]);

            return $this->loginError('No se pudo validar el acceso DNId. Inténtelo nuevamente.');
        }

        $data = $response->json();
        if (! is_array($data) || ! is_array($data['identity'] ?? null)) {
            Log::warning('DNId authorization code returned an invalid response');

            return $this->loginError('El servicio DNId devolvió una respuesta inválida.');
        }

        $identity = $data['identity'];
        $dni = $identity['dni'] ?? null;

        if (
            ($data['verified'] ?? false) !== true
            || ! is_string($dni)
            || ! preg_match('/^\d{8}$/D', $dni)
        ) {
            Log::warning('DNId authorization code returned an invalid identity');

            return $this->loginError('El servicio DNId devolvió una identidad inválida.');
        }

        $usuario = User::where('dni', $dni)->first();

        if (! $usuario && config('dniauth.solo_usuarios_existentes')) {
            return $this->loginError(
                'Su DNI no está registrado en este sitio. Comuníquese con el administrador.'
            );
        }

        if (! $usuario) {
            $nombres = trim((string) ($identity['given_names'] ?? ''));
            $apellidos = trim((string) ($identity['family_names'] ?? ''));

            $usuario = User::create([
                'dni' => $dni,
                'name' => trim($nombres.' '.$apellidos) ?: 'Usuario DNId',
                'email' => null,
                'password' => Hash::make(Str::random(64)),
            ]);
        }

        if ((int) $usuario->estado !== 1) {
            return $this->loginError(
                'Su cuenta no está activa. Comuníquese con el administrador.'
            );
        }

        $request->session()->regenerate();
        Auth::login($usuario, remember: false);
        $request->session()->put('auth_method', 'dnid');

        Log::info('Ingreso con DNId', ['user_id' => $usuario->id]);

        return redirect()->to($this->redirectFor($usuario));
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect('/login')->with('status', $message);
    }

    private function redirectFor(User $usuario): string
    {
        return match ($usuario->id_rol) {
            7 => '/calificacion',
            6 => '/simulacro',
            1 => '/admin/dashboard',
            2 => '/revisor',
            3 => '/segundas',
            8 => '/postulante/dashboard?seleccionar_proceso=1',
            default => config('dniauth.redirect_after_login'),
        };
    }
}
