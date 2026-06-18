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
        $busqueda = $request->input('busqueda', '');
        $solicitudes = $this->service->solicitudesRevision($busqueda);

        return Inertia('Revisor/SolicitudesRevision', [
            'solicitudes' => $solicitudes,
            'busqueda'    => $busqueda,
        ]);
    }
}
