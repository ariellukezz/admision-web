<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\CertificadoFirma;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }



    public function getDatosUsuario()
    {
        $user = User::query()
            ->select([
                'users.dni',
                'users.name',
                'users.paterno',
                'users.materno',
                'users.email',
                'users.celular',
                'roles.id as id_rol',
                'roles.name as rol',
                'procesos.id as id_proceso',
                'procesos.nombre as proceso',
                'users.estado',
            ])
            ->join('roles', 'roles.id', '=', 'users.id_rol')
            ->join('procesos', 'procesos.id', '=', 'users.id_proceso')
            ->where('users.id', Auth::id())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }


    public function actualizarDatosUsuario(){
      $user = User::find(Auth::id());

      $user->name = request()->name;
      $user->paterno = request()->paterno;
      $user->materno = request()->materno;
      $user->celular = request()->celular;
      $user->email = request()->email;
      $user->save();

      return response()->json([ 'success' => true, 'data' => $user ]);

    }

    public function cambiarContrasenaPerfil(Request $request)
    {
        $user = User::find(Auth::id());

        if (!Hash::check($request->password_actual, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 422);
        }

        $user->password = Hash::make($request->password_nueva);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    public function actualizarEstadoFirma(Request $request)
    {
        $certificado = CertificadoFirma::where('id_usuario', Auth::id())->first();

        $certificado->estado = $request->estado ? 1 : 0;
        $certificado->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado de firma electrónica actualizado correctamente',
            'data' => $certificado
        ]);
    }


    public function crearCertificadoDigital(Request $request) {

      $request->validate([
          'usuario' => 'required|string',
          'departamento' => 'required|string',
          'email' => 'required|email',
          'rol' => 'required|string',
          'password_p12' => 'required|string|min:6',
          'valid_days' => 'nullable|integer|in:365,730,1095',
      ]);

      $pythonApiUrl = 'https://test-admision.unap.edu.pe/service_firma';

      $response = Http::timeout(60)->post("{$pythonApiUrl}/certificados/generar/", [
          'dni' => $request->dni,
          'usuario' => $request->usuario,
          'cargo' => $request->rol,
          'departamento' => $request->departamento,
          'email' => $request->email,
          'password_p12' => $request->password_p12,
          'valid_days' => $request->input('valid_days', 365),
      ]);

      if (!$response->successful()) {
          abort(502, 'Error al generar certificado en el servicio de firma');
      }

      $now = Carbon::now();
      $validDays = $request->input('valid_days', 365);

      $certificado = CertificadoFirma::where('id_usuario', Auth::id())->first();

      if ($certificado) {
          $certificado->password_p12 = $request->password_p12;
          $certificado->valid_days = $validDays;
          $certificado->fecha_expiracion = $now->copy()->addDays($validDays);
          $certificado->ruta_p12 = "/app/certificados/usuarios/{$request->dni}.p12";
          $certificado->updated_at = $now;
          $certificado->save();

          return response()->json([
              'success' => true,
              'message' => 'Certificado digital actualizado correctamente',
              'data' => $certificado
          ]);
      }else {

          $certificado = CertificadoFirma::create([
              'id_usuario' => auth()->id(),
              'dni' => $request->dni,
              'departamento' => $request->departamento,
              'cargo' => $request->rol,
              'password_p12' => $request->password_p12,
              'valid_days' => $validDays,
              'estado' => 1,
              'fecha_creacion' => $now,
              'fecha_expiracion' => $now->copy()->addDays($validDays),
              'ruta_p12' => "/app/certificados/usuarios/{$request->dni}.p12",
              'created_at' => $now,
              'updated_at' => $now,
          ]);

          return response()->json([
              'success' => true,
              'message' => 'Certificado digital creado correctamente',
              'data' => $certificado
          ]);
      }

    }

    public function getCertificadoDigital()
    {
        $certificado = CertificadoFirma::where('id_usuario', Auth::id())->first();

        return response()->json([
            'success' => true,
            'data' => $certificado
        ]);
    }

    public function getActivityLog()
    {
        $logs = ActivityLog::where('user_id', Auth::id())
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log, $index) {

                return [
                    'id' => $index + 1,
                    'title' => $this->mapTitle($log),
                    'description' => $this->mapDescription($log),
                    'time' => Carbon::parse($log->fecha)->diffForHumans(),
                    'color' => $this->mapColor($log->action),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }


    private function mapTitle($log)
    {
        if ($log->tabla === 'users' && $log->action === 'insertó') {
            return 'Usuario creado';
        }

        if ($log->tabla === 'users' && $log->action === 'actualizó') {
            return 'Perfil actualizado';
        }

        return ucfirst($log->action);
    }

    private function mapDescription($log)
    {
        return "Registro en {$log->tabla} (ID: {$log->model_id}) desde {$log->direccion}";
    }

    private function mapColor($action)
    {
        return match ($action) {
            'insertó' => 'green',
            'actualizó' => 'blue',
            'eliminó' => 'red',
            default => 'gray',
        };
    }

}
