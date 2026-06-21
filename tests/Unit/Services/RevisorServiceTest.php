<?php

namespace Tests\Unit\Services;

use App\Services\Revisor\RevisorDashboardService;
use App\Services\Revisor\RevisorDocumentoService;
use App\Services\Revisor\RevisorNotificacionService;
use App\Services\Revisor\RevisorPersonalService;
use Tests\TestCase;

class RevisorServiceTest extends TestCase
{
    public function test_dashboard_service_can_be_resolved_from_container(): void
    {
        $service = app(RevisorDashboardService::class);

        $this->assertInstanceOf(RevisorDashboardService::class, $service);
    }

    public function test_personal_service_can_be_resolved_from_container(): void
    {
        $service = app(RevisorPersonalService::class);

        $this->assertInstanceOf(RevisorPersonalService::class, $service);
    }

    public function test_documento_service_can_be_resolved_with_dependencies(): void
    {
        $service = app(RevisorDocumentoService::class);

        $this->assertInstanceOf(RevisorDocumentoService::class, $service);
    }

    public function test_notificacion_service_can_be_resolved_from_container(): void
    {
        $service = app(RevisorNotificacionService::class);

        $this->assertInstanceOf(RevisorNotificacionService::class, $service);
    }

    public function test_dashboard_service_has_expected_methods(): void
    {
        $service = app(RevisorDashboardService::class);

        $this->assertTrue(method_exists($service, 'resumen'));
        $this->assertTrue(method_exists($service, 'biometricoResumen'));
        $this->assertTrue(method_exists($service, 'inscripcionesPorPrograma'));
        $this->assertTrue(method_exists($service, 'modalidadDistribucion'));
        $this->assertTrue(method_exists($service, 'verificacionesPendientes'));
    }

    public function test_documento_service_has_expected_methods(): void
    {
        $service = app(RevisorDocumentoService::class);

        $this->assertTrue(method_exists($service, 'iniciarRevision'));
        $this->assertTrue(method_exists($service, 'finalizarRevision'));
        $this->assertTrue(method_exists($service, 'revisionRapida'));
        $this->assertTrue(method_exists($service, 'cambiarEstadoDocumento'));
        $this->assertTrue(method_exists($service, 'observarDocumento'));
        $this->assertTrue(method_exists($service, 'documentosPorRequisitos'));
        $this->assertTrue(method_exists($service, 'citacionSugerida'));
    }
}
