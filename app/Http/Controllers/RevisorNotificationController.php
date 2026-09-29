<?php

namespace App\Http\Controllers;

use App\Services\Revisor\RevisorNotificacionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RevisorNotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private RevisorNotificacionService $service,
    ) {}

    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        return $this->success($this->service->index(auth()->id(), $limit));
    }

    public function noLeidas()
    {
        return $this->success(['no_leidas' => $this->service->noLeidas()]);
    }

    public function marcarLeida(string $id)
    {
        $ok = $this->service->marcarLeida($id);

        if (!$ok) {
            return $this->notFound('Notificación no encontrada');
        }

        return $this->success(null, 'Notificación marcada como leída');
    }

    public function marcarTodasLeidas()
    {
        $this->service->marcarTodasLeidas();
        return $this->success(null, 'Todas las notificaciones marcadas como leídas');
    }

    public function solicitudesRevision(Request $request)
    {
        $busqueda = (string) ($request->input('busqueda') ?? '');
        $filtro = (string) ($request->input('filtro') ?? 'pendientes');
        if (!in_array($filtro, ['pendientes', 'atendidas', 'todas'])) {
            $filtro = 'pendientes';
        }
        $desde = $request->input('desde') ?: null;
        $hasta = $request->input('hasta') ?: null;
        $modalidadId = $request->input('modalidad_id') ? (int) $request->input('modalidad_id') : null;
        $solicitudes = $this->service->solicitudesRevision($busqueda, $filtro, $desde, $hasta, $modalidadId);

        $modalidades = \Illuminate\Support\Facades\DB::table('modalidad')
            ->select('id as value', 'nombre as label')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return Inertia('Revisor/SolicitudesRevision', [
            'solicitudes' => $solicitudes,
            'busqueda'    => $busqueda,
            'filtro'      => $filtro,
            'desde'       => $desde,
            'hasta'       => $hasta,
            'modalidad_id' => $modalidadId,
            'modalidades' => $modalidades,
        ]);
    }
}
