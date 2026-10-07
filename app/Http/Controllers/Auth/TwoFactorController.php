<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor)
    {
    }

    public function show(Request $request): Response|RedirectResponse
    {
        $userId = $request->session()->get('two_factor:user_id');

        if (!$userId || Auth::check()) {
            return redirect('/login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor:user_id');

        if (!$userId || Auth::check()) {
            return redirect('/login');
        }

        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget('two_factor:user_id');

            return redirect('/login')->withErrors(['email' => 'Sesión expirada, inicia sesión nuevamente.']);
        }

        $result = $this->twoFactor->verifyCode($user, $request->input('code'));

        if (!$result['ok']) {
            return back()->withErrors(['code' => $result['message']]);
        }

        $request->session()->forget('two_factor:user_id');

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->id_rol == 7) { return redirect('/calificacion'); }
        if ($user->id_rol == 6) { return redirect('/simulacro'); }
        if ($user->id_rol == 1) { return redirect('/admin/dashboard'); }
        if ($user->id_rol == 2) { return redirect('/revisor'); }
        if ($user->id_rol == 3) { return redirect('/segundas'); }
        if ($user->id_rol == 8) { return redirect('/postulante/dashboard?seleccionar_proceso=1'); }

        return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor:user_id');

        if (!$userId || Auth::check()) {
            return redirect('/login');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect('/login');
        }

        try {
            $code = $this->twoFactor->generateCode($user);
            $this->twoFactor->sendCode($user, $code);
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'No se pudo enviar el correo: ' . $e->getMessage()]);
        }

        return back()->with('success', 'Se envió un nuevo código a tu correo.');
    }
}
