<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class RevisorEndpoints
{
    // ── Dashboard ──────────────────────────────────────────

    #[OA\Get(
        path: '/revisor/dashboard/resumen',
        summary: 'Resumen general del proceso',
        description: 'KPIs: inscritos, preinscritos, biometricos, documentos y comprobantes pendientes/verificados',
        tags: ['Revisor - Dashboard'],
    )]
    #[OA\Response(
        response: 200,
        description: 'Resumen del dashboard',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'OK'),
                new OA\Property(property: 'data', properties: [
                    new OA\Property(property: 'inscritos', type: 'integer', example: 150),
                    new OA\Property(property: 'inscritos_hoy', type: 'integer', example: 12),
                    new OA\Property(property: 'preinscritos', type: 'integer', example: 300),
                    new OA\Property(property: 'documentos_pendientes', type: 'integer', example: 45),
                    new OA\Property(property: 'comprobantes_pendientes', type: 'integer', example: 30),
                ], type: 'object'),
            ],
        ),
    )]
    public function dashboardResumen() {}

    #[OA\Get(
        path: '/revisor/dashboard/inscripciones-por-programa',
        summary: 'Inscripciones por programa (top 12)',
        tags: ['Revisor - Dashboard'],
    )]
    #[OA\Response(response: 200, description: 'Lista de programas con cantidad de inscritos')]
    public function dashboardInscripcionesPrograma() {}

    #[OA\Get(
        path: '/revisor/dashboard/biometrico-resumen',
        summary: 'Resumen de control biométrico',
        tags: ['Revisor - Dashboard'],
    )]
    #[OA\Response(response: 200, description: 'Total de inscritos con/sin biométrico')]
    public function dashboardBiometrico() {}

    // ── Revisión de Documentos ─────────────────────────────

    #[OA\Post(
        path: '/revisor/iniciar-revision/{dni}',
        summary: 'Iniciar revisión de documentos',
        tags: ['Revisor - Documentos'],
    )]
    #[OA\Parameter(name: 'dni', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'solicitud_id', type: 'integer', nullable: true),
            ],
        ),
    )]
    #[OA\Response(response: 200, description: 'Revisión iniciada')]
    #[OA\Response(response: 404, description: 'Postulante no encontrado')]
    public function iniciarRevision() {}

    #[OA\Post(
        path: '/revisor/finalizar-revision/{dni}',
        summary: 'Finalizar revisión de documentos',
        description: 'Si todos los documentos son válidos: marca como completada y genera citación. Si hay pendientes: marca como pendiente y notifica al postulante.',
        tags: ['Revisor - Documentos'],
    )]
    #[OA\Parameter(name: 'dni', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['fecha', 'hora_inicio', 'hora_fin', 'lugar'],
            properties: [
                new OA\Property(property: 'solicitud_id', type: 'integer', nullable: true),
                new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2026-07-15'),
                new OA\Property(property: 'hora_inicio', type: 'string', example: '08:00'),
                new OA\Property(property: 'hora_fin', type: 'string', example: '10:00'),
                new OA\Property(property: 'lugar', type: 'string', example: 'Sala de Revisión - Pabellón A'),
                new OA\Property(property: 'instrucciones', type: 'string', nullable: true),
            ],
        ),
    )]
    #[OA\Response(response: 200, description: 'Revisión finalizada')]
    public function finalizarRevision() {}

    #[OA\Post(
        path: '/revisor/revision-rapida/{dni}',
        summary: 'Revisión rápida: marcar todos los documentos como válidos',
        tags: ['Revisor - Documentos'],
    )]
    #[OA\Parameter(name: 'dni', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Documentos marcados como válidos')]
    public function revisionRapida() {}

    #[OA\Post(
        path: '/revisor/cambiar-estado-documento',
        summary: 'Cambiar estado de un documento individual',
        tags: ['Revisor - Documentos'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['id_documento', 'accion'],
            properties: [
                new OA\Property(property: 'id_documento', type: 'integer', example: 1),
                new OA\Property(property: 'accion', type: 'string', enum: ['apto_revision', 'valido', 'desmarcar']),
                new OA\Property(property: 'fecha_caducidad', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'observacion', type: 'string', nullable: true, maxLength: 1000),
            ],
        ),
    )]
    #[OA\Response(response: 200, description: 'Estado del documento actualizado')]
    public function cambiarEstadoDocumento() {}

    #[OA\Get(
        path: '/revisor/documentos-requisitos/{dni}',
        summary: 'Documentos del postulante agrupados por requisitos',
        tags: ['Revisor - Documentos'],
    )]
    #[OA\Parameter(name: 'dni', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'solicitud', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Lista de requisitos con documentos')]
    public function documentosPorRequisitos() {}

    // ── Notificaciones ─────────────────────────────────────

    #[OA\Get(
        path: '/revisor/notificaciones',
        summary: 'Listar notificaciones del revisor',
        tags: ['Revisor - Notificaciones'],
    )]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Lista de notificaciones')]
    public function notificacionesIndex() {}

    #[OA\Post(
        path: '/revisor/notificaciones/{id}/leer',
        summary: 'Marcar notificación como leída',
        tags: ['Revisor - Notificaciones'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'Notificación marcada como leída')]
    public function notificacionLeer() {}

    // ── Mi Actividad ───────────────────────────────────────

    #[OA\Get(
        path: '/revisor/mi-actividad/resumen',
        summary: 'KPIs personales del revisor',
        tags: ['Revisor - Mi Actividad'],
    )]
    #[OA\Response(response: 200, description: 'Resumen de actividad personal')]
    public function actividadResumen() {}

    #[OA\Get(
        path: '/revisor/mi-actividad/timeline',
        summary: 'Timeline de actividad (últimos 30 días)',
        tags: ['Revisor - Mi Actividad'],
    )]
    #[OA\Response(response: 200, description: 'Serie temporal de actividad diaria')]
    public function actividadTimeline() {}
}
