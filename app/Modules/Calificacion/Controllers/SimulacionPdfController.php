<?php

namespace App\Modules\Calificacion\Controllers;

use App\Modules\Calificacion\Exports\SimulacionGridExport;
use App\Modules\Calificacion\Exports\SimulacionMapeoExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SimulacionPdfController extends BaseCalificacionController
{
    // ─── PDF (mPDF) ───

    public function gridPdf(Request $request): Response|JsonResponse
    {
        $request->validate([
            'nombre' => 'nullable|string',
            'aulas' => 'required|array',
            'aulas.*.nombre' => 'required|string',
            'aulas.*.capacidad' => 'required|integer',
            'aulas.*.area' => 'nullable|string',
            'aulas.*.assignments' => 'required|array',
            'aulas.*.assignments.*.posicion' => 'required|integer',
            'aulas.*.assignments.*.nombre' => 'required|string',
            'aulas.*.assignments.*.codigo' => 'required|string',
            'aulas.*.assignments.*.n_documento' => 'required|string',
            'aulas.*.assignments.*.tipo_examen' => 'required|string',
        ]);

        try {
            $data = $request->all();
            $nombre = $data['nombre'] ?? 'Simulación Manual';
            $aulas = $data['aulas'];

            $totalStudents = 0;
            foreach ($aulas as $a) {
                $totalStudents += count($a['assignments']);
            }

            $html = View::make('calificacion::pdf.simulacion_grid', [
                'nombre' => $nombre,
                'aulas' => $aulas,
                'totalStudents' => $totalStudents,
            ])->render();

            return $this->renderPdf($html, 'Simulacion_Grid_' . date('YmdHis'));
        } catch (\Exception $e) {
            Log::error('Error generando PDF de simulación', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    public function mapeoPdf(Request $request): Response|JsonResponse
    {
        $request->validate([
            'nombre' => 'nullable|string',
            'mapeo' => 'required|array',
            'mapeo.*.aula_virtual' => 'required|string',
            'mapeo.*.area_virtual' => 'nullable|string',
            'mapeo.*.ambiente_nombre' => 'nullable|string',
            'mapeo.*.aula_fisica' => 'nullable|string',
            'mapeo.*.piso' => 'nullable',
            'mapeo.*.estudiantes' => 'required|integer',
            'mapeo.*.capacidad' => 'required|integer',
        ]);

        try {
            $data = $request->all();
            $nombre = $data['nombre'] ?? 'Mapeo de Ambientes - Simulación';
            $mapeo = $data['mapeo'];

            $totalEstudiantes = array_sum(array_column($mapeo, 'estudiantes'));
            $totalCapacidad = array_sum(array_column($mapeo, 'capacidad'));

            $html = View::make('calificacion::pdf.simulacion_mapeo', [
                'nombre' => $nombre,
                'mapeo' => $mapeo,
                'totalEstudiantes' => $totalEstudiantes,
                'totalCapacidad' => $totalCapacidad,
            ])->render();

            return $this->renderPdf($html, 'Mapeo_Ambientes_Simulacion_' . date('YmdHis'));
        } catch (\Exception $e) {
            Log::error('Error generando PDF de mapeo', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar PDF: ' . $e->getMessage(), 500);
        }
    }

    // ─── Excel (Laravel Excel) ───

    public function gridExcel(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'aulas' => 'required|array',
            'aulas.*.nombre' => 'required|string',
            'aulas.*.capacidad' => 'required|integer',
            'aulas.*.area' => 'nullable|string',
            'aulas.*.assignments' => 'required|array',
        ]);

        try {
            $aulas = $request->input('aulas');
            $filename = 'Simulacion_Grid_' . date('YmdHis') . '.xlsx';

            return Excel::download(new SimulacionGridExport($aulas), $filename);
        } catch (\Exception $e) {
            Log::error('Error generando Excel de simulación', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar Excel: ' . $e->getMessage(), 500);
        }
    }

    public function mapeoExcel(Request $request): BinaryFileResponse|JsonResponse
    {
        $request->validate([
            'mapeo' => 'required|array',
        ]);

        try {
            $mapeo = $request->input('mapeo');
            $filename = 'Mapeo_Ambientes_Simulacion_' . date('YmdHis') . '.xlsx';

            return Excel::download(new SimulacionMapeoExport($mapeo), $filename);
        } catch (\Exception $e) {
            Log::error('Error generando Excel de mapeo', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al generar Excel: ' . $e->getMessage(), 500);
        }
    }

    // ─── Helpers ───

    private function renderPdf(string $html, string $title): Response
    {
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir,
        ]);

        $mpdf->SetTitle($title);
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->WriteHTML($html);

        $filename = strtolower($title) . '.pdf';

        return response($mpdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
