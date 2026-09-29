<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DniAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Recibe el código que el frontend obtuvo del servicio de autenticación, lo canjea por la
 * identidad verificada y abre la sesión de Laravel.
 *
 * A partir de aquí todo es la sesión normal de Laravel: Auth::user(), middleware 'auth',
 * políticas, etc. El JWT del servicio no se usa como sesión del sitio.
 */
class DniAuthController extends Controller
{
    public function __construct(private readonly DniAuthService $dniAuth)
    {
    }

    public function callback(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'min:16', 'max:128'],
        ]);

        try {
            $identidad = $this->dniAuth->canjear($datos['codigo']);
        } catch (RuntimeException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        }

        $usuario = User::where('dni', $identidad['dni'])->first();

        if (! $usuario) {
            if (config('dniauth.solo_usuarios_existentes')) {
                Log::info('Ingreso con DNIe rechazado: DNI no registrado', ['dni' => $identidad['dni']]);

                return response()->json([
                    'mensaje' => 'Su DNI no está registrado en este sitio. Comuníquese con el administrador.',
                ], 403);
            }

            $usuario = User::create([
                'dni'      => $identidad['dni'],
                'name'     => trim($identidad['nombres'].' '.($identidad['apellidos'] ?? '')),
                // El DNIe no aporta correo. Se deja nulo si el esquema lo permite; si no,
                // use un marcador y pida el correo real en el primer ingreso.
                'email'    => null,
                // La contraseña no se usa nunca: se rellena con un valor imposible de adivinar
                // para que nadie pueda entrar por el formulario clásico con esta cuenta.
                'password' => bcrypt(Str::random(64)),
            ]);
        }

        // Igual que el ingreso con Google: solo cuentas activas.
        if ($usuario->estado !== 1) {
            Log::info('Ingreso con DNIe rechazado: cuenta inactiva', ['user_id' => $usuario->id]);

            return response()->json([
                'mensaje' => 'Su cuenta no está activa. Comuníquese con el administrador.',
            ], 403);
        }

        // Regenerar el id de sesión al autenticar corta la fijación de sesión.
        $request->session()->regenerate();
        Auth::login($usuario, remember: false);

        $request->session()->put('dnie_serial', $identidad['serial']);
        $request->session()->put('dnie_autenticado_en', now()->toIso8601String());

        Log::info('Ingreso con DNIe', ['user_id' => $usuario->id, 'dni' => $identidad['dni']]);

        return response()->json([
            'ok'          => true,
            'redirigirA'  => $this->redirigirSegunRol($usuario),
        ]);
    }

    /**
     * Misma regla que el ingreso con Google: cada rol aterriza en su panel.
     */
    private function redirigirSegunRol(User $usuario): string
    {
        return match ($usuario->id_rol) {
            7       => '/calificacion',
            6       => '/simulacro',
            1       => '/admin/dashboard',
            2       => '/revisor',
            3       => '/segundas',
            8       => '/postulante/dashboard?seleccionar_proceso=1',
            default => config('dniauth.redirect_after_login'),
        };
    }
}
