<?php

namespace App\Http\Controllers;

use App\Http\Requests\Revisor\CambiarEstadoDocumentoRequest;
use App\Http\Requests\Revisor\FinalizarRevisionRequest;
use App\Http\Requests\Revisor\IniciarRevisionRequest;
use App\Http\Requests\Revisor\ObservarDocumentoRequest;
use App\Services\Revisor\RevisorDocumentoService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class RevisorDocumentoController extends Controller
{
    use ApiResponse;

    public function __construct(
        private RevisorDocumentoService $service,
    ) {}

    private function flatSuccess(array $result)
    {
        // Compat: el Vue lee res.data.mensaje / res.data.iniciada_at en plano.
        // Se manda plano + anidado para el código nuevo y viejo.
        return response()->json(array_merge(['success' => true], $result, ['data' => $result]));
    }

    public function iniciarRevision(IniciarRevisionRequest $request, string $dni)
    {
        $result = $this->service->iniciarRevision($dni, $request->input('solicitud_id'));

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function marcarApto(Request $request, string $dni)
    {
        $result = $this->service->marcarApto($dni, $request->input('solicitud_id'));

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function finalizarRevision(FinalizarRevisionRequest $request, string $dni)
    {
        $result = $this->service->finalizarRevision(
            $dni,
            $request->only(['fecha', 'hora_inicio', 'hora_fin', 'lugar', 'instrucciones']),
            $request->input('solicitud_id')
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function renotificarPostulante(Request $request, string $dni)
    {
        $result = $this->service->renotificarPostulante($dni, $request->input('solicitud_id'));

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function revisionRapida(Request $request, string $dni)
    {
        $result = $this->service->revisionRapida($dni, $request->input('solicitud_id'));

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function cambiarEstadoDocumento(CambiarEstadoDocumentoRequest $request)
    {
        $result = $this->service->cambiarEstadoDocumento(
            $request->input('id_documento'),
            $request->input('accion'),
            $request->input('fecha_caducidad'),
            $request->input('observacion'),
            $request->input('solicitud_id') ? (int) $request->input('solicitud_id') : null
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return response()->json(array_merge(['success' => true, 'message' => $result['mensaje'] ?? 'OK', 'data' => $result['datos'] ?? $result, 'datos' => $result['datos'] ?? $result], $result['datos'] ?? []));
    }

    public function observarDocumento(ObservarDocumentoRequest $request)
    {
        $result = $this->service->observarDocumento(
            $request->input('id_documento'),
            $request->input('observacion'),
            $request->input('solicitud_id')
        );

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return response()->json(array_merge(['success' => true, 'message' => $result['mensaje'] ?? 'OK', 'data' => $result['datos'] ?? $result, 'datos' => $result['datos'] ?? $result], $result['datos'] ?? []));
    }

    public function citacionSugerida(Request $request, string $dni)
    {
        $idProceso = auth()->user()->id_proceso;
        $result = $this->service->citacionSugerida($dni, $idProceso);

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        return $this->flatSuccess($result);
    }

    public function documentosPorRequisitos(Request $request, string $dni)
    {
        $result = $this->service->documentosPorRequisitos($dni, $request->query('solicitud') ? (int) $request->query('solicitud') : null);

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['status']);
        }

        // Compat: el Vue viejo lee `.datos`, ApiResponse usa `.data`. Mandar ambos.
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => $result,
            'datos' => $result,
        ]);
    }
}
