<?php

namespace App\Modules\Pruebas\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BasePruebaController extends Controller
{
    protected function successResponse($data = null, string $message = '', int $status = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(string $message, int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
