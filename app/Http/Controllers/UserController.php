<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::paginate()
        ]);
    }

    public function registro(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'name' => 'nullable|string|max:255',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'id_rol' => 8,
            ]);

            $token = $user->createToken('auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'id_rol' => $user->id_rol,
                    'foto' => $user->foto,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function loginApp(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)
                ->first();

            if (!$user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Correo no registrado'
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {

                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth')
                ->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->name,
                    'email' => $user->email,
                    'id_rol' => $user->id_rol,
                    'foto' => $user->foto,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recuperarPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)
                ->first();

            if (!$user) {

                return response()->json([
                    'success' => false,
                    'message' => 'El correo no está registrado'
                ], 404);
            }

            $codigo = rand(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => Hash::make($codigo),
                    'created_at' => now(),
                ]
            );

            Mail::html("
                <div style='font-family: Arial, sans-serif; padding:30px;'>

                    <h2 style='color:#111827; margin-bottom:20px;'>
                        Recuperación de contraseña
                    </h2>

                    <p style='font-size:16px;'>
                        Hola <strong>{$user->name}</strong>,
                    </p>

                    <p style='font-size:15px; color:#374151;'>
                        Usa el siguiente código para recuperar tu contraseña:
                    </p>

                    <div style='
                        margin:30px 0;
                        background:#f3f4f6;
                        border-radius:12px;
                        padding:20px;
                        text-align:center;
                    '>

                        <span style='
                            font-size:36px;
                            font-weight:bold;
                            letter-spacing:10px;
                            color:#2563eb;
                        '>
                            {$codigo}
                        </span>

                    </div>

                    <p style='color:#6b7280; font-size:14px;'>
                        Este código expirará en 10 minutos.
                    </p>

                    <p style='color:#6b7280; font-size:14px;'>
                        Si no solicitaste este cambio, ignora este mensaje.
                    </p>

                </div>
            ", function ($message) use ($user) {

                $message->to($user->email)
                    ->subject('Código de recuperación');
            });

            return response()->json([
                'success' => true,
                'message' => 'Código enviado al correo'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function restablecerPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
                'codigo' => 'required|digits:6',
                'password' => 'required|min:8|confirmed',
            ]);

            $reset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$reset) {

                return response()->json([
                    'success' => false,
                    'message' => 'Código inválido'
                ], 400);
            }

            if (now()->diffInMinutes($reset->created_at) > 10) {

                return response()->json([
                    'success' => false,
                    'message' => 'El código expiró'
                ], 400);
            }

            if (!Hash::check($request->codigo, $reset->token)) {

                return response()->json([
                    'success' => false,
                    'message' => 'Código incorrecto'
                ], 400);
            }

            $user = User::where('email', $request->email)
                ->first();

            if (!$user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
