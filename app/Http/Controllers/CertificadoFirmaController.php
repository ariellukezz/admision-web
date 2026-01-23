<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class CertificadoFirmaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $activo = $request->input('activo', null);
        $perPage = $request->input('per_page', 50);

        $query = DB::table('certificados_firma');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('dni', 'LIKE', "%{$search}%")
                  ->orWhere('usuario', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($activo !== null) {
            $query->where('activo', $activo);
        }

        $certificados = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $certificados
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validated = $request->validate([
                'dni' => 'required|string|max:20|unique:certificados_firma,dni',
                'usuario' => 'required|string|max:255',
                'departamento' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'password_p12' => 'required|string|min:6',
                'valid_days' => 'nullable|integer|min:1|max:3650',
            ]);

            try {
                DB::table('certificados_firma')->count();
            } catch (\Exception $e) {
                throw new \Exception('La tabla de certificados no está configurada correctamente');
            }

            // Llamar a la API Python para generar el certificado real
            $pythonApiUrl = env('PYTHON_API_URL', 'https://test-admision.unap.edu.pe/service_firma');

            $response = Http::timeout(60)->post("{$pythonApiUrl}/certificados/generar/", [
                'dni' => $request->dni,
                'usuario' => $request->usuario,
                'departamento' => $request->departamento,
                'email' => $request->email,
                'password_p12' => $request->password_p12,
                'valid_days' => $request->input('valid_days', 365),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error al generar el certificado digital: ' . $response->body());
            }

            $pythonResult = $response->json();

            if ($pythonResult['status'] !== 'ok') {
                throw new \Exception($pythonResult['mensaje'] ?? 'Error desconocido de la API Python');
            }

            // Preparar datos para MySQL
            $now = Carbon::now();
            $validDays = $request->input('valid_days', 365);
            $fechaExpiracion = $now->copy()->addDays($validDays);

            $certificadoData = [
                'dni' => $request->dni,
                'usuario' => $request->usuario,
                'departamento' => $request->departamento,
                'email' => $request->email,
                'password_p12' => $request->password_p12,
                'valid_days' => $validDays,
                'activo' => 1,
                'fecha_creacion' => $now,
                'fecha_expiracion' => $fechaExpiracion,
                'ruta_p12' => "/app/certificados/usuarios/{$request->dni}.p12",
                'created_at' => $now,
                'updated_at' => $now,
            ];

            DB::beginTransaction();

            try {
                $id = DB::table('certificados_firma')->insertGetId($certificadoData);

                DB::commit();

            } catch (\Exception $dbError) {
                DB::rollBack();
                throw new \Exception('Error al guardar en la base de datos: ' . $dbError->getMessage());
            }

            $certificado = DB::table('certificados_firma')->where('id', $id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Certificado digital generado y guardado exitosamente',
                'data' => $certificado,
                'api_response' => $pythonResult
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el certificado: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $certificado = DB::table('certificados_firma')->where('id', $id)->first();

        if (!$certificado) {
            return response()->json([
                'success' => false,
                'message' => 'Credencial no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $certificado
        ]);
    }

    public function update(Request $request, $id)
    {
        $certificado = DB::table('certificados_firma')->where('id', $id)->first();

        if (!$certificado) {
            return response()->json([
                'success' => false,
                'message' => 'Credencial no encontrada'
            ], 404);
        }

        $request->validate([
            'usuario' => 'sometimes|string|max:255',
            'departamento' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255',
            'password_p12' => 'sometimes|string|min:6',
            'activo' => 'sometimes|boolean',
        ]);

        $updateData = [
            'updated_at' => Carbon::now()
        ];

        if ($request->has('usuario')) {
            $updateData['usuario'] = $request->usuario;
        }

        if ($request->has('departamento')) {
            $updateData['departamento'] = $request->departamento;
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('password_p12')) {
            $updateData['password_p12'] = $request->password_p12;
        }

        if ($request->has('activo')) {
            $updateData['activo'] = $request->activo ? 1 : 0;
        }

        DB::table('certificados_firma')->where('id', $id)->update($updateData);

        $certificadoActualizado = DB::table('certificados_firma')->where('id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Credencial actualizada exitosamente',
            'data' => $certificadoActualizado
        ]);
    }

    public function destroy($id)
    {
        $certificado = DB::table('certificados_firma')->where('id', $id)->first();

        if (!$certificado) {
            return response()->json([
                'success' => false,
                'message' => 'Credencial no encontrada'
            ], 404);
        }

        // Eliminar físicamente (o cambiar a desactivar si prefieres)
        DB::table('certificados_firma')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Credencial eliminada exitosamente'
        ]);
    }
}
