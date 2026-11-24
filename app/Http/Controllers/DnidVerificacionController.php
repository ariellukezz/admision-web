<?php

namespace App\Http\Controllers;

use App\Services\DnidVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class DnidVerificacionController extends Controller
{
    public function publico(Request $request): Response
    {
        return $this->iniciar($request, 'publico');
    }

    public function admin(Request $request): Response
    {
        return $this->iniciar($request, 'admin');
    }

    public function estadoPublico(Request $request): JsonResponse
    {
        return $this->estado($request, 'publico');
    }

    public function estadoAdmin(Request $request): JsonResponse
    {
        return $this->estado($request, 'admin');
    }

    private function iniciar(Request $request, string $area): Response
    {
        $error = '';

        try {
            $data = app(DnidVerificationService::class)->start($request, $area);
        } catch (RuntimeException $exception) {
            Log::warning('DNId verification could not be started', [
                'area' => $area,
                'message' => $exception->getMessage(),
            ]);

            $data = [];
            $error = $exception->getMessage();
        }

        $component = $area === 'admin' ? 'Admin/VerificarDni' : 'VerificacionDni/Publica';

        return Inertia::render($component, [
            'area' => $area,
            'qrPngDataUrl' => $data['qr_png_data_url'] ?? null,
            'deepLink' => $data['deep_link'] ?? null,
            'expiresAtEpochMs' => $data['expires_at_epoch_ms'] ?? null,
            'initialError' => $error,
            'statusUrl' => route($area === 'admin' ? 'admin-verificar-dni-estado' : 'dnid.verificacion.estado.publico'),
            'restartUrl' => route($area === 'admin' ? 'admin-verificar-dni' : 'dnid.verificacion.publico'),
            'backUrl' => route($area === 'admin' ? 'admin-dashboard' : 'login'),
            'backLabel' => $area === 'admin' ? 'Volver al panel administrativo' : 'Volver al inicio de sesión',
        ])->toResponse($request)->header('Cache-Control', 'no-store');
    }

    private function estado(Request $request, string $area): JsonResponse
    {
        try {
            $data = app(DnidVerificationService::class)->status($request, $area);
        } catch (RuntimeException $exception) {
            Log::warning('DNId verification failed', [
                'area' => $area,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'FAILED',
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 400)->header('Cache-Control', 'no-store');
        }

        return response()->json($data)->header('Cache-Control', 'no-store');
    }
}
