<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditTrail;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class AuditTrailController extends Controller
{
    // ─── ADMIN ─────────────────────────────────────

    public function trazabilidad()
    {
        return Inertia::render('Admin/Trazabilidad/index');
    }

    public function getAuditTrail(Request $request)
    {
        $query = AuditTrail::with('user:id,id_rol,name,paterno,materno,dni')
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('target_user_id')) {
            $query->forTargetUser($request->target_user_id);
        }

        if ($request->filled('id_proceso')) {
            $query->forProceso($request->id_proceso);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('alias', 'LIKE', "%{$search}%");
            });
        }

        $res = $query->paginate($request->input('per_page', 25));

        $res->getCollection()->transform(function ($item) {
            $item->actor_name = AuditService::resolveActorName($item);
            $item->model_short = class_basename($item->model_type);
            return $item;
        });

        return response()->json([
            'estado' => true,
            'datos' => $res,
        ]);
    }

    public function getResumenAcciones(Request $request)
    {
        $procesoId = $request->input('id_proceso', auth()->user()->id_proceso);

        $byAction = AuditTrail::select('action', DB::raw('count(*) as total'))
            ->when($procesoId, fn($q) => $q->forProceso($procesoId))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('action')
            ->pluck('total', 'action');

        $byModel = AuditTrail::select('model_type', DB::raw('count(*) as total'))
            ->when($procesoId, fn($q) => $q->forProceso($procesoId))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('model_type')
            ->get()
            ->map(fn($r) => ['model' => class_basename($r->model_type), 'total' => $r->total]);

        $topActors = AuditTrail::select('user_id', 'alias', DB::raw('count(*) as total'))
            ->when($procesoId, fn($q) => $q->forProceso($procesoId))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('user_id')
            ->groupBy('user_id', 'alias')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['name' => $r->alias ?? 'Admin', 'total' => $r->total]);

        return response()->json([
            'estado' => true,
            'datos' => [
                'by_action' => $byAction,
                'by_model' => $byModel,
                'top_actors' => $topActors,
            ],
        ]);
    }

    // ─── POSTULANTE ────────────────────────────────

    public function misAcciones()
    {
        return Inertia::render('Postulante/MisAcciones');
    }

    public function getMisAcciones(Request $request)
    {
        $userId = auth()->id();

        $query = AuditTrail::orderByDesc('created_at')
            ->forUser($userId);

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        $res = $query->paginate($request->input('per_page', 20));

        $res->getCollection()->transform(function ($item) {
            $item->actor_name = AuditService::resolveActorName($item);
            $item->model_short = class_basename($item->model_type);
            return $item;
        });

        return response()->json([
            'estado' => true,
            'datos' => $res,
        ]);
    }

    public function getAccionesMisDocumentos(Request $request)
    {
        $userId = auth()->id();

        $query = AuditTrail::orderByDesc('created_at')
            ->forTargetUser($userId)
            ->where('model_type', \App\Models\Documento::class);

        if ($request->filled('action')) {
            $query->forAction($request->action);
        }

        $res = $query->paginate($request->input('per_page', 20));

        $res->getCollection()->transform(function ($item) {
            $item->actor_name = AuditService::resolveActorName($item);
            $item->model_short = class_basename($item->model_type);
            return $item;
        });

        return response()->json([
            'estado' => true,
            'datos' => $res,
        ]);
    }

    public function getHistorialPreinscripciones(Request $request)
    {
        $user = auth()->user();
        $postulante = \App\Models\Postulante::where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json([
                'estado' => true,
                'datos' => [
                    'vinculado' => false,
                    'preinscripciones' => [],
                    'inscripciones' => [],
                ],
            ]);
        }

        $preinscripciones = \App\Models\Preinscripcion::where('id_postulante', $postulante->id)
            ->with(['programa:id,nombre', 'modalidad:id,nombre'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'programa' => $p->programa?->nombre ?? '—',
                'modalidad' => $p->modalidad?->nombre ?? '—',
                'estado' => $p->estado,
                'fecha' => $p->created_at?->format('d/m/Y H:i'),
            ]);

        $inscripciones = \App\Models\Inscripcion::where('id_postulante', $postulante->id)
            ->with(['programa:id,nombre', 'modalidad:id,nombre'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'codigo' => $i->codigo,
                'programa' => $i->programa?->nombre ?? '—',
                'modalidad' => $i->modalidad?->nombre ?? '—',
                'estado' => $i->estado,
                'fecha' => $i->created_at?->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'estado' => true,
            'datos' => [
                'vinculado' => true,
                'postulante_id' => $postulante->id,
                'postulante_nombre' => trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}"),
                'preinscripciones' => $preinscripciones,
                'inscripciones' => $inscripciones,
            ],
        ]);
    }
}
