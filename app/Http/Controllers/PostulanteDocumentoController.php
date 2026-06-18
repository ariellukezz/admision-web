<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequisitoDocumento;
use App\Models\Documento;
use App\Models\Inscripcion;
use App\Models\Postulante;
use App\Models\RevisionSolicitud;
use App\Models\User;
use App\Models\FcmToken;
use App\Services\DocumentoStorageService;
use App\Services\FirebaseService;
use App\Notifications\SolicitudRevisionNotification;
use App\Notifications\RevisionDocumentosRevisorNotification;
use App\Mail\SolicitudRevisionMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PostulanteDocumentoController extends Controller
{
    private DocumentoStorageService $storageService;

    public function __construct(DocumentoStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function getRequisitos()
    {
        $user = Auth::user();
        $idProceso = $user->id_proceso;

        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();
        if (!$postulante) {
            return response()->json(['success' => true, 'datos' => ['requisitos' => [], 'modalidades' => [], 'total_requisitos' => 0, 'completados' => 0, 'con_programas' => false, 'id_programa_postulante' => null]]);
        }

        // Buscar programa del postulante desde su inscripción
        $inscripcion = Inscripcion::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->orderByDesc('id')
            ->first();
        $idProgramaPostulante = $inscripcion?->id_programa;

        $requisitos = RequisitoDocumento::with([
            'modalidades:id,nombre,codigo',
            'programas:id,nombre,nombre_corto',
            'tiposDocumento:id,nombre,codigo'
        ])
            ->where('estado', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        // Marcar como inactivos los documentos caducados
        $hoy = now()->startOfDay();
        Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->whereNotNull('fecha_caducidad')
            ->where('fecha_caducidad', '<', $hoy)
            ->update(['estado' => 0]);

        $documentosSubidos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->get()
            ->groupBy('id_tipo_documento');

        // Calcular modalidadIds antes de usarlos
        $modalidadIds = $requisitos->pluck('modalidades')->flatten()->pluck('id')->unique()->values();

        // Modalidades con revisión pendiente (para bloquear edición per-modalidad)
        $modalidadesConRevisionPendiente = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada')
            ->whereIn('id_modalidad', $modalidadIds)
            ->pluck('id_modalidad')
            ->unique()
            ->toArray();

        // tieneRevisionActiva derivado de la tabla (no del flag almacenado)
        $tieneRevisionActiva = count($modalidadesConRevisionPendiente) > 0;

        // Sincronizar flag global en postulante con el estado real
        $postulanteModel = Postulante::find($postulante->id);
        if ($postulanteModel && $postulanteModel->tiene_revision_activa !== $tieneRevisionActiva) {
            $postulanteModel->tiene_revision_activa = $tieneRevisionActiva;
            $postulanteModel->save();
        }

        // Mapear modalidades con su estado de revisión
        $modalidadesConRevision = DB::table('modalidad')
            ->whereIn('id', $modalidadIds)
            ->select('id', 'nombre', 'codigo')
            ->orderBy('nombre')
            ->get()
            ->map(function ($m) use ($postulante, $modalidadesConRevisionPendiente) {
                $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
                    ->where('id_modalidad', $m->id)
                    ->where('estado', '!=', 'completada')
                    ->latest('id')
                    ->first();

                $veces = RevisionSolicitud::where('id_postulante', $postulante->id)
                    ->where('id_modalidad', $m->id)
                    ->count();

                $puedeSolicitar = true;
                $proximaInsistencia = null;
                if ($solicitudActiva) {
                    $horas = now()->diffInHours($solicitudActiva->solicitada_at);
                    if ($horas < 24) {
                        $puedeSolicitar = false;
                        $proximaInsistencia = $solicitudActiva->solicitada_at->copy()->addHours(24)->format('d/m/Y H:i');
                    }
                }

                return [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'codigo' => $m->codigo,
                    'tiene_revision_pendiente' => $solicitudActiva !== null,
                    'puede_solicitar' => $puedeSolicitar,
                    'proxima_insistencia' => $proximaInsistencia,
                    'veces' => $veces,
                ];
            });

        $requisitos->each(function ($req) use ($documentosSubidos, $idProgramaPostulante, $modalidadesConRevisionPendiente) {
            // Determinar si este requisito tiene revisión pendiente en alguna de sus modalidades
            $reqModalidadIds = $req->modalidades->pluck('id')->toArray();
            $reqTieneRevisionPendiente = !empty(array_intersect($reqModalidadIds, $modalidadesConRevisionPendiente));

            // Los tipos de documento son OPCIONES para cumplir el requisito
            // Solo se necesita subir UNO de ellos
            $req->tiposDocumento->each(function ($td) use ($documentosSubidos, $reqTieneRevisionPendiente) {
                $subidos = $documentosSubidos->get($td->id, collect());
                $td->subido = $subidos->count() > 0;

                // Mostrar TODOS los documentos subidos para este tipo
                $td->documentos = $subidos->map(function ($doc) use ($reqTieneRevisionPendiente) {
                    return [
                        'id' => $doc->id,
                        'nombre' => $doc->nombre,
                        'url' => $doc->url,
                        'seleccionado' => $doc->seleccionado,
                        'apto_revision' => $doc->apto_revision,
                        'valido' => $doc->valido,
                        'fecha_caducidad' => $doc->fecha_caducidad?->format('Y-m-d'),
                        'observacion_revisor' => $doc->observacion_revisor,
                        'revisado_at' => $doc->revisado_at?->format('d/m/Y H:i'),
                        'validado_at' => $doc->validado_at?->format('d/m/Y H:i'),
                        // Bloquear edición si hay revisión pendiente para esta modalidad y el doc no ha sido revisado
                        'puede_editar' => !$reqTieneRevisionPendiente || $doc->revisado_at !== null,
                    ];
                })->values()->toArray();

                $td->verificado = $subidos->contains(fn($d) => $d->valido);
                $td->documento_id = $subidos->firstWhere('seleccionado', true)?->id ?? $subidos->first()?->id;
            });

            // El requisito se cumple si AL MENOS un tipo tiene documento subido
            $tipoSubido = $req->tiposDocumento->firstWhere('subido', true);
            $req->documento_subido = $tipoSubido !== null;
            $req->documento_id = $tipoSubido?->documento_id;
            $req->tipo_subido_nombre = $tipoSubido?->nombre;
            $req->verificado = $tipoSubido?->verificado ?? false;
            $req->porcentaje = $req->documento_subido ? 100 : 0;

            // Lógica de obligatoriedad según programa del postulante
            $programasRequisito = $req->programas;
            $tieneProgramas = $programasRequisito->count() > 0;

            if ($req->obligatorio) {
                // Siempre obligatorio, sin importar programa
                $req->obligatorio_para_postulante = true;
                $req->no_aplica = false;
                $req->programas_obligatorios = [];
            } elseif ($tieneProgramas && $idProgramaPostulante) {
                // Opcional pero con programas específicos
                $idsProgramasReq = $programasRequisito->pluck('id')->toArray();
                if (in_array($idProgramaPostulante, $idsProgramasReq)) {
                    // El programa del postulante está en la lista → es obligatorio para él
                    $req->obligatorio_para_postulante = true;
                    $req->no_aplica = false;
                } else {
                    // El programa del postulante NO está → no aplica
                    $req->obligatorio_para_postulante = false;
                    $req->no_aplica = true;
                }
                $req->programas_obligatorios = $programasRequisito->pluck('nombre_corto')->filter()->values()->toArray();
                if (empty($req->programas_obligatorios)) {
                    $req->programas_obligatorios = $programasRequisito->pluck('nombre')->values()->toArray();
                }
            } elseif ($tieneProgramas && !$idProgramaPostulante) {
                // Opcional con programas pero postulante sin inscripción → mostrar como opcional con nota
                $req->obligatorio_para_postulante = false;
                $req->no_aplica = false;
                $req->programas_obligatorios = $programasRequisito->pluck('nombre_corto')->filter()->values()->toArray();
                if (empty($req->programas_obligatorios)) {
                    $req->programas_obligatorios = $programasRequisito->pluck('nombre')->values()->toArray();
                }
            } else {
                // Opcional sin programas específicos → aplica a todos como opcional
                $req->obligatorio_para_postulante = false;
                $req->no_aplica = false;
                $req->programas_obligatorios = [];
            }
        });

        $modalidades = $modalidadesConRevision;

        $conProgramas = $requisitos->filter(fn($r) => $r->programas->count() > 0)->count() > 0;

        return response()->json([
            'success' => true,
            'datos' => [
                'requisitos' => $requisitos,
                'modalidades' => $modalidades,
                'total_requisitos' => $requisitos->count(),
                'completados' => $requisitos->filter(fn($r) => $r->porcentaje === 100)->count(),
                'con_programas' => $conProgramas,
                'id_programa_postulante' => $idProgramaPostulante,
                'tiene_revision_activa' => $tieneRevisionActiva,
            ],
        ]);
    }

    public function subirDocumento(Request $request)
    {
        $request->validate([
            'id_tipo_documento' => 'required|integer',
            'archivo' => 'required|file|mimes:pdf|max:20480',
        ]);

        $user = Auth::user();
        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        // Validar máximo 10 documentos por requisito (sumando todos los tipos del requisito)
        $idTiposDelRequisito = DB::table('requisito_tipo_documento')
            ->where('id_tipo_documento', $request->id_tipo_documento)
            ->pluck('id_requisito_documento');

        if ($idTiposDelRequisito->isNotEmpty()) {
            $tiposDelRequisito = DB::table('requisito_tipo_documento')
                ->whereIn('id_requisito_documento', $idTiposDelRequisito)
                ->pluck('id_tipo_documento')
                ->unique();

            $countDocsRequisito = Documento::where('id_postulante', $postulante->id)
                ->where('estado', 1)
                ->where('is_deleted', false)
                ->whereIn('id_tipo_documento', $tiposDelRequisito)
                ->count();

            if ($countDocsRequisito >= 10) {
                return response()->json(['success' => false, 'mensaje' => 'Has alcanzado el límite de 10 documentos por requisito'], 400);
            }
        }

        try {
            $documento = $this->storageService->saveFile(
                $request->file('archivo'),
                $user->id,
                $postulante->id,
                $request->id_tipo_documento,
            );

            return response()->json([
                'success' => true,
                'mensaje' => 'Documento subido correctamente',
                'datos' => $documento,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 400);
        } catch (\Throwable $e) {
            \Log::error('Error subirDocumento: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'mensaje' => 'Error al subir documento: ' . $e->getMessage()], 500);
        }
    }

    public function misDocumentos(Request $request)
    {
        $user = Auth::user();
        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => true, 'datos' => []]);
        }

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        $result = $this->storageService->getFiles($page, $limit, $postulante->id);

        return response()->json(['success' => true, 'datos' => $result]);
    }

    public function descargarDocumento(int $id)
    {
        $user = Auth::user();
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();
        if (!$postulante || $documento->id_postulante !== $postulante->id) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado'], 403);
        }

        try {
            return $this->storageService->downloadFile($id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    public function previewDocumento(int $id)
    {
        $user = Auth::user();
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            abort(404);
        }

        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();
        if (!$postulante || $documento->id_postulante !== $postulante->id) {
            abort(403);
        }

        try {
            return $this->storageService->previewFile($id);
        } catch (\RuntimeException $e) {
            abort(404);
        }
    }

    public function actualizarDocumento(Request $request, int $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $user = Auth::user();
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();
        if (!$postulante || $documento->id_postulante !== $postulante->id) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado'], 403);
        }

        // Bloquear edición si hay revisión pendiente para la modalidad del documento y no ha sido revisado
        if (!$documento->revisado_at) {
            $modalidadesDelDoc = DB::table('requisito_tipo_documento')
                ->join('requisito_modalidad', 'requisito_tipo_documento.id_requisito_documento', '=', 'requisito_modalidad.id_requisito_documento')
                ->where('requisito_tipo_documento.id_tipo_documento', $documento->id_tipo_documento)
                ->pluck('requisito_modalidad.id_modalidad')
                ->unique()
                ->toArray();

            $tieneRevisionPendiente = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereIn('id_modalidad', $modalidadesDelDoc)
                ->exists();

            if ($tieneRevisionPendiente) {
                return response()->json(['success' => false, 'mensaje' => 'No puedes modificar documentos enviados a revisión hasta que sean revisados'], 403);
            }
        }

        try {
            $documento = $this->storageService->updateFile($id, $request->file('archivo'), $user->id);

            return response()->json([
                'success' => true,
                'mensaje' => 'Documento actualizado correctamente',
                'datos' => $documento,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 403);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'mensaje' => 'Error al actualizar documento'], 500);
        }
    }

    public function eliminarDocumento(int $id)
    {
        $user = Auth::user();
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();
        if (!$postulante || $documento->id_postulante !== $postulante->id) {
            return response()->json(['success' => false, 'mensaje' => 'No autorizado'], 403);
        }

        // Bloquear eliminación si hay revisión pendiente para la modalidad del documento y no ha sido revisado
        if (!$documento->revisado_at) {
            $modalidadesDelDoc = DB::table('requisito_tipo_documento')
                ->join('requisito_modalidad', 'requisito_tipo_documento.id_requisito_documento', '=', 'requisito_modalidad.id_requisito_documento')
                ->where('requisito_tipo_documento.id_tipo_documento', $documento->id_tipo_documento)
                ->pluck('requisito_modalidad.id_modalidad')
                ->unique()
                ->toArray();

            $tieneRevisionPendiente = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereIn('id_modalidad', $modalidadesDelDoc)
                ->exists();

            if ($tieneRevisionPendiente) {
                return response()->json(['success' => false, 'mensaje' => 'No puedes eliminar documentos enviados a revisión hasta que sean revisados'], 403);
            }
        }

        try {
            $result = $this->storageService->deleteFile($id, $user->id);

            return response()->json(['success' => true, 'mensaje' => $result['message']]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    public function eliminarDocumentoPorTipo(int $idTipoDocumento)
    {
        $user = Auth::user();
        $postulante = DB::table('postulante')->where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $documento = Documento::where('id_postulante', $postulante->id)
            ->where('id_tipo_documento', $idTipoDocumento)
            ->where('estado', 1)
            ->first();

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        try {
            $result = $this->storageService->deleteFile($documento->id, $user->id);

            return response()->json(['success' => true, 'mensaje' => $result['message']]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    public function solicitarRevision(Request $request)
    {
        $user = Auth::user();
        $postulante = Postulante::where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'No tienes datos de postulante registrados'], 404);
        }

        // Verificar que completó sus datos personales (avance >= 5)
        $idProceso = $user->id_proceso;
        $avanceRecord = \App\Models\AvancePostulante::where('dni_postulante', $postulante->nro_doc)
            ->where('id_proceso', $idProceso)
            ->first();
        $avance = $avanceRecord ? $avanceRecord->avance : 0;

        $pasosCompletados = \App\Models\Paso::where('postulante', $postulante->id)
            ->where('proceso', $idProceso)
            ->pluck('nro')
            ->toArray();
        if (!empty($pasosCompletados)) {
            $avance = max($pasosCompletados);
        }

        if ($avance < 5) {
            return response()->json(['success' => false, 'mensaje' => 'Debes completar tus datos personales antes de solicitar la revisión'], 400);
        }

        // Verificar que tiene al menos un documento subido
        $documentosSubidos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->count();

        if ($documentosSubidos === 0) {
            return response()->json(['success' => false, 'mensaje' => 'Debes subir al menos un documento antes de solicitar la revisión'], 400);
        }

        // Obtener modalidad desde el request (selección del postulante) o desde preinscripción
        $idModalidad = $request->input('id_modalidad');
        $idPrograma = null;

        if (!$idModalidad) {
            $preinscripcion = \App\Models\Preinscripcion::where('id_postulante', $postulante->id)
                ->latest('id')
                ->first();
            $idModalidad = $preinscripcion?->id_modalidad;
            $idPrograma = $preinscripcion?->id_programa;
        }

        if (!$idModalidad) {
            $inscripcion = Inscripcion::where('id_postulante', $postulante->id)
                ->where('estado', 1)
                ->orderByDesc('id')
                ->first();
            $idPrograma = $idPrograma ?? $inscripcion?->id_programa;
        }

        if (!$idModalidad) {
            return response()->json(['success' => false, 'mensaje' => 'Debes seleccionar una modalidad'], 400);
        }

        // Validar cooldown de 24 horas POR MODALIDAD
        $solicitudPendiente = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('id_modalidad', $idModalidad)
            ->where('estado', '!=', 'completada')
            ->latest('id')
            ->first();

        if ($solicitudPendiente) {
            $horasTranscurridas = now()->diffInHours($solicitudPendiente->solicitada_at);
            if ($horasTranscurridas < 24) {
                $proximaInsistencia = $solicitudPendiente->solicitada_at->copy()->addHours(24);
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Debes esperar hasta el ' . $proximaInsistencia->format('d/m/Y H:i') . ' para insistir en la revisión de esta modalidad',
                ], 400);
            }
        }

        // Validar que los requisitos obligatorios tengan documento subido
        $requisitos = RequisitoDocumento::with(['tiposDocumento:id,nombre,codigo'])
            ->where('estado', true)
            ->orderBy('orden')
            ->get();

        $inscripcion = Inscripcion::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->orderByDesc('id')
            ->first();
        $idProgramaPostulante = $idPrograma ?? $inscripcion?->id_programa;

        $obligatoriosSinDocumento = [];

        foreach ($requisitos as $req) {
            // Determinar obligatoriedad
            $esObligatorio = $req->obligatorio;
            if (!$esObligatorio && $req->programas->count() > 0 && $idProgramaPostulante) {
                $esObligatorio = $req->programas->contains('id', $idProgramaPostulante);
            }

            // Filtrar por modalidad
            if ($idModalidad && $req->modalidades->count() > 0 && !$req->modalidades->contains('id', $idModalidad)) {
                continue;
            }

            if ($esObligatorio) {
                $tiposDelRequisito = DB::table('requisito_tipo_documento')
                    ->where('id_requisito_documento', $req->id)
                    ->pluck('id_tipo_documento')
                    ->toArray();

                $tieneDoc = Documento::where('id_postulante', $postulante->id)
                    ->where('estado', 1)
                    ->where('is_deleted', false)
                    ->whereIn('id_tipo_documento', $tiposDelRequisito)
                    ->exists();

                if (!$tieneDoc) {
                    $obligatoriosSinDocumento[] = $req->nombre;
                }
            }
        }

        if (!empty($obligatoriosSinDocumento)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Debes subir un documento para los siguientes requisitos obligatorios: ' . implode(', ', $obligatoriosSinDocumento),
            ], 400);
        }

        // Reabrir solicitud existente o crear nueva
        if ($solicitudPendiente) {
            // >= 24h: reabrir la solicitud existente en lugar de crear una nueva
            $veces = $solicitudPendiente->veces + 1;
            $solicitudPendiente->solicitada_at = now();
            $solicitudPendiente->veces = $veces;
            $solicitudPendiente->estado = 'solicitada';
            $solicitudPendiente->iniciada_at = null;
            $solicitudPendiente->finalizada_at = null;
            $solicitudPendiente->apto = false;
            $solicitudPendiente->apto_at = null;
            $solicitudPendiente->revisor_id = null;
            $solicitudPendiente->documentos_seleccionados = null;
            $solicitudPendiente->documentos_verificados = null;
            $solicitudPendiente->documentos_pendientes = null;
            $solicitudPendiente->datos_citacion = null;
            $solicitudPendiente->save();
        } else {
            // No hay solicitud pendiente: crear nueva
            $veces = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('id_modalidad', $idModalidad)
                ->count() + 1;
            RevisionSolicitud::create([
                'id_postulante' => $postulante->id,
                'id_proceso' => $idProceso,
                'id_modalidad' => $idModalidad,
                'id_programa' => $idPrograma,
                'veces' => $veces,
                'solicitada_at' => now(),
                'estado' => 'solicitada',
                'documentos_seleccionados' => null,
            ]);
        }

        // Recalcular flag global tiene_revision_activa desde la tabla
        $tieneActivas = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada')
            ->exists();
        $postulante->tiene_revision_activa = $tieneActivas;
        $postulante->save();

        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");

        // Notificar al postulante (BD)
        $user->notify(new SolicitudRevisionNotification($nombreCompleto, $postulante->nro_doc, $veces));

        // Enviar FCM push al postulante
        $firebaseService = new FirebaseService();
        $tokensPostulante = $user->fcmTokens()->pluck('token')->toArray();
        if (!empty($tokensPostulante)) {
            $firebaseService->sendToTokens(
                $tokensPostulante,
                'Solicitud de revisión enviada',
                "Tu solicitud de revisión de documentos ha sido registrada (intento #{$veces})",
                [
                    'tipo' => 'solicitud_revision',
                    'postulante_dni' => $postulante->nro_doc,
                    'veces' => (string) $veces,
                ],
                route('postulante.documentos')
            );
        }

        $revisores = User::where('id_rol', 2)->get();

        // Notificacion en BD inmediata: la campana del revisor lee esta tabla.
        foreach ($revisores as $revisor) {
            $revisor->notify(new RevisionDocumentosRevisorNotification($nombreCompleto, $postulante->nro_doc, $veces));
        }

        // Enviar push notifications a revisores (síncrono para garantizar entrega)
        $clickUrl = route('revisor.postulante-perfil', $postulante->nro_doc);

        \Log::info('[FCM] Enviando push a revisores', [
            'revisores_count' => $revisores->count(),
            'firebase_configured' => $firebaseService->isConfigured(),
        ]);

        foreach ($revisores as $revisor) {
            $tokens = $revisor->fcmTokens()->pluck('token')->toArray();
            \Log::info('[FCM] Revisor ' . $revisor->id . ' tokens: ' . count($tokens));

            if (!empty($tokens)) {
                $result = $firebaseService->sendToTokens(
                    $tokens,
                    'Solicitud de revisión de documentos',
                    "{$nombreCompleto} solicita revisión de sus documentos",
                    [
                        'tipo' => 'revision_documentos',
                        'postulante_dni' => $postulante->nro_doc,
                        'postulante_nombre' => $nombreCompleto,
                        'veces' => (string) $veces,
                        'url' => $clickUrl,
                    ],
                    $clickUrl
                );
                \Log::info('[FCM] Resultado push revisor ' . $revisor->id, $result);
            }
        }

        // También enviar al topic de revisores
        $firebaseService->sendToTopic(
            'revisores',
            'Solicitud de revisión de documentos',
            "{$nombreCompleto} solicita revisión de sus documentos",
            [
                'tipo' => 'revision_documentos',
                'postulante_dni' => $postulante->nro_doc,
                'postulante_nombre' => $nombreCompleto,
                'veces' => (string) $veces,
                'url' => $clickUrl,
            ],
            $clickUrl
        );

        // Registrar paso de documentos en la tabla paso
        if ($idProceso) {
            \App\Models\Paso::create([
                'nombre' => 'REVISIÓN DE DOCUMENTOS SOLICITADA',
                'nro' => 6,
                'avance' => 100,
                'postulante' => $postulante->id,
                'proceso' => $idProceso,
            ]);
        }

        return response()->json([
            'success' => true,
            'mensaje' => $veces > 1
                ? 'Se ha enviado tu solicitud de revisión nuevamente (intento #' . $veces . ')'
                : 'Tu solicitud de revisión ha sido enviada correctamente',
            'datos' => [
                'revision_solicitada' => true,
                'revision_solicitada_at' => now()->format('d/m/Y H:i'),
                'veces_revision_solicitada' => $veces,
            ],
        ]);
    }

    public function estadoRevision(Request $request)
    {
        $user = Auth::user();
        $postulante = Postulante::where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => true, 'datos' => ['revision_solicitada' => false, 'revision_solicitada_at' => null, 'veces_revision_solicitada' => 0]]);
        }

        $idModalidad = $request->query('id_modalidad');

        // Buscar la solicitud activa (no completada) para la modalidad
        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($idModalidad) {
            $query->where('id_modalidad', $idModalidad);
        }

        $solicitud = $query->latest('id')->first();

        // Si no hay activa, buscar la última completada para mostrar histórico
        if (!$solicitud) {
            $queryCompletada = RevisionSolicitud::where('id_postulante', $postulante->id);
            if ($idModalidad) {
                $queryCompletada->where('id_modalidad', $idModalidad);
            }
            $solicitud = $queryCompletada->latest('id')->first();
        }

        $vecesQuery = RevisionSolicitud::where('id_postulante', $postulante->id);
        if ($idModalidad) {
            $vecesQuery->where('id_modalidad', $idModalidad);
        } elseif ($solicitud) {
            $vecesQuery->where('id_modalidad', $solicitud->id_modalidad);
        }
        $vecesTotal = $vecesQuery->count();

        // Calcular si puede insistir (cooldown de 24h por modalidad)
        $puedeInsistir = true;
        $proximaInsistencia = null;

        $queryActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');
        if ($idModalidad) {
            $queryActiva->where('id_modalidad', $idModalidad);
        }
        $solicitudActiva = $queryActiva->latest('id')->first();

        if ($solicitudActiva) {
            $horasTranscurridas = now()->diffInHours($solicitudActiva->solicitada_at);
            if ($horasTranscurridas < 24) {
                $puedeInsistir = false;
                $proximaInsistencia = $solicitudActiva->solicitada_at->copy()->addHours(24)->format('d/m/Y H:i');
            }
        }

        return response()->json([
            'success' => true,
            'datos' => [
                'revision_solicitada' => $solicitud && $solicitud->estado !== 'completada',
                'tiene_revision_pendiente' => $solicitudActiva !== null,
                'puede_solicitar' => $puedeInsistir,
                'revision_solicitada_at' => $solicitud?->solicitada_at ? $solicitud->solicitada_at->format('d/m/Y H:i') : null,
                'veces_revision_solicitada' => $vecesTotal,
                'revision_iniciada_at' => $solicitud?->iniciada_at ? $solicitud->iniciada_at->format('d/m/Y H:i') : null,
                'revision_finalizada_at' => $solicitud?->finalizada_at ? $solicitud->finalizada_at->format('d/m/Y H:i') : null,
                'datos_citacion' => $solicitud?->datos_citacion,
                'puede_insistir' => $puedeInsistir,
                'proxima_insistencia' => $proximaInsistencia,
            ],
        ]);
    }

    public function seguimientoData()
    {
        $user = Auth::user();
        $postulante = Postulante::where('nro_doc', $user->dni)->first();

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'Postulante no encontrado'], 404);
        }

        $idProceso = $user->id_proceso;

        // Pasos del postulante (historial completo)
        $pasos = \App\Models\Paso::where('postulante', $postulante->id)
            ->where('proceso', $idProceso)
            ->orderBy('id')
            ->get();

        // Mapear pasos a formato legible
        $pasosData = $pasos->map(function ($paso) {
            return [
                'id' => $paso->id,
                'nro' => $paso->nro,
                'nombre' => $paso->nombre,
                'fecha' => $paso->created_at ? $paso->created_at->format('d/m/Y H:i') : null,
            ];
        });

        // Estado de revisión — leer desde revision_solicitudes
        $solicitudActiva = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada')
            ->latest('id')
            ->first();

        $ultimaCompletada = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', 'completada')
            ->latest('id')
            ->first();

        $idModalidadActual = ($solicitudActiva ?? $ultimaCompletada)?->id_modalidad;

        $revision = [
            'solicitada' => $solicitudActiva !== null,
            'solicitada_at' => ($solicitudActiva?->solicitada_at ?: $ultimaCompletada?->solicitada_at)?->format('d/m/Y H:i'),
            'veces' => $idModalidadActual
                ? RevisionSolicitud::where('id_postulante', $postulante->id)->where('id_modalidad', $idModalidadActual)->count()
                : RevisionSolicitud::where('id_postulante', $postulante->id)->count(),
            'iniciada_at' => ($solicitudActiva?->iniciada_at ?: $ultimaCompletada?->iniciada_at)?->format('d/m/Y H:i'),
            'finalizada_at' => ($solicitudActiva?->finalizada_at ?: $ultimaCompletada?->finalizada_at)?->format('d/m/Y H:i'),
            'datos_citacion' => $solicitudActiva?->datos_citacion ?: $ultimaCompletada?->datos_citacion,
        ];

        // Calcular paso actual
        $avance = 0;
        if ($pasos->isNotEmpty()) {
            $avance = $pasos->max('nro');
        }

        // Documentos subidos
        $docsSubidos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->count();

        return response()->json([
            'success' => true,
            'datos' => [
                'pasos' => $pasosData,
                'revision' => $revision,
                'avance' => $avance,
                'documentos_subidos' => $docsSubidos,
            ],
        ]);
    }

    public function registrarFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string|in:web,android,ios',
        ]);

        $user = Auth::user();

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'device_type' => $request->device_type ?? 'web',
            ]
        );

        // Suscribir al topic de revisores si es revisor
        if ($user->isRevisor()) {
            $firebaseService = new FirebaseService();
            $firebaseService->subscribeToTopic('revisores', [$request->token]);
        }

        return response()->json(['success' => true]);
    }

    public function eliminarFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        $fcmToken = FcmToken::where('token', $request->token)
            ->where('user_id', $user->id)
            ->first();

        if ($fcmToken) {
            // Desuscribir del topic de revisores si era revisor
            if ($user->isRevisor()) {
                $firebaseService = new FirebaseService();
                $firebaseService->unsubscribeFromTopic('revisores', [$request->token]);
            }

            $fcmToken->delete();
        }

        return response()->json(['success' => true]);
    }

    public function previewDocumentoRevisor(int $id)
    {
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            abort(404);
        }

        try {
            return $this->storageService->previewFile($id);
        } catch (\RuntimeException $e) {
            abort(404);
        }
    }

    public function descargarDocumentoRevisor(int $id)
    {
        $documento = $this->storageService->getFile($id);

        if (!$documento) {
            return response()->json(['success' => false, 'mensaje' => 'Documento no encontrado'], 404);
        }

        try {
            return $this->storageService->downloadFile($id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }
}
