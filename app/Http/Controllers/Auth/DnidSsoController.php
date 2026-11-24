<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DnidSsoController extends Controller
{
    private const SESSION_KEY = 'dnid.oauth';

    private const STATE_COOKIE = 'dnid_login_state';

    public function redirect(Request $request): Response|RedirectResponse
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

        try {
            $response = $this->providerGet('/login/dnid', [
                'client_id' => config('dnid.client_id'),
                'redirect_uri' => config('dnid.callback_url'),
                'state' => $state,
                'code_challenge' => $challenge,
            ]);
        } catch (ConnectionException $exception) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId login could not be started', [
                'message' => $exception->getMessage(),
            ]);

            return $this->loginError('No se pudo conectar con el servicio DNId.');
        }

        if ($response->status() !== 303) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId login start was rejected', [
                'status' => $response->status(),
            ]);

            return $this->loginError('No se pudo iniciar la verificación DNId.');
        }

        $providerState = $this->queryParameter((string) $response->header('Location'), 'state');
        $providerCookie = $this->stateCookie($response);

        if (
            ! is_string($providerState)
            || ! preg_match('/^[A-Za-z0-9_-]{43,128}$/D', $providerState)
            || ! $this->isValidProviderCookie($providerCookie)
        ) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId login start returned an invalid state');

            return $this->loginError('El servicio DNId devolvió una respuesta inválida.');
        }

        $pending = $request->session()->get(self::SESSION_KEY);
        $pending['provider_state'] = $providerState;
        $pending['provider_cookie'] = $providerCookie;
        $request->session()->put(self::SESSION_KEY, $pending);

        try {
            $requestResponse = $this->providerGet('/auth/dnid/request', [
                'state' => $providerState,
            ], $providerCookie);
        } catch (ConnectionException $exception) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId QR request failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->loginError('No se pudo obtener el código QR de DNId.');
        }

        if ($requestResponse->failed()) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId QR request was rejected', [
                'status' => $requestResponse->status(),
            ]);

            return $this->loginError('No se pudo obtener el código QR de DNId.');
        }

        $data = $this->responseJson($requestResponse);
        $qrPngDataUrl = is_array($data) ? ($data['qr_png_data_url'] ?? null) : null;
        $deepLink = is_array($data) ? ($data['deep_link'] ?? null) : null;
        $expiresAt = is_array($data) ? ($data['expires_at_epoch_ms'] ?? null) : null;

        if (
            ! is_array($data)
            || ! is_string($qrPngDataUrl)
            || ! str_starts_with($qrPngDataUrl, 'data:image/png;base64,')
            || ! is_string($deepLink)
            || ! str_starts_with($deepLink, 'openid4vp://')
            || strlen($deepLink) > 4096
            || ! is_numeric($expiresAt)
            || (int) $expiresAt <= 0
        ) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId QR request returned an invalid response');

            return $this->loginError('El servicio DNId devolvió un código QR inválido.');
        }

        return Inertia::render('Auth/Dnid', [
            'qrPngDataUrl' => $qrPngDataUrl,
            'deepLink' => $deepLink,
            'expiresAtEpochMs' => (int) $expiresAt,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (
            ! is_array($pending)
            || ! is_string($pending['state'] ?? null)
            || ! is_string($pending['verifier'] ?? null)
            || ! is_string($pending['provider_state'] ?? null)
            || ! $this->isValidProviderCookie($pending['provider_cookie'] ?? null)
        ) {
            $request->session()->forget(self::SESSION_KEY);

            return $this->statusFailure('La solicitud DNId no existe o venció.', 410);
        }

        if (is_string($pending['callback_url'] ?? null)) {
            return response()->json([
                'status' => 'SUBMITTED',
                'callback_url' => $pending['callback_url'],
            ]);
        }

        try {
            $response = $this->providerGet('/auth/dnid/status', [
                'state' => $pending['provider_state'],
            ], $pending['provider_cookie']);
        } catch (ConnectionException $exception) {
            Log::warning('DNId status request failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->statusFailure('No se pudo consultar el estado de DNId.', 502);
        }

        $data = $this->responseJson($response);
        $status = is_array($data) ? ($data['status'] ?? null) : null;

        if ($response->failed()) {
            $request->session()->forget(self::SESSION_KEY);
            $message = is_array($data) && is_string($data['detail'] ?? null)
                ? $data['detail']
                : 'No se pudo completar la verificación DNId.';

            return $this->statusFailure($message, $response->serverError() ? 502 : 400);
        }

        if ($status === 'PENDING') {
            return response()->json(['status' => 'PENDING']);
        }

        if ($status === 'EXPIRED') {
            $request->session()->forget(self::SESSION_KEY);

            return response()->json([
                'status' => 'EXPIRED',
                'message' => 'El código QR venció. Solicite uno nuevo.',
            ]);
        }

        if ($status !== 'SUBMITTED') {
            $request->session()->forget(self::SESSION_KEY);

            return $this->statusFailure('El servicio DNId devolvió un estado inválido.');
        }

        try {
            $callbackResponse = $this->providerGet('/auth/dnid/callback', [
                'state' => $pending['provider_state'],
            ], $pending['provider_cookie']);
        } catch (ConnectionException $exception) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId provider callback failed', [
                'message' => $exception->getMessage(),
            ]);

            return $this->statusFailure('No se pudo completar la verificación DNId.', 502);
        }

        $callbackUrl = $callbackResponse->status() === 303
            ? $this->localCallbackUrl($callbackResponse, $pending['state'])
            : null;

        if (! is_string($callbackUrl)) {
            $request->session()->forget(self::SESSION_KEY);

            Log::warning('DNId provider callback returned an invalid destination', [
                'status' => $callbackResponse->status(),
            ]);

            return $this->statusFailure('No se pudo validar la respuesta de DNId.');
        }

        $pending['callback_url'] = $callbackUrl;
        $request->session()->put(self::SESSION_KEY, $pending);

        return response()->json([
            'status' => 'SUBMITTED',
            'callback_url' => $callbackUrl,
        ]);
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
                'body' => $this->responseJson($response) ?? $response->body(),
            ]);

            return $this->loginError('No se pudo validar el acceso DNId. Inténtelo nuevamente.');
        }

        $data = $this->responseJson($response);
        if (
            ! is_array($data)
            || ($data['verified'] ?? false) !== true
            || ! is_array($data['identity'] ?? null)
        ) {
            Log::warning('DNId token exchange returned an invalid response');

            return $this->loginError('El servicio DNId devolvió una respuesta inválida.');
        }

        $identity = $data['identity'];
        $dni = $identity['dni'] ?? null;
        if (! is_string($dni) || ! preg_match('/^\d{8}$/D', $dni)) {
            Log::warning('DNId identity returned an invalid DNI');

            return $this->loginError('El servicio DNId devolvió una identidad inválida.');
        }

        $usuario = User::where('dni', $dni)->first();

        if (! $usuario && config('dniauth.solo_usuarios_existentes')) {
            return $this->loginError(
                'Su DNI no está registrado en este sitio. Comuníquese con el administrador.'
            );
        }

        if (! $usuario) {
            $nombreCompleto = trim((string) ($identity['full_name'] ?? ''));
            $nombres = trim((string) ($identity['given_names'] ?? ''));
            $apellidos = trim((string) ($identity['family_names'] ?? ''));

            $usuario = User::create([
                'dni' => $dni,
                'name' => $nombreCompleto ?: (trim($nombres.' '.$apellidos) ?: 'Usuario DNId'),
                'email' => null,
                'password' => Hash::make(Str::random(64)),
                'id_rol' => 8,
                'estado' => 1,
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

    private function responseJson(ClientResponse $response): mixed
    {
        return json_decode($response->body(), true);
    }

    private function providerGet(string $path, array $query = [], ?string $providerCookie = null): ClientResponse
    {
        $headers = $providerCookie === null
            ? []
            : ['Cookie' => self::STATE_COOKIE.'='.$providerCookie];

        return Http::timeout(config('dnid.timeout'))
            ->acceptJson()
            ->withHeaders($headers)
            ->withoutRedirecting()
            ->get(rtrim(config('dnid.auth_url'), '/').$path, $query);
    }

    private function stateCookie(ClientResponse $response): ?string
    {
        foreach ($response->cookies() as $cookie) {
            if ($cookie->getName() === self::STATE_COOKIE) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    private function isValidProviderCookie(mixed $cookie): bool
    {
        return is_string($cookie)
            && $cookie !== ''
            && strlen($cookie) <= 512
            && ! preg_match('/[\r\n]/', $cookie);
    }

    private function queryParameter(string $url, string $name): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $value = $query[$name] ?? null;

        return is_string($value) ? $value : null;
    }

    private function localCallbackUrl(ClientResponse $response, string $expectedState): ?string
    {
        $location = (string) $response->header('Location');
        $expected = parse_url((string) config('dnid.callback_url'));
        $actual = parse_url($location);

        if (
            ! is_array($expected)
            || ! is_array($actual)
            || strcasecmp($expected['scheme'] ?? '', $actual['scheme'] ?? '') !== 0
            || strcasecmp($expected['host'] ?? '', $actual['host'] ?? '') !== 0
            || ($expected['port'] ?? null) !== ($actual['port'] ?? null)
            || ($expected['path'] ?? '') !== ($actual['path'] ?? '')
        ) {
            return null;
        }

        parse_str((string) ($actual['query'] ?? ''), $query);
        $state = $query['state'] ?? null;

        if (! is_string($state) || ! hash_equals($expectedState, $state)) {
            return null;
        }

        $parameters = ['state' => $state];
        $code = $query['code'] ?? null;
        $error = $query['error'] ?? null;

        if (is_string($code) && preg_match('/^[A-Za-z0-9_-]{20,128}$/D', $code)) {
            $parameters['code'] = $code;
        } elseif (is_string($error) && $error !== '' && strlen($error) <= 128) {
            $parameters['error'] = $error;
        } else {
            return null;
        }

        return route('dnid.callback', [], false).'?'.http_build_query($parameters);
    }

    private function statusFailure(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => 'FAILED',
            'message' => $message,
        ], $status);
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
