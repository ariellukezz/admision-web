<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Services\PdfReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DistributionPdfController extends BaseCalificacionController
{
    public function __construct(
        private readonly PdfReportService $pdfReportService
    ) {}

    public function classroomPdf(int $id): Response|JsonResponse
    {
        try {
            return $this->pdfReportService->generateClassroomPdf($id);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de aulas', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function conflictsPdf(Request $request, int $id): Response|JsonResponse
    {
        try {
            $toggles = [
                'twins' => (bool) $request->query('show_twins', 1),
                'same_college' => (bool) $request->query('show_same_college', 1),
                'same_parents' => (bool) $request->query('show_same_parents', 1),
            ];
            return $this->pdfReportService->generateConflictsPdf($id, $toggles);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de conflictos', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function parametersPdf(int $id): Response|JsonResponse
    {
        try {
            return $this->pdfReportService->generateParametersPdf($id);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de parametros', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function auditLogPdf(int $id): Response|JsonResponse
    {
        try {
            return $this->pdfReportService->generateAuditLogPdf($id);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de historial', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function testTypesPdf(int $id): Response|JsonResponse
    {
        try {
            return $this->pdfReportService->generateTestTypesPdf($id);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de tipos de examen', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function padronPdf(int $id): Response|JsonResponse
    {
        try {
            return $this->pdfReportService->generatePadronPdf($id);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de padrón', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function listaPdf(Request $request, int $id): Response|JsonResponse
    {
        try {
            $orderBy = $request->query('order_by', 'asiento');
            return $this->pdfReportService->generateListaPdf($id, $orderBy);
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (\Exception $e) {
            Log::error('Error generando PDF de lista', ['grupo_filtro_id' => $id, 'error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }
}
