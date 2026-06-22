<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\Documento;
use App\Models\RevisionSolicitud;
use App\Models\Paso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDocumentoController extends Controller
{
    /**
     * Lista postulantes con solicitudes de revisión pendientes o en curso.
     */
    public function listarSolicitudesPendientes(Request $request)
    {
        $query = RevisionSolicitud::with(['postulante:id,nro_doc,nombres,primer_apellido,segundo_apellido,id_proceso'])
            ->where('estado', '!=', 'completada')
            ->orderByDesc('solicitada_at');

        $solicitudes = $query->get()->map(function ($s) {
            $postulante = $s->postulante;
            if (!$postulante) return null;

            $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");

            // Contar documentos del postulante
            $totalDocs = Documento::where('id_postulante', $postulante->id)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->count();

            $docsValidos = Documento::where('id_postulante', $postulante->id)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->where('valido', true)
                ->count();

            // Obtener modalidad y programa
            $modalidad = $s->id_modalidad ? DB::table('modalidad')->where('id', $s->id_modalidad)->first() : null;
            $programa = $s->id_programa ? DB::table('programa')->where('id', $s->id_programa)->first() : null;

            return [
                'id_solicitud' => $s->id,
                'id_postulante' => $postulante->id,
                'dni' => $postulante->nro_doc,
                'nombre_completo' => $nombreCompleto,
                'modalidad' => $modalidad?->nombre ?? '—',
                'programa' => $programa?->nombre_corto ?? $programa?->nombre ?? '—',
                'total_docs' => $totalDocs,
                'docs_validos' => $docsValidos,
                'estado' => $s->estado,
                'solicitada_at' => $s->solicitada_at?->format('d/m/Y H:i'),
                'veces' => $s->veces,
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'datos' => $solicitudes,
        ]);
    }

    /**
     * Validación masiva: marca todos los documentos de los postulantes seleccionados como válidos.
     */
    public function validacionMasiva(Request $request)
    {
        $request->validate([
            'ids_postulantes' => 'required|array|min:1',
            'ids_postulantes.*' => 'integer',
            'motivo' => 'required|string|max:1000',
        ]);

        $idsPostulantes = $request->ids_postulantes;
        $motivo = $request->motivo;
        $adminId = Auth::id();
        $ahora = now();
        $totalDocsValidados = 0;
        $totalSolicitudesCompletadas = 0;

        foreach ($idsPostulantes as $idPostulante) {
            // Marcar todos los documentos activos como válidos
            $count = Documento::where('id_postulante', $idPostulante)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->update([
                    'apto_revision' => true,
                    'valido' => true,
                    'revisado_at' => $ahora,
                    'validado_at' => $ahora,
                    'id_revisor' => $adminId,
                    'verificado' => 1,
                    'observacion_revisor' => 'Validación masiva por administrador. Motivo: ' . $motivo,
                ]);

            $totalDocsValidados += $count;

            // Completar solicitudes de revisión activas
            $solicitudes = RevisionSolicitud::where('id_postulante', $idPostulante)
                ->where('estado', '!=', 'completada')
                ->get();

            foreach ($solicitudes as $solicitud) {
                $solicitud->finalizada_at = $ahora;
                $solicitud->estado = 'completada';
                $solicitud->apto = true;
                $solicitud->apto_at = $ahora;
                $solicitud->revisor_id = $adminId;
                $solicitud->datos_citacion = [
                    'fecha' => $ahora->format('Y-m-d'),
                    'hora_inicio' => '00:00',
                    'hora_fin' => '00:00',
                    'lugar' => 'Validación masiva administrativa',
                    'instrucciones' => 'Documentos validados masivamente. Motivo: ' . $motivo,
                ];

                // Guardar resumen de documentos
                $docs = Documento::where('id_postulante', $idPostulante)
                    ->where('estado', 1)
                    ->where('is_deleted', false)
                    ->get();
                $solicitud->documentos_verificados = $docs->pluck('nombre')->toArray();
                $solicitud->documentos_pendientes = [];
                $solicitud->save();

                $totalSolicitudesCompletadas++;

                // Registrar paso
                $idProceso = $solicitud->id_proceso;
                if ($idProceso) {
                    Paso::create([
                        'nombre' => 'REVISIÓN DE DOCUMENTOS COMPLETADA (ADMIN)',
                        'nro' => 7,
                        'avance' => 7,
                        'postulante' => $idPostulante,
                        'proceso' => $idProceso,
                    ]);
                }
            }

            // Actualizar flag del postulante
            $tieneActivas = RevisionSolicitud::where('id_postulante', $idPostulante)
                ->where('estado', '!=', 'completada')
                ->exists();
            $postulante = Postulante::find($idPostulante);
            if ($postulante) {
                $postulante->tiene_revision_activa = $tieneActivas;
                $postulante->save();
            }
        }

        // Log de la acción administrativa
        Log::info('Validación masiva ejecutada', [
            'admin_id' => $adminId,
            'postulantes_count' => count($idsPostulantes),
            'docs_validados' => $totalDocsValidados,
            'solicitudes_completadas' => $totalSolicitudesCompletadas,
            'motivo' => $motivo,
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => "Se validaron {$totalDocsValidados} documento(s) de " . count($idsPostulantes) . " postulante(s)",
            'total_docs' => $totalDocsValidados,
            'total_solicitudes' => $totalSolicitudesCompletadas,
        ]);
    }
}
