<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirigir a Google OAuth
     */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Manejar el callback de Google
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('email', $googleUser->email)
                ->first();

            if (!$user) {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(24)),
                    'id_rol' => 8, // Rol por defecto
                    'estado' => 1, // Activo por defecto
                ]);
            } else {
                // Actualizar usuario existente
                $user->update([
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                ]);
            }

            // Verificar si es para la app móvil (por el user agent o parámetro)
            $isMobile = request()->has('mobile') || 
                        str_contains(request()->userAgent() ?? '', 'unapadmisionapp');

            if ($isMobile) {
                // Respuesta para app móvil
                $token = $user->createToken('auth')->plainTextToken;

                $url = 'unapadmisionapp://oauth/google?' . http_build_query([
                    'success' => true,
                    'token' => $token,
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'foto' => $user->foto,
                    'id_rol' => $user->id_rol,
                ]);

                return redirect($url);
            } else {
                // Login para web
                session(['auth_method' => 'google']);
                Auth::login($user, true);

                // Redirigir según el rol
                return redirect()->intended($this->getRedirectPath($user));
            }

        } catch (\Exception $e) {
            // Manejar error
            if (request()->has('mobile')) {
                $url = 'unapadmisionapp://oauth/google-error?' . http_build_query([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                return redirect($url);
            }

            return redirect('/login')->withErrors([
                'email' => 'Error al iniciar sesión con Google: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener ruta de redirección según el rol del usuario
     */
    private function getRedirectPath($user)
    {
        if ($user->estado !== 1) {
            Auth::logout();
            return redirect('/login')->withErrors([
                'password' => 'Su cuenta no está activa',
            ]);
        }

        switch ($user->id_rol) {
            case 7: return '/calificacion';
            case 6: return '/simulacro';
            case 1: return '/admin/dashboard';
            case 2: return '/revisor';
            case 3: return '/segundas';
            case 8:
                return '/postulante/dashboard?seleccionar_proceso=1';
            default: return '/dashboard';
        }
    }
}