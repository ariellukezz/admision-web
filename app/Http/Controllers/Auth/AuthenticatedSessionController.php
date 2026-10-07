<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        session(['auth_method' => 'email']);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Credenciales incorrectas.',
            ]);
        }

        $user = Auth::user();
        if ($user->estado !== 1) {
            Auth::logout();
            return redirect('/login')->withErrors([
                'password' => 'Su cuenta no está activa',
            ]);
        }

        if ($user->two_factor_enabled) {
            Auth::logout();
            $request->session()->put('two_factor:user_id', $user->id);

            try {
                $twoFactor = app(\App\Services\TwoFactorService::class);
                $code = $twoFactor->generateCode($user);
                $twoFactor->sendCode($user, $code);
            } catch (\Exception $e) {
                return redirect('/login')->withErrors([
                    'email' => 'No se pudo enviar el código de verificación: ' . $e->getMessage(),
                ]);
            }

            return redirect('/2fa/challenge');
        }

        $request->session()->regenerate();

        if ($user->id_rol == 7) { return redirect('/calificacion'); }
        if ($user->id_rol == 6) { return redirect('/simulacro'); }
        if ($user->id_rol == 1) { return redirect('/admin/dashboard'); } 
        if ($user->id_rol == 2) { return redirect('/revisor'); }
        if ($user->id_rol == 3) { return redirect('/segundas'); }
        if ($user->id_rol == 8) { return redirect('/postulante/dashboard?seleccionar_proceso=1'); }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
