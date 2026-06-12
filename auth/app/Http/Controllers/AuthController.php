<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\RabbitMQService;
use GuzzleHttp\Client;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Configuration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;



class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombres'  => 'required|string|max:255',
            'paterno'  => 'required|string|max:255',
            'materno'  => 'required|string|max:255',
            'celular'  => 'nullable|string|max:15',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'nombres'  => $request->nombres,
            'paterno'  => $request->paterno,
            'materno'  => $request->materno,
            'celular'  => $request->celular,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        try {
            $http = new Client;
            $response = $http->post(url('oauth/token'), [
                'form_params' => [
                    'grant_type'    => 'password',
                    'client_id'     => config('passport.password_client_id'),
                    'client_secret' => config('passport.password_client_secret'),
                    'username'      => $request->email,
                    'password'      => $request->password,
                    'scope'         => '',
                ],
            ]);

            $tokens = json_decode((string) $response->getBody(), true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el token',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => $user,
            'tokens'  => $tokens,
        ], 201);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'        => 'required|email',
            'password'     => 'required|string',
            'system_token' => 'required|string|exists:systems,token',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $privateKeyPath = storage_path('oauth-private.key');
        $publicKeyPath  = storage_path('oauth-public.key');

        if (!file_exists($privateKeyPath) || !file_exists($publicKeyPath)) {
            return response()->json(['message' => 'Claves JWT no encontradas'], 500);
        }

        $privateKey = InMemory::file($privateKeyPath);
        $publicKey  = InMemory::file($publicKeyPath);

        $config = Configuration::forAsymmetricSigner(
            new Sha256(),
            $privateKey,
            $publicKey
        );

        $now = new \DateTimeImmutable();
        $accessToken = $config->builder()
            ->issuedBy(env('APP_URL'))
            ->permittedFor('api_gateway')
            ->identifiedBy(Str::uuid()->toString(), true)
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('user_id', $user->id)
            ->withClaim('email', $user->email)
            ->withClaim('system_id', $request->system->id)
            ->withClaim('role_id', $user->role_id)
            ->getToken($config->signer(), $config->signingKey())
            ->toString();

        $refreshToken = Str::random(64);

        $rabbit = new RabbitMQService();
        $rabbit->publish("logs_auth", [
            "event"      => "user.login",
            "user_id"    => $user->id,
            "email"      => $user->email,
            "ip"         => $request->ip(),
            "user_agent" => $request->header('User-Agent'),
            "system_id"  => $request->system->id,
            "success"    => true,
            "timestamp"  => now()->toDateTimeString()
        ]);

        return response()->json([
            'message'       => 'Login exitoso',
            'user'          => [
                'id'      => $user->id,
                'nombres' => $user->nombres,
                'email'   => $user->email,
            ],
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => 3600
        ]);
    }


    public function me(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        $publicKeyPath = storage_path('oauth-public.key');
        if (!file_exists($publicKeyPath)) {
            return response()->json(['message' => 'Clave pública no encontrada'], 500);
        }

        $publicKey = file_get_contents($publicKeyPath);

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        [$h, $p, $s] = $parts;

        $b64url_decode = function ($input) {
            $remainder = strlen($input) % 4;
            if ($remainder) $input .= str_repeat('=', 4 - $remainder);
            return base64_decode(strtr($input, '-_', '+/'));
        };

        $signature = $b64url_decode($s);
        $signedData = $h . '.' . $p;

        $verified = openssl_verify($signedData, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($verified !== 1) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        $payloadJson = $b64url_decode($p);
        $claims = json_decode($payloadJson, true);
        if (!is_array($claims)) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        if (isset($claims['exp']) && $claims['exp'] < time()) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        $user = User::find($claims['user_id']?? null)->first();

        return response()->json([
            'user' => $user,
            'system_id' => $claims['system_id'] ?? null,
        ], 200);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        try {
            $http = new Client;
            $response = $http->post(url('oauth/token'), [
                'form_params' => [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $request->refresh_token,
                    'client_id'     => config('passport.password_client_id'),
                    'client_secret' => config('passport.password_client_secret'),
                    'scope'         => '',
                ],
            ]);

            return response()->json(json_decode((string) $response->getBody(), true));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al refrescar el token',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token no proporcionado'], 400);
        }

        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $todayKey = "blacklist:$today";
        $yesterdayKey = "blacklist:$yesterday";

        if (Redis::exists($yesterdayKey)) {
            Redis::del($yesterdayKey);
        }

        Redis::sadd($todayKey, $token);

        if (!Redis::ttl($todayKey) || Redis::ttl($todayKey) < 0) {
            Redis::expire($todayKey, 86400);
        }

        return response()->json(['message' => 'Logout exitoso'], 200);
    }


    public function verBlacklist()
    {
        $today = Carbon::now()->format('Y-m-d');
        $blacklistKey = "blacklist:$today";

        $tokens = Redis::smembers($blacklistKey);

        return response()->json([
            'fecha' => $today,
            'cantidad' => count($tokens),
            'tokens' => $tokens,
        ]);
    }

}
