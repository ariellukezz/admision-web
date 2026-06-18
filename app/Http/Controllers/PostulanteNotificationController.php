<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostulanteNotificationController extends Controller
{
    /**
     * Listar notificaciones del postulante autenticado
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 20);

        $notificaciones = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($n) {
                $data = $n->data;
                return [
                    'id' => $n->id,
                    'tipo' => $data['tipo'] ?? 'otro',
                    'mensaje' => $data['mensaje'] ?? '',
                    'postulante_nombre' => $data['postulante_nombre'] ?? '',
                    'postulante_dni' => $data['postulante_dni'] ?? '',
                    'veces' => $data['veces'] ?? 1,
                    'documentos_verificados' => $data['documentos_verificados'] ?? [],
                    'documentos_pendientes' => $data['documentos_pendientes'] ?? [],
                    'fecha_cita' => $data['fecha_cita'] ?? null,
                    'hora_inicio' => $data['hora_inicio'] ?? null,
                    'hora_fin' => $data['hora_fin'] ?? null,
                    'lugar' => $data['lugar'] ?? null,
                    'instrucciones' => $data['instrucciones'] ?? null,
                    'leida' => $n->read_at !== null,
                    'created_at' => $n->created_at->format('d/m/Y H:i'),
                    'created_at_diff' => $n->created_at->diffForHumans(),
                ];
            });

        $noLeidas = $user->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas,
        ]);
    }

    /**
     * Contar notificaciones no leídas
     */
    public function noLeidas()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'no_leidas' => $count,
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarLeida(string $id)
    {
        $notificacion = Auth::user()->notifications()->where('id', $id)->first();

        if (!$notificacion) {
            return response()->json(['success' => false, 'mensaje' => 'Notificación no encontrada'], 404);
        }

        $notificacion->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
