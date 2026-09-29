<?php

namespace App\Services\Revisor;

use App\Models\Documento;
use App\Models\RevisionSolicitud;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevisorNotificacionService
{
    public function index(int $userId, int $limit = 20): array
    {
        $user = Auth::user();
        $user->load('notifications');

        $notificaciones = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($n) {
                return [
                    'id'                => $n->id,
                    'tipo'              => $n->data['tipo'] ?? 'otro',
                    'mensaje'           => $n->data['mensaje'] ?? '',
                    'postulante_nombre' => $n->data['postulante_nombre'] ?? '',
                    'postulante_dni'    => $n->data['postulante_dni'] ?? '',
                    'veces'             => $n->data['veces'] ?? 1,
                    'url'               => $n->data['url'] ?? '',
                    'leida'             => $n->read_at !== null,
                    'created_at'        => $n->created_at->format('d/m/Y H:i'),
                    'created_at_diff'   => $n->created_at->diffForHumans(),
                ];
            });

        $noLeidas = $user->unreadNotifications()->count();

        return [
            'notificaciones' => $notificaciones,
            'no_leidas'     => $noLeidas,
        ];
    }

    public function noLeidas(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function marcarLeida(string $id): bool
    {
        $notificacion = Auth::user()->notifications()->where('id', $id)->first();

        if (!$notificacion) {
            return false;
        }

        $notificacion->markAsRead();
        return true;
    }

    public function marcarTodasLeidas(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function solicitudesRevision(string $busqueda = '', string $filtro = 'pendientes', ?string $desde = null, ?string $hasta = null, ?int $modalidadId = null)
    {
        $idProceso = Auth::user()->id_proceso;

        $query = RevisionSolicitud::with('postulante:id,nro_doc,primer_apellido,segundo_apellido,nombres')
            ->orderBy('solicitada_at', 'desc');

        if ($filtro === 'atendidas') {
            $query->whereNotNull('finalizada_at');
        } elseif ($filtro === 'todas') {
            // sin filtro de estado
        } else {
            $query->whereNull('finalizada_at');
        }

        if ($desde) {
            $query->whereDate('solicitada_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('solicitada_at', '<=', $hasta);
        }
        if ($modalidadId) {
            $query->where('id_modalidad', $modalidadId);
        }

        if ($busqueda) {
            $query->whereHas('postulante', function ($q) use ($busqueda) {
                $q->where('nro_doc', 'like', "%{$busqueda}%")
                    ->orWhere('nombres', 'like', "%{$busqueda}%")
                    ->orWhere('primer_apellido', 'like', "%{$busqueda}%")
                    ->orWhere('segundo_apellido', 'like', "%{$busqueda}%");
            });
        }

        return $query->paginate(20)->through(function ($s) use ($idProceso) {
            $p = $s->postulante;

            // Tipos válidos solo para la modalidad de esta solicitud,
            // no globales. Así cada fila muestra sus propios documentos.
            $reqIds = DB::table('requisito_modalidad')
                ->where('id_modalidad', $s->id_modalidad)
                ->pluck('id_requisito_documento');

            $tiposSolicitud = DB::table('requisito_tipo_documento')
                ->whereIn('id_requisito_documento', $reqIds)
                ->pluck('id_tipo_documento')
                ->unique();

            $documentosSubidos = $tiposSolicitud->isEmpty() ? 0 : Documento::where('id_postulante', $p->id)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->whereIn('id_tipo_documento', $tiposSolicitud)
                ->count();

            $documentosVerificados = $tiposSolicitud->isEmpty() ? 0 : Documento::where('id_postulante', $p->id)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->whereIn('id_tipo_documento', $tiposSolicitud)
                ->where('valido', 1)
                ->count();

            $modalidad = DB::table('modalidad')
                ->where('id', $s->id_modalidad)
                ->select('nombre')
                ->first();

            if (!$modalidad) {
                $modalidad = DB::table('pre_inscripcion')
                    ->join('modalidad', 'modalidad.id', '=', 'pre_inscripcion.id_modalidad')
                    ->where('pre_inscripcion.id_postulante', $p->id)
                    ->select('modalidad.nombre')
                    ->latest('pre_inscripcion.id')
                    ->first();
            }

            return [
                'id'                          => $p->id,
                'solicitud_id'                => $s->id,
                'nro_doc'                     => $p->nro_doc,
                'nombre_completo'             => trim("{$p->primer_apellido} {$p->segundo_apellido} {$p->nombres}"),
                'modalidad'                   => $modalidad?->nombre ?? 'No asignada',
                'revision_solicitada_at'      => $s->solicitada_at ? $s->solicitada_at->format('d/m/Y H:i') : null,
                'revision_solicitada_at_diff' => $s->solicitada_at ? $s->solicitada_at->diffForHumans() : null,
                'veces_revision_solicitada'   => $s->veces,
                'revisado'                    => $p->revisado,
                'revision_iniciada_at'        => $s->iniciada_at ? $s->iniciada_at->format('d/m/Y H:i') : null,
                'revision_finalizada_at'      => $s->finalizada_at ? $s->finalizada_at->format('d/m/Y H:i') : null,
                'documentos_subidos'          => $documentosSubidos,
                'documentos_verificados'       => $documentosVerificados,
            ];
        });
    }
}
