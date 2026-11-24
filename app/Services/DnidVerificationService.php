<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class DnidVerificationService
{
    private const SESSION_KEY = 'dnid.verificacion';

    private const STATE_COOKIE = 'dnid_login_state';

    public function start(Request $request, string $area): array
    {
        $state = Str::random(48);
        $verifier = Str::random(64);
        $challenge = rtrim(strtr(
            base64_encode(hash('sha256', $verifier, true)),
            '+/',
            '-_',
        ), '=');

        try {
            $response = $this->providerGet('/login/dnid', [
                'client_id' => config('dnid.client_id'),
                'redirect_uri' => config('dnid.callback_url'),
                'state' => $state,
                'code_challenge' => $challenge,
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudo conectar con el servicio DNId.', 502, $exception);
        }

        if ($response->status() !== 303) {
            throw new RuntimeException('No se pudo iniciar la verificación DNId.', 502);
        }

        $providerState = $this->queryParameter((string) $response->header('Location'), 'state');
        $providerCookie = $this->stateCookie($response);

        if (
            ! is_string($providerState)
            || ! preg_match('/^[A-Za-z0-9_-]{43,128}$/D', $providerState)
            || ! $this->isValidProviderCookie($providerCookie)
        ) {
            throw new RuntimeException('El servicio DNId devolvió una respuesta inválida.');
        }

        $request->session()->put(self::SESSION_KEY, [
            'area' => $area,
            'state' => $state,
            'verifier' => $verifier,
            'provider_state' => $providerState,
            'provider_cookie' => $providerCookie,
        ]);

        try {
            $requestResponse = $this->providerGet('/auth/dnid/request', [
                'state' => $providerState,
            ], $providerCookie);
        } catch (ConnectionException $exception) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('No se pudo obtener el código QR de DNId.', 502, $exception);
        }

        if ($requestResponse->failed()) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('No se pudo obtener el código QR de DNId.', 502);
        }

        $data = $this->responseJson($requestResponse);
        $qrPngDataUrl = is_array($data) ? ($data['qr_png_data_url'] ?? null) : null;
        $deepLink = is_array($data) ? ($data['deep_link'] ?? null) : null;
        $expiresAt = is_array($data) ? ($data['expires_at_epoch_ms'] ?? null) : null;

        if (
            ! is_array($data)
            || ! is_string($qrPngDataUrl)
            || ! str_starts_with($qrPngDataUrl, 'data:image/png;base64,')
            || strlen($qrPngDataUrl) > 2_000_000
            || ! is_string($deepLink)
            || ! str_starts_with($deepLink, 'openid4vp://')
            || strlen($deepLink) > 4096
            || ! is_numeric($expiresAt)
            || (int) $expiresAt <= 0
        ) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('El servicio DNId devolvió un código QR inválido.');
        }

        return [
            'qr_png_data_url' => $qrPngDataUrl,
            'deep_link' => $deepLink,
            'expires_at_epoch_ms' => (int) $expiresAt,
        ];
    }

    public function status(Request $request, string $expectedArea): array
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (
            ! is_array($pending)
            || ($pending['area'] ?? null) !== $expectedArea
            || ! is_string($pending['state'] ?? null)
            || ! is_string($pending['verifier'] ?? null)
            || ! is_string($pending['provider_state'] ?? null)
            || ! $this->isValidProviderCookie($pending['provider_cookie'] ?? null)
        ) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('La solicitud de verificación no existe o venció.', 410);
        }

        try {
            $response = $this->providerGet('/auth/dnid/status', [
                'state' => $pending['provider_state'],
            ], $pending['provider_cookie']);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudo consultar el estado de DNId.', 502, $exception);
        }

        $data = $this->responseJson($response);
        $status = is_array($data) ? ($data['status'] ?? null) : null;

        if ($response->failed()) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException(
                'No se pudo completar la verificación DNId.',
                $response->serverError() ? 502 : 400,
            );
        }

        if ($status === 'PENDING') {
            return ['status' => 'PENDING'];
        }

        if ($status === 'EXPIRED') {
            $request->session()->forget(self::SESSION_KEY);

            return [
                'status' => 'EXPIRED',
                'message' => 'El código QR venció. Solicite uno nuevo.',
            ];
        }

        if ($status !== 'SUBMITTED') {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('El servicio DNId devolvió un estado inválido.');
        }

        try {
            $callbackResponse = $this->providerGet('/auth/dnid/callback', [
                'state' => $pending['provider_state'],
            ], $pending['provider_cookie']);
        } catch (ConnectionException $exception) {
            $request->session()->forget(self::SESSION_KEY);

            throw new RuntimeException('No se pudo completar la verificación DNId.', 502, $exception);
        }

        $code = $this->authorizationCode($callbackResponse, $pending['state']);
        $request->session()->forget(self::SESSION_KEY);

        if (! is_string($code)) {
            throw new RuntimeException('No se pudo validar la respuesta de DNId.');
        }

        $identity = $this->redeemCode($code, $pending['verifier']);

        Log::info('Verificación DNId completada', ['area' => $expectedArea]);

        return [
            'status' => 'COMPLETED',
            'identity' => $identity,
            'verified_at' => now()->toIso8601String(),
        ];
    }

    private function redeemCode(string $code, string $verifier): array
    {
        try {
            $response = Http::timeout(config('dnid.timeout'))
                ->acceptJson()
                ->asJson()
                ->post(rtrim((string) config('dnid.auth_url'), '/').'/oauth/token', [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('dnid.client_id'),
                    'code' => $code,
                    'redirect_uri' => config('dnid.callback_url'),
                    'code_verifier' => $verifier,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('No se pudo conectar con el servicio DNId.', 502, $exception);
        }

        if ($response->failed()) {
            Log::warning('DNId verification code exchange was rejected', [
                'status' => $response->status(),
            ]);

            throw new RuntimeException(
                'No se pudo validar la credencial DNId.',
                $response->serverError() ? 502 : 400,
            );
        }

        $data = $this->responseJson($response);

        if (
            ! is_array($data)
            || ($data['verified'] ?? false) !== true
            || ! is_array($data['identity'] ?? null)
        ) {
            throw new RuntimeException('El servicio DNId no confirmó una identidad verificada.');
        }

        $identity = $data['identity'];
        $dni = $identity['dni'] ?? null;

        if (! is_string($dni) || ! preg_match('/^\d{8}$/D', $dni)) {
            throw new RuntimeException('El servicio DNId devolvió una identidad inválida.');
        }

        $nombres = $this->text($identity['given_names'] ?? null);
        $apellidos = $this->text($identity['family_names'] ?? null);
        $nombreCompleto = $this->text($identity['full_name'] ?? null);

        return [
            'dni' => $dni,
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'nombre_completo' => $nombreCompleto ?: trim($nombres.' '.$apellidos),
        ];
    }

    private function authorizationCode(ClientResponse $response, string $expectedState): ?string
    {
        if ($response->status() !== 303) {
            return null;
        }

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

        $code = $query['code'] ?? null;

        if (is_string($code) && preg_match('/^[A-Za-z0-9_-]{20,128}$/D', $code)) {
            return $code;
        }

        return null;
    }

    private function text(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return Str::limit(trim($value), 200, '');
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
            ->get(rtrim((string) config('dnid.auth_url'), '/').$path, $query);
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
}
