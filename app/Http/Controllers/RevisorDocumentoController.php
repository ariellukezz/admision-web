<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\Documento;
use App\Models\RequisitoDocumento;
use App\Models\Preinscripcion;
use App\Models\Inscripcion;
use App\Models\RevisionSolicitud;
use App\Models\User;
use App\Models\Paso;
use App\Notifications\RevisionCompletadaNotification;
use App\Notifications\RevisionIniciadaNotification;
use App\Services\CitacionService;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevisorDocumentoController extends Controller
{
    /**
     * Inicia la revisión de documentos de un postulante.
     */
    public function iniciarRevision(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        // Buscar la solicitud de revisión: específica (si viene solicitud_id) o la última activa
        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        } else {
            $query->whereNull('finalizada_at');
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'mensaje' => 'El postulante no ha solicitado revisión'], 400);
        }

        // Si la revisión ya está iniciada y no finalizada, retornar info actual
        if ($solicitud->iniciada_at && !$solicitud->finalizada_at) {
            $revisor = User::find($solicitud->revisor_id);
            return response()->json([
                'success' => true,
                'mensaje' => 'La revisión ya está en curso',
                'iniciada_at' => $solicitud->iniciada_at,
                'revisor' => $revisor ? $revisor->name : null,
            ]);
        }

        // Iniciar revisión
        $solicitud->iniciada_at = now();
        $solicitud->finalizada_at = null;
        $solicitud->apto = false;
        $solicitud->apto_at = null;
        $solicitud->revisor_id = Auth::id();
        $solicitud->estado = 'en_revision';
        $solicitud->save();

        // Notificar al postulante que su revisión ha iniciado
        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");
        $revisorNombre = Auth::user()->name ?? 'Revisor';
        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($user) {
            $user->notify(new RevisionIniciadaNotification($nombreCompleto, $postulante->nro_doc, $revisorNombre));

            // Enviar FCM push al postulante
            $firebaseService = new FirebaseService();
            $tokens = $user->fcmTokens()->pluck('token')->toArray();

            if (!empty($tokens)) {
                $firebaseService->sendToTokens(
                    $tokens,
                    'Revisión de documentos iniciada',
                    "{$revisorNombre} está revisando tus documentos",
                    [
                        'tipo' => 'revision_iniciada',
                        'postulante_dni' => $postulante->nro_doc,
                        'revisor_nombre' => $revisorNombre,
                    ],
                    route('postulante.documentos')
                );
            }
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Revisión iniciada. Se notificó al postulante.',
            'iniciada_at' => $solicitud->iniciada_at,
        ]);
    }

    /**
     * Marca al postulante como apto (el revisor da su visto bueno manualmente).
     */
    public function marcarApto(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        } else {
            $query->whereNull('finalizada_at');
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        if (!$solicitud->iniciada_at) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        if ($solicitud->finalizada_at) {
            return response()->json(['success' => false, 'mensaje' => 'La revisión ya fue completada'], 400);
        }

        $apto = !$solicitud->apto;
        $solicitud->apto = $apto;
        $solicitud->apto_at = $apto ? now() : null;
        $solicitud->save();

        return response()->json([
            'success' => true,
            'mensaje' => $apto ? 'Postulante marcado como apto' : 'Apto desmarcado',
            'apto' => $apto,
            'apto_at' => $solicitud->apto_at,
        ]);
    }

    /**
     * Finaliza la revisión:
     * - Si todos los documentos tienen visto bueno → apto=true, estado='completada', citación presencial.
     * - Si hay documentos observados/pendientes → apto=false, estado='pendiente', notifica al postulante corregir.
     */
    public function finalizarRevision(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        } else {
            $query->whereNull('finalizada_at');
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        // Si la solicitud encontrada ya fue finalizada, buscar la activa más reciente
        if ($solicitud->finalizada_at) {
            $solicitud = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereNull('finalizada_at')
                ->latest('id')
                ->first();
            if (!$solicitud) {
                return response()->json(['success' => false, 'mensaje' => 'La revisión ya fue completada'], 400);
            }
        }

        if (!$solicitud->iniciada_at) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        // Obtener documentos que pertenecen a los requisitos configurados
        $tiposEnRequisitos = DB::table('requisito_tipo_documento')
            ->pluck('id_tipo_documento')
            ->unique();

        $documentos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->whereIn('id_tipo_documento', $tiposEnRequisitos)
            ->get();

        $verificados = $documentos->filter(fn($d) => $d->valido)->pluck('nombre')->toArray();
        $pendientes = $documentos->filter(fn($d) => !$d->valido)->pluck('nombre')->toArray();

        // Determinar si todos los documentos están válidos
        $todosValidos = count($pendientes) === 0;

        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");
        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($todosValidos) {
            // ── CASO 1: Todos válidos → apto, completada, citación ──
            $request->validate([
                'fecha' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required',
                'lugar' => 'required|string|max:255',
                'instrucciones' => 'nullable|string|max:1000',
            ]);

            $datosCitacion = [
                'fecha' => $request->fecha,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'lugar' => $request->lugar,
                'instrucciones' => $request->instrucciones,
            ];

            $solicitud->finalizada_at = now();
            $solicitud->estado = 'completada';
            $solicitud->apto = true;
            $solicitud->apto_at = now();
            $solicitud->datos_citacion = $datosCitacion;
            $solicitud->documentos_verificados = $verificados;
            $solicitud->documentos_pendientes = [];
            $solicitud->save();

            $tieneActivas = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->exists();
            $postulante->tiene_revision_activa = $tieneActivas;
            $postulante->save();

            // Notificar al postulante: sin novedades, apto para revisión presencial
            if ($user) {
                $user->notify(new RevisionCompletadaNotification(
                    $nombreCompleto,
                    $postulante->nro_doc,
                    $verificados,
                    [],
                    $datosCitacion
                ));

                $firebaseService = new FirebaseService();
                $tokens = $user->fcmTokens()->pluck('token')->toArray();

                if (!empty($tokens)) {
                    $verificadosCount = count($verificados);
                    $firebaseService->sendToTokens(
                        $tokens,
                        'Revisión de documentos completada',
                        "Sin novedades. Cita presencial: {$datosCitacion['fecha']} · {$verificadosCount} aprobados",
                        [
                            'tipo' => 'revision_completada',
                            'postulante_dni' => $postulante->nro_doc,
                            'fecha_cita' => $datosCitacion['fecha'],
                        ],
                        route('postulante.documentos')
                    );
                }
            }

            // Registrar paso
            $idProceso = $solicitud->id_proceso ?: Auth::user()->id_proceso;
            if ($idProceso) {
                Paso::create([
                    'nombre' => 'REVISIÓN DE DOCUMENTOS COMPLETADA',
                    'nro' => 7,
                    'avance' => 7,
                    'postulante' => $postulante->id,
                    'proceso' => $idProceso,
                ]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Revisión finalizada. Todos los documentos están válidos. Se notificó al postulante para la citación presencial.',
                'resultado' => 'completada',
                'finalizada_at' => $solicitud->finalizada_at,
                'documentos_verificados' => $verificados,
                'documentos_pendientes' => [],
                'datos_citacion' => $datosCitacion,
            ]);
        } else {
            // ── CASO 2: Hay documentos observados → pendiente, sin citación ──
            // Recopilar observaciones de los documentos pendientes
            $observaciones = [];
            foreach ($documentos as $doc) {
                if (!$doc->valido) {
                    $observaciones[] = [
                        'nombre' => $doc->nombre,
                        'observacion' => $doc->observacion_revisor ?: 'Documento no validado',
                    ];
                }
            }

            $solicitud->finalizada_at = now();
            $solicitud->estado = 'pendiente';
            $solicitud->apto = false;
            $solicitud->apto_at = null;
            $solicitud->datos_citacion = null;
            $solicitud->documentos_verificados = $verificados;
            $solicitud->documentos_pendientes = $pendientes;
            $solicitud->save();

            // El postulante sigue teniendo revisión activa (pendiente)
            $postulante->tiene_revision_activa = true;
            $postulante->save();

            // Notificar al postulante: tiene documentos por corregir
            if ($user) {
                $user->notify(new RevisionCompletadaNotification(
                    $nombreCompleto,
                    $postulante->nro_doc,
                    $verificados,
                    $pendientes,
                    []
                ));

                $firebaseService = new FirebaseService();
                $tokens = $user->fcmTokens()->pluck('token')->toArray();

                if (!empty($tokens)) {
                    $firebaseService->sendToTokens(
                        $tokens,
                        'Documentos por corregir',
                        count($pendientes) . ' documento(s) observado(s). Debes corregirlos y solicitar nuevamente revisión.',
                        [
                            'tipo' => 'revision_pendiente',
                            'postulante_dni' => $postulante->nro_doc,
                        ],
                        route('postulante.documentos')
                    );
                }
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Revisión finalizada con observaciones. El postulante debe corregir ' . count($pendientes) . ' documento(s).',
                'resultado' => 'pendiente',
                'finalizada_at' => $solicitud->finalizada_at,
                'documentos_verificados' => $verificados,
                'documentos_pendientes' => $pendientes,
                'observaciones' => $observaciones,
                'datos_citacion' => null,
            ]);
        }
    }

    /**
     * Re-notifica al postulante para que actualice/corraja sus documentos observados.
     */
    public function renotificarPostulante(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', 'pendiente');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return response()->json(['success' => false, 'mensaje' => 'No hay revisión pendiente para este postulante'], 400);
        }

        $pendientes = $solicitud->documentos_pendientes ?? [];
        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");

        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($user) {
            $firebaseService = new FirebaseService();
            $tokens = $user->fcmTokens()->pluck('token')->toArray();

            if (!empty($tokens)) {
                $firebaseService->sendToTokens(
                    $tokens,
                    'Recordatorio: Actualiza tus documentos',
                    'Tienes ' . count($pendientes) . ' documento(s) por corregir. Actualízalos y solicita nuevamente revisión.',
                    [
                        'tipo' => 'revision_renotificar',
                        'postulante_dni' => $postulante->nro_doc,
                    ],
                    route('postulante.documentos')
                );
            }
        }

        Log::info('Re-notificación enviada al postulante', [
            'postulante' => $postulante->nro_doc,
            'revisor' => Auth::id(),
            'solicitud' => $solicitud->id,
            'pendientes' => count($pendientes),
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Se envió recordatorio al postulante para que actualice sus documentos.',
        ]);
    }

    /**
     * Revisión rápida: marca todos los documentos del postulante como válidos.
     */
    public function revisionRapida(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        } else {
            $query->whereNull('finalizada_at');
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        if ($solicitud->finalizada_at) {
            return response()->json(['success' => false, 'mensaje' => 'La revisión ya fue completada'], 400);
        }

        $revisorId = Auth::id();
        $ahora = now();

        // Solo marcar documentos que pertenecen a los tipos configurados en requisitos
        $tiposEnRequisitos = DB::table('requisito_tipo_documento')
            ->pluck('id_tipo_documento')
            ->unique();

        $count = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->whereIn('id_tipo_documento', $tiposEnRequisitos)
            ->update([
                'apto_revision' => true,
                'valido' => true,
                'revisado_at' => $ahora,
                'validado_at' => $ahora,
                'id_revisor' => $revisorId,
                'verificado' => 1,
            ]);

        return response()->json([
            'success' => true,
            'mensaje' => "{$count} documento(s) marcado(s) como válidos",
            'count' => $count,
        ]);
    }

    /**
     * Cambia el estado de un documento individual (apto_revision → valido, secuencial).
     */
    public function cambiarEstadoDocumento(Request $request)
    {
        $request->validate([
            'id_documento' => 'required|integer',
            'accion' => 'required|string|in:apto_revision,valido,desmarcar',
            'fecha_caducidad' => 'nullable|date',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $documento = Documento::find($request->id_documento);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        $postulante = Postulante::find($documento->id_postulante);

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        // Validar que haya una revisión iniciada
        $solicitud = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada')
            ->whereNull('finalizada_at')
            ->latest('id')
            ->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        if ($solicitud->finalizada_at) {
            return response()->json(['success' => false, 'mensaje' => 'La revisión ya fue completada'], 400);
        }

        $accion = $request->accion;
        $revisorId = Auth::id();

        if ($accion === 'apto_revision') {
            if ($documento->apto_revision) {
                return response()->json(['success' => false, 'mensaje' => 'El documento ya está marcado como apto para revisión'], 400);
            }
            $documento->apto_revision = true;
            $documento->revisado_at = now();
            $documento->id_revisor = $revisorId;
            $documento->save();

            return response()->json([
                'success' => true,
                'mensaje' => 'Documento marcado como apto para revisión',
                'datos' => [
                    'apto_revision' => true,
                    'valido' => $documento->valido,
                    'revisado_at' => $documento->revisado_at?->format('d/m/Y H:i'),
                ],
            ]);
        }

        if ($accion === 'valido') {
            if (!$documento->apto_revision) {
                return response()->json(['success' => false, 'mensaje' => 'Debe marcar el documento como apto para revisión primero'], 400);
            }
            if ($documento->valido) {
                return response()->json(['success' => false, 'mensaje' => 'El documento ya está marcado como válido'], 400);
            }
            $documento->valido = true;
            $documento->validado_at = now();
            $documento->id_revisor = $revisorId;
            $documento->verificado = 1; // compatibilidad legacy
            if ($request->fecha_caducidad) {
                $documento->fecha_caducidad = $request->fecha_caducidad;
            }
            if ($request->observacion) {
                $documento->observacion_revisor = $request->observacion;
            }
            $documento->save();

            return response()->json([
                'success' => true,
                'mensaje' => 'Documento marcado como válido',
                'datos' => [
                    'apto_revision' => true,
                    'valido' => true,
                    'fecha_caducidad' => $documento->fecha_caducidad?->format('Y-m-d'),
                    'validado_at' => $documento->validado_at?->format('d/m/Y H:i'),
                ],
            ]);
        }

        if ($accion === 'desmarcar') {
            if ($documento->valido) {
                $documento->valido = false;
                $documento->validado_at = null;
                $documento->verificado = 0;
                $documento->save();

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Documento válido desmarcado',
                    'datos' => [
                        'apto_revision' => $documento->apto_revision,
                        'valido' => false,
                    ],
                ]);
            } elseif ($documento->apto_revision) {
                $documento->apto_revision = false;
                $documento->revisado_at = null;
                $documento->save();

                return response()->json([
                    'success' => true,
                    'mensaje' => 'Apto para revisión desmarcado',
                    'datos' => [
                        'apto_revision' => false,
                        'valido' => false,
                    ],
                ]);
            }

            return response()->json(['success' => false, 'mensaje' => 'El documento no tiene estados para desmarcar'], 400);
        }

        return response()->json(['success' => false, 'mensaje' => 'Acción no válida'], 400);
    }

    /**
     * Guarda o actualiza una observación del revisor sobre un documento.
     */
    public function observarDocumento(Request $request)
    {
        $request->validate([
            'id_documento' => 'required|integer',
            'observacion' => 'nullable|string|max:1000',
        ]);

        $documento = Documento::find($request->id_documento);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        $postulante = Postulante::find($documento->id_postulante);

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        // Validar que haya una revisión en curso
        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($request->input('solicitud_id')) {
            $query->where('id', $request->input('solicitud_id'));
        } else {
            $query->whereNull('finalizada_at');
        }
        $solicitud = $query->latest('id')->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return response()->json(['success' => false, 'mensaje' => 'Debe iniciar la revisión primero'], 400);
        }

        if ($solicitud->finalizada_at) {
            return response()->json(['success' => false, 'mensaje' => 'La revisión ya fue completada'], 400);
        }

        $documento->observacion_revisor = $request->observacion ?: null;
        $documento->id_revisor = Auth::id();
        $documento->save();

        return response()->json([
            'success' => true,
            'mensaje' => $request->observacion ? 'Observación guardada' : 'Observación eliminada',
            'datos' => [
                'observacion_revisor' => $documento->observacion_revisor,
            ],
        ]);
    }

    /**
     * Obtiene la citación sugerida automáticamente para un postulante.
     */
    public function citacionSugerida(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $idProceso = Auth::user()->id_proceso;

        // Buscar preinscripción para obtener programa y modalidad
        $preinscripcion = Preinscripcion::where('id_postulante', $postulante->id)
            ->where('id_proceso', $idProceso)
            ->latest()
            ->first();

        $citacionService = new CitacionService();
        $citacion = $citacionService->calcularCitacion(
            $idProceso,
            $dni,
            $preinscripcion?->id_programa,
            $preinscripcion?->id_modalidad
        );

        if (!$citacion) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No hay configuración de citación para este proceso',
            ]);
        }

        return response()->json([
            'success' => true,
            'citacion' => [
                'fecha' => $citacion->fecha?->format('Y-m-d'),
                'hora_inicio' => $citacion->hora_inicio,
                'hora_fin' => $citacion->hora_fin,
                'lugar' => $citacion->lugar,
                'instrucciones' => $citacion->instrucciones,
                'tipo_criterio' => $citacion->tipo_criterio,
                'valor' => $citacion->valor,
            ],
        ]);
    }

    /**
     * Obtiene los documentos del postulante agrupados por requisitos configurados.
     * Muestra todos los documentos subidos por tipo_documento, filtrado por la modalidad del postulante.
     */
    public function documentosPorRequisitos(Request $request, string $dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        // Obtener la modalidad y programa desde la solicitud de revisión específica (si viene solicitud_id)
        // o la última activa, fallback a la última de cualquier estado
        $solicitudId = $request->query('solicitud');

        $solicitudActiva = null;
        if ($solicitudId) {
            $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('id', $solicitudId)
                ->first();
        }

        if (!$solicitudActiva) {
            $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereNull('finalizada_at')
                ->latest('id')
                ->first();
        }

        if (!$solicitudActiva) {
            $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->latest('id')
                ->first();
        }

        $idModalidadPostulante = $solicitudActiva?->id_modalidad;
        $idProgramaPostulante = $solicitudActiva?->id_programa;

        if (!$idModalidadPostulante) {
            $preinscripcion = Preinscripcion::where('id_postulante', $postulante->id)
                ->latest()
                ->first();
            $idModalidadPostulante = $preinscripcion?->id_modalidad;
            $idProgramaPostulante = $idProgramaPostulante ?? $preinscripcion?->id_programa;

            if (!$idModalidadPostulante) {
                $inscripcion = Inscripcion::where('id_postulante', $postulante->id)
                    ->where('estado', 1)
                    ->orderByDesc('id')
                    ->first();
                $idProgramaPostulante = $idProgramaPostulante ?? $inscripcion?->id_programa;
            }
        }

        // Cargar requisitos activos con sus relaciones
        $requisitos = RequisitoDocumento::with([
            'modalidades:id,nombre,codigo',
            'programas:id,nombre,nombre_corto',
            'tiposDocumento:id,nombre,codigo'
        ])
            ->where('estado', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        // Documentos subidos por el postulante, agrupados por id_tipo_documento
        $documentosSubidos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->get()
            ->groupBy('id_tipo_documento');

        // Filtrar requisitos por la modalidad del postulante
        // Si no hay modalidad definida (sin preinscripción), mostrar todos
        $requisitosFiltrados = $requisitos->filter(function ($req) use ($idModalidadPostulante) {
            if (!$idModalidadPostulante) {
                return true;
            }
            if ($req->modalidades->isEmpty()) {
                // Si no tiene modalidades asignadas, aplica a todas
                return true;
            }
            return $req->modalidades->contains('id', $idModalidadPostulante);
        });

        $requisitosData = $requisitosFiltrados->map(function ($req) use ($documentosSubidos, $idProgramaPostulante) {
            // Los tipos de documento son opciones para cumplir el requisito
            $tiposData = $req->tiposDocumento->map(function ($td) use ($documentosSubidos) {
                $subidos = $documentosSubidos->get($td->id, collect());
                // Mostrar TODOS los documentos subidos para este tipo con nuevos estados
                $docsData = $subidos->map(function ($doc) {
                    $hoy = now()->startOfDay();
                    $vigente = !$doc->fecha_caducidad || $doc->fecha_caducidad >= $hoy;
                    $porVencer = $doc->fecha_caducidad && $doc->fecha_caducidad < $hoy->copy()->addDays(30) && $doc->fecha_caducidad >= $hoy;
                    $caducado = $doc->fecha_caducidad && $doc->fecha_caducidad < $hoy;

                    return [
                        'id' => $doc->id,
                        'nombre' => $doc->nombre,
                        'url' => $doc->url,
                        'seleccionado' => $doc->seleccionado,
                        'apto_revision' => $doc->apto_revision,
                        'valido' => $doc->valido,
                        'verificado' => $doc->valido,
                        'fecha_caducidad' => $doc->fecha_caducidad?->format('Y-m-d'),
                        'observacion_revisor' => $doc->observacion_revisor,
                        'revisado_at' => $doc->revisado_at?->format('d/m/Y H:i'),
                        'validado_at' => $doc->validado_at?->format('d/m/Y H:i'),
                        'vigente' => $vigente,
                        'por_vencer' => $porVencer,
                        'caducado' => $caducado,
                    ];
                })->values()->toArray();
                return [
                    'id' => $td->id,
                    'nombre' => $td->nombre,
                    'codigo' => $td->codigo,
                    'subido' => $subidos->isNotEmpty(),
                    'verificado' => $subidos->contains(fn($d) => $d->valido),
                    'documentos' => $docsData,
                ];
            });

            $tipoSubido = $tiposData->firstWhere('subido', true);
            $reqCumplido = $tipoSubido !== null;
            $reqVerificado = $tiposData->contains(fn($td) => $td['subido'] && $td['verificado']);

            // Lógica de obligatoriedad según programa
            $programasRequisito = $req->programas;
            $tieneProgramas = $programasRequisito->count() > 0;

            if ($req->obligatorio) {
                $obligatorioParaPostulante = true;
                $noAplica = false;
            } elseif ($tieneProgramas && $idProgramaPostulante) {
                $idsProgramasReq = $programasRequisito->pluck('id')->toArray();
                if (in_array($idProgramaPostulante, $idsProgramasReq)) {
                    $obligatorioParaPostulante = true;
                    $noAplica = false;
                } else {
                    $obligatorioParaPostulante = false;
                    $noAplica = true;
                }
            } else {
                $obligatorioParaPostulante = false;
                $noAplica = false;
            }

            return [
                'id' => $req->id,
                'nombre' => $req->nombre,
                'obligatorio' => $req->obligatorio,
                'orden' => $req->orden,
                'obligatorio_para_postulante' => $obligatorioParaPostulante,
                'no_aplica' => $noAplica,
                'modalidades' => $req->modalidades->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre, 'codigo' => $m->codigo])->toArray(),
                'programas' => $req->programas->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre, 'nombre_corto' => $p->nombre_corto])->toArray(),
                'tipos_documento' => $tiposData->values()->toArray(),
                'cumplido' => $reqCumplido,
                'verificado' => $reqVerificado,
            ];
        });

        // Requisitos obligatorios pendientes
        $obligatoriosPendientes = $requisitosData->filter(fn($r) => $r['obligatorio_para_postulante'] && !$r['no_aplica'] && !$r['cumplido'])->count();

        // Información de la modalidad del postulante
        $modalidadInfo = null;
        if ($idModalidadPostulante) {
            $modalidad = DB::table('modalidad')->where('id', $idModalidadPostulante)->first();
            $modalidadInfo = $modalidad ? ['id' => $modalidad->id, 'nombre' => $modalidad->nombre, 'codigo' => $modalidad->codigo] : null;
        }

        $programaInfo = null;
        if ($idProgramaPostulante) {
            $programa = DB::table('programa')->where('id', $idProgramaPostulante)->first();
            $programaInfo = $programa ? ['id' => $programa->id, 'nombre' => $programa->nombre, 'nombre_corto' => $programa->nombre_corto ?? null] : null;
        }

        // Leer estado de revisión desde la solicitud específica (si se pasó solicitud_id)
        // o desde la última activa
        if ($solicitudId && $solicitudActiva) {
            $revisionSolicitada = $solicitudActiva->estado !== 'completada';
        } else {
            $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereNull('finalizada_at')
                ->latest('id')
                ->first();
            $revisionSolicitada = $solicitudActiva !== null;
        }
        $revisionIniciadaAt = $solicitudActiva?->iniciada_at;
        $revisionFinalizadaAt = $solicitudActiva?->finalizada_at;
        $revisionApto = $solicitudActiva?->apto ?? false;
        $revisionAptoAt = $solicitudActiva?->apto_at;
        $revisionRevisorId = $solicitudActiva?->revisor_id;

        // Si no hay activa, buscar la última completada para mostrar histórico
        if (!$solicitudActiva) {
            $ultimaCompletada = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', 'completada')
                ->latest('id')
                ->first();
            if ($ultimaCompletada) {
                $revisionFinalizadaAt = $ultimaCompletada->finalizada_at;
            }
        }

        // Incluir datos del postulante para que el frontend funcione incluso si getPostulanteByDni falla
        return response()->json([
            'success' => true,
            'datos' => [
                'requisitos' => $requisitosData->values()->toArray(),
                'modalidad_postulante' => $modalidadInfo,
                'programa_postulante' => $programaInfo,
                'postulante' => [
                    'nombres' => $postulante->nombres,
                    'primer_apellido' => $postulante->primer_apellido,
                    'segundo_apellido' => $postulante->segundo_apellido,
                    'revision_solicitada' => $revisionSolicitada,
                    'revision_iniciada_at' => $revisionIniciadaAt,
                    'revision_finalizada_at' => $revisionFinalizadaAt,
                    'revision_apto' => $revisionApto,
                    'revision_apto_at' => $revisionAptoAt,
                    'revision_revisor_id' => $revisionRevisorId,
                    'revision_estado' => $solicitudActiva?->estado,
                ],
                'apto_revision' => $revisionApto,
                'apto_at' => $revisionAptoAt,
                'obligatorios_pendientes' => $obligatoriosPendientes,
                'total_requisitos' => $requisitosData->count(),
                'requisitos_cumplidos' => $requisitosData->filter(fn($r) => $r['cumplido'])->count(),
            ],
        ]);
    }
}
