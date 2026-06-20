<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'API Admisión UNAP',
    description: 'API del sistema de admisión de la Universidad Nacional del Altiplano de Puno.

## Módulos

- **Revisor**: Endpoints para la revisión de documentos, dashboard, notificaciones y gestión de postulantes.
- **Health Check**: Estado del sistema.',
)]
#[OA\Server(
    url: 'http://localhost',
    description: 'Servidor local (Laragon)',
)]
#[OA\Components(
    securitySchemes: [
        new OA\SecurityScheme(
            securityScheme: 'sanctum',
            type: 'http',
            scheme: 'bearer',
            bearerFormat: 'JWT',
            description: 'Token Sanctum (Bearer token)',
        ),
    ]
)]
#[OA\Security(name: 'sanctum', securityScheme: 'sanctum')]
class SwaggerInfo
{
    // Clase solo para anotaciones OpenAPI
}
