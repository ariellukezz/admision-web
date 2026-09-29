<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Canje del código de autenticación por la identidad verificada.
 *
 * Esta llamada es servidor a servidor y lleva el secreto de cliente, así que NUNCA debe
 * hacerse desde el navegador. El servicio de autenticación rechaza cualquier canje que
 * llegue con cabecera Origin, precisamente para detectar ese error.
 */
class DniAuthService
{
    /**
     * @return array{dni: string, nombres: string, apellidos: ?string, serial: string, token: string}
     *
     * @throws RuntimeException si el código no es válido o el servicio no responde.
     */
    public function canjear(string $codigo): array
    {
        $config = config('dniauth');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            throw new RuntimeException('Faltan DNIAUTH_CLIENT_ID o DNIAUTH_CLIENT_SECRET en el entorno.');
        }

        $respuesta = Http::timeout($config['timeout'])
            ->acceptJson()
            ->asJson()
            ->post(rtrim($config['url'], '/').'/api/v1/token', [
                'clientId'     => $config['client_id'],
                'clientSecret' => $config['client_secret'],
                'codigo'       => $codigo,
            ]);

        if ($respuesta->failed()) {
            $cuerpo = $respuesta->json();

            // El código de error sí es útil registrarlo; el código en sí no, es un secreto.
            Log::warning('Canje DNIe rechazado', [
                'status' => $respuesta->status(),
                'codigo' => $cuerpo['codigo'] ?? null,
            ]);

            throw new RuntimeException(match ($cuerpo['codigo'] ?? '') {
                'CodigoInvalido'     => 'La solicitud de ingreso expiró o ya se usó. Inténtelo de nuevo.',
                'ClienteNoAutorizado' => 'Este sitio no está autorizado en el servicio de autenticación.',
                default               => 'No se pudo completar el ingreso con DNIe.',
            });
        }

        $datos = $respuesta->json();
        $usuario = $datos['usuario'] ?? null;

        if (empty($usuario['dni']) || empty($datos['token'])) {
            throw new RuntimeException('El servicio de autenticación devolvió una respuesta incompleta.');
        }

        return [
            'dni'       => $usuario['dni'],
            'nombres'   => $usuario['nombres'] ?? '',
            'apellidos' => $usuario['apellidos'] ?? null,
            'serial'    => $usuario['serialCertificado'] ?? '',
            'token'     => $datos['token'],
        ];
    }
}
