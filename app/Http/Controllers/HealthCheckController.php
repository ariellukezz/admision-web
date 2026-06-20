<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Health Check', description: 'Estado del sistema')]
class HealthCheckController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Estado del sistema',
        description: 'Verifica el estado de la base de datos, almacenamiento y caché',
        tags: ['Health Check'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Sistema saludable',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                new OA\Property(property: 'checks', properties: [
                    new OA\Property(property: 'database', type: 'string', example: 'ok'),
                    new OA\Property(property: 'storage', type: 'string', example: 'ok'),
                ], type: 'object'),
                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
            ],
        ),
    )]
    #[OA\Response(
        response: 503,
        description: 'Sistema degradado',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'degraded'),
                new OA\Property(property: 'checks', type: 'object'),
                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
            ],
        ),
    )]
    public function __invoke()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage'  => $this->checkStorage(),
        ];

        $healthy = !in_array(false, $checks, true);

        return response()->json([
            'status'    => $healthy ? 'ok' : 'degraded',
            'checks'    => array_map(fn ($v) => $v ? 'ok' : 'fail', $checks),
            'timestamp' => now()->toISOString(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::select('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkStorage(): bool
    {
        try {
            return is_writable(storage_path('app'));
        } catch (\Throwable) {
            return false;
        }
    }
}
