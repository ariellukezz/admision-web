<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('email', $googleUser->email)
                ->first();

            if (!$user) {

                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                    'password' => bcrypt(Str::random(24)),
                    'id_rol' => 8,
                ]);

            } else {

                $user->update([
                    'google_id' => $googleUser->id,
                    'foto' => $googleUser->avatar,
                ]);
            }

            $token = $user->createToken('auth')
                ->plainTextToken;

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

        } catch (\Exception $e) {

            $url = 'unapadmisionapp://oauth/google-error?' . http_build_query([

                'success' => false,

                'error' => $e->getMessage()
            ]);

            return redirect($url);
        }
    }


}