<?php

namespace App\Services\Revisor;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevisorDocumentoService
{
    public function __construct(
        private CitacionService $citacionService,
        private FirebaseService $firebaseService,
    ) {}

    public function iniciarRevision(string $dni, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        } else {
            $query->whereNull('finalizada_at');
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return ['error' => 'El postulante no ha solicitado revisión', 'status' => 400];
        }

        if ($solicitud->iniciada_at && !$solicitud->finalizada_at) {
            $revisor = User::find($solicitud->revisor_id);
            return [
                'mensaje'     => 'La revisión ya está en curso',
                'iniciada_at' => $solicitud->iniciada_at,
                'revisor'     => $revisor?->name,
            ];
        }

        $solicitud->iniciada_at = now();
        $solicitud->finalizada_at = null;
        $solicitud->apto = false;
        $solicitud->apto_at = null;
        $solicitud->revisor_id = Auth::id();
        $solicitud->estado = 'en_revision';
        $solicitud->save();

        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");
        $revisorNombre = Auth::user()->name ?? 'Revisor';
        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($user) {
            $user->notify(new RevisionIniciadaNotification($nombreCompleto, $postulante->nro_doc, $revisorNombre));

            $tokens = $user->fcmTokens()->pluck('token')->toArray();
            if (!empty($tokens)) {
                $this->firebaseService->sendToTokens(
                    $tokens,
                    'Revisión de documentos iniciada',
                    "{$revisorNombre} está revisando tus documentos",
                    [
                        'tipo'           => 'revision_iniciada',
                        'postulante_dni' => $postulante->nro_doc,
                        'revisor_nombre' => $revisorNombre,
                    ],
                    route('postulante.documentos')
                );
            }
        }

        return [
            'mensaje'     => 'Revisión iniciada. Se notificó al postulante.',
            'iniciada_at' => $solicitud->iniciada_at,
        ];
    }

    public function marcarApto(string $dni, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        } else {
            $query->whereNull('finalizada_at');
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

        if ($solicitud->finalizada_at) {
            return ['error' => 'La revisión ya fue completada', 'status' => 400];
        }

        $apto = !$solicitud->apto;
        $solicitud->apto = $apto;
        $solicitud->apto_at = $apto ? now() : null;
        $solicitud->save();

        return [
            'mensaje' => $apto ? 'Postulante marcado como apto' : 'Apto desmarcado',
            'apto'    => $apto,
            'apto_at' => $solicitud->apto_at,
        ];
    }

    public function finalizarRevision(string $dni, array $data, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        } else {
            $query->whereNull('finalizada_at');
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

        if ($solicitud->finalizada_at) {
            $solicitud = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', '!=', 'completada')
                ->whereNull('finalizada_at')
                ->latest('id')
                ->first();

            if (!$solicitud) {
                return ['error' => 'La revisión ya fue completada', 'status' => 400];
            }
        }

        if (!$solicitud->iniciada_at) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

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
        $todosValidos = count($pendientes) === 0;

        $nombreCompleto = trim("{$postulante->primer_apellido} {$postulante->segundo_apellido} {$postulante->nombres}");
        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($todosValidos) {
            $datosCitacion = [
                'fecha'         => $data['fecha'],
                'hora_inicio'    => $data['hora_inicio'],
                'hora_fin'       => $data['hora_fin'],
                'lugar'          => $data['lugar'],
                'instrucciones'  => $data['instrucciones'] ?? null,
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

            if ($user) {
                $user->notify(new RevisionCompletadaNotification(
                    $nombreCompleto, $postulante->nro_doc, $verificados, [], $datosCitacion
                ));

                $tokens = $user->fcmTokens()->pluck('token')->toArray();
                if (!empty($tokens)) {
                    $this->firebaseService->sendToTokens(
                        $tokens,
                        'Revisión de documentos completada',
                        "Sin novedades. Cita presencial: {$datosCitacion['fecha']} · " . count($verificados) . " aprobados",
                        [
                            'tipo'           => 'revision_completada',
                            'postulante_dni' => $postulante->nro_doc,
                            'fecha_cita'     => $datosCitacion['fecha'],
                        ],
                        route('postulante.documentos')
                    );
                }
            }

            $idProceso = $solicitud->id_proceso ?: Auth::user()->id_proceso;
            if ($idProceso) {
                Paso::create([
                    'nombre'    => 'REVISIÓN DE DOCUMENTOS COMPLETADA',
                    'nro'       => 7,
                    'avance'    => 7,
                    'postulante' => $postulante->id,
                    'proceso'   => $idProceso,
                ]);
            }

            return [
                'mensaje'               => 'Revisión finalizada. Todos los documentos están válidos. Se notificó al postulante para la citación presencial.',
                'resultado'             => 'completada',
                'finalizada_at'         => $solicitud->finalizada_at,
                'documentos_verificados' => $verificados,
                'documentos_pendientes'  => [],
                'datos_citacion'        => $datosCitacion,
            ];
        }

        $observaciones = [];
        foreach ($documentos as $doc) {
            if (!$doc->valido) {
                $observaciones[] = [
                    'nombre'      => $doc->nombre,
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

        $postulante->tiene_revision_activa = true;
        $postulante->save();

        if ($user) {
            $user->notify(new RevisionCompletadaNotification(
                $nombreCompleto, $postulante->nro_doc, $verificados, $pendientes, []
            ));

            $tokens = $user->fcmTokens()->pluck('token')->toArray();
            if (!empty($tokens)) {
                $this->firebaseService->sendToTokens(
                    $tokens,
                    'Documentos por corregir',
                    count($pendientes) . ' documento(s) observado(s). Debes corregirlos y solicitar nuevamente revisión.',
                    [
                        'tipo'           => 'revision_pendiente',
                        'postulante_dni' => $postulante->nro_doc,
                    ],
                    route('postulante.documentos')
                );
            }
        }

        return [
            'mensaje'               => 'Revisión finalizada con observaciones. El postulante debe corregir ' . count($pendientes) . ' documento(s).',
            'resultado'             => 'pendiente',
            'finalizada_at'         => $solicitud->finalizada_at,
            'documentos_verificados' => $verificados,
            'documentos_pendientes'  => $pendientes,
            'observaciones'          => $observaciones,
            'datos_citacion'        => null,
        ];
    }

    public function renotificarPostulante(string $dni, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', 'pendiente');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud) {
            return ['error' => 'No hay revisión pendiente para este postulante', 'status' => 400];
        }

        $pendientes = $solicitud->documentos_pendientes ?? [];
        $user = User::where('dni', $postulante->nro_doc)->first();

        if ($user) {
            $tokens = $user->fcmTokens()->pluck('token')->toArray();
            if (!empty($tokens)) {
                $this->firebaseService->sendToTokens(
                    $tokens,
                    'Recordatorio: Actualiza tus documentos',
                    'Tienes ' . count($pendientes) . ' documento(s) por corregir. Actualízalos y solicita nuevamente revisión.',
                    [
                        'tipo'           => 'revision_renotificar',
                        'postulante_dni' => $postulante->nro_doc,
                    ],
                    route('postulante.documentos')
                );
            }
        }

        Log::info('Re-notificación enviada al postulante', [
            'postulante' => $postulante->nro_doc,
            'revisor'    => Auth::id(),
            'solicitud'  => $solicitud->id,
            'pendientes' => count($pendientes),
        ]);

        return ['mensaje' => 'Se envió recordatorio al postulante para que actualice sus documentos.'];
    }

    public function revisionRapida(string $dni, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        } else {
            $query->whereNull('finalizada_at');
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

        if ($solicitud->finalizada_at) {
            return ['error' => 'La revisión ya fue completada', 'status' => 400];
        }

        $revisorId = Auth::id();
        $ahora = now();

        $tiposEnRequisitos = DB::table('requisito_tipo_documento')
            ->pluck('id_tipo_documento')
            ->unique();

        $count = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->whereIn('id_tipo_documento', $tiposEnRequisitos)
            ->update([
                'apto_revision' => true,
                'valido'        => true,
                'revisado_at'   => $ahora,
                'validado_at'   => $ahora,
                'id_revisor'    => $revisorId,
                'verificado'    => 1,
            ]);

        return [
            'mensaje' => "{$count} documento(s) marcado(s) como válidos",
            'count'   => $count,
        ];
    }

    public function cambiarEstadoDocumento(int $documentoId, string $accion, ?string $fechaCaducidad = null, ?string $observacion = null): array
    {
        $documento = Documento::find($documentoId);

        if (!$documento) {
            return ['error' => 'Documento no encontrado', 'status' => 404];
        }

        $postulante = Postulante::find($documento->id_postulante);

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $solicitud = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada')
            ->whereNull('finalizada_at')
            ->latest('id')
            ->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

        if ($solicitud->finalizada_at) {
            return ['error' => 'La revisión ya fue completada', 'status' => 400];
        }

        $revisorId = Auth::id();

        if ($accion === 'apto_revision') {
            if ($documento->apto_revision) {
                return ['error' => 'El documento ya está marcado como apto para revisión', 'status' => 400];
            }
            $documento->apto_revision = true;
            $documento->revisado_at = now();
            $documento->id_revisor = $revisorId;
            $documento->save();

            return [
                'mensaje' => 'Documento marcado como apto para revisión',
                'datos'   => [
                    'apto_revision' => true,
                    'valido'        => $documento->valido,
                    'revisado_at'   => $documento->revisado_at?->format('d/m/Y H:i'),
                ],
            ];
        }

        if ($accion === 'valido') {
            if (!$documento->apto_revision) {
                return ['error' => 'Debe marcar el documento como apto para revisión primero', 'status' => 400];
            }
            if ($documento->valido) {
                return ['error' => 'El documento ya está marcado como válido', 'status' => 400];
            }
            $documento->valido = true;
            $documento->validado_at = now();
            $documento->id_revisor = $revisorId;
            $documento->verificado = 1;
            if ($fechaCaducidad) {
                $documento->fecha_caducidad = $fechaCaducidad;
            }
            if ($observacion) {
                $documento->observacion_revisor = $observacion;
            }
            $documento->save();

            return [
                'mensaje' => 'Documento marcado como válido',
                'datos'   => [
                    'apto_revision'    => true,
                    'valido'           => true,
                    'fecha_caducidad'  => $documento->fecha_caducidad?->format('Y-m-d'),
                    'validado_at'      => $documento->validado_at?->format('d/m/Y H:i'),
                ],
            ];
        }

        if ($accion === 'desmarcar') {
            if ($documento->valido) {
                $documento->valido = false;
                $documento->validado_at = null;
                $documento->verificado = 0;
                $documento->save();

                return [
                    'mensaje' => 'Documento válido desmarcado',
                    'datos'   => [
                        'apto_revision' => $documento->apto_revision,
                        'valido'        => false,
                    ],
                ];
            } elseif ($documento->apto_revision) {
                $documento->apto_revision = false;
                $documento->revisado_at = null;
                $documento->save();

                return [
                    'mensaje' => 'Apto para revisión desmarcado',
                    'datos'   => [
                        'apto_revision' => false,
                        'valido'        => false,
                    ],
                ];
            }

            return ['error' => 'El documento no tiene estados para desmarcar', 'status' => 400];
        }

        return ['error' => 'Acción no válida', 'status' => 400];
    }

    public function observarDocumento(int $documentoId, ?string $observacion, ?int $solicitudId = null): array
    {
        $documento = Documento::find($documentoId);

        if (!$documento) {
            return ['error' => 'Documento no encontrado', 'status' => 404];
        }

        $postulante = Postulante::find($documento->id_postulante);

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $query = RevisionSolicitud::where('id_postulante', $postulante->id)
            ->where('estado', '!=', 'completada');

        if ($solicitudId) {
            $query->where('id', $solicitudId);
        } else {
            $query->whereNull('finalizada_at');
        }

        $solicitud = $query->latest('id')->first();

        if (!$solicitud || !$solicitud->iniciada_at) {
            return ['error' => 'Debe iniciar la revisión primero', 'status' => 400];
        }

        if ($solicitud->finalizada_at) {
            return ['error' => 'La revisión ya fue completada', 'status' => 400];
        }

        $documento->observacion_revisor = $observacion ?: null;
        $documento->id_revisor = Auth::id();
        $documento->save();

        return [
            'mensaje' => $observacion ? 'Observación guardada' : 'Observación eliminada',
            'datos'   => [
                'observacion_revisor' => $documento->observacion_revisor,
            ],
        ];
    }

    public function citacionSugerida(string $dni, int $idProceso): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

        $preinscripcion = Preinscripcion::where('id_postulante', $postulante->id)
            ->where('id_proceso', $idProceso)
            ->latest()
            ->first();

        $citacion = $this->citacionService->calcularCitacion(
            $idProceso,
            $dni,
            $preinscripcion?->id_programa,
            $preinscripcion?->id_modalidad
        );

        if (!$citacion) {
            return ['error' => 'No hay configuración de citación para este proceso', 'status' => 400];
        }

        return [
            'citacion' => [
                'fecha'          => $citacion->fecha?->format('Y-m-d'),
                'hora_inicio'    => $citacion->hora_inicio,
                'hora_fin'       => $citacion->hora_fin,
                'lugar'          => $citacion->lugar,
                'instrucciones'  => $citacion->instrucciones,
                'tipo_criterio'  => $citacion->tipo_criterio,
                'valor'          => $citacion->valor,
            ],
        ];
    }

    public function documentosPorRequisitos(string $dni, ?int $solicitudId = null): array
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return ['error' => 'Postulante no encontrado', 'status' => 404];
        }

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

        $requisitos = RequisitoDocumento::with([
            'modalidades:id,nombre,codigo',
            'programas:id,nombre,nombre_corto',
            'tiposDocumento:id,nombre,codigo'
        ])
            ->where('estado', true)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $documentosSubidos = Documento::where('id_postulante', $postulante->id)
            ->where('estado', 1)
            ->where('is_deleted', false)
            ->get()
            ->groupBy('id_tipo_documento');

        $requisitosFiltrados = $requisitos->filter(function ($req) use ($idModalidadPostulante) {
            if (!$idModalidadPostulante) return true;
            if ($req->modalidades->isEmpty()) return true;
            return $req->modalidades->contains('id', $idModalidadPostulante);
        });

        $requisitosData = $requisitosFiltrados->map(function ($req) use ($documentosSubidos, $idProgramaPostulante) {
            $tiposData = $req->tiposDocumento->map(function ($td) use ($documentosSubidos) {
                $subidos = $documentosSubidos->get($td->id, collect());
                $docsData = $subidos->map(function ($doc) {
                    $hoy = now()->startOfDay();
                    return [
                        'id'                  => $doc->id,
                        'nombre'              => $doc->nombre,
                        'url'                 => $doc->url,
                        'seleccionado'        => $doc->seleccionado,
                        'apto_revision'       => $doc->apto_revision,
                        'valido'              => $doc->valido,
                        'verificado'          => $doc->valido,
                        'fecha_caducidad'     => $doc->fecha_caducidad?->format('Y-m-d'),
                        'observacion_revisor' => $doc->observacion_revisor,
                        'revisado_at'         => $doc->revisado_at?->format('d/m/Y H:i'),
                        'validado_at'         => $doc->validado_at?->format('d/m/Y H:i'),
                        'vigente'             => !$doc->fecha_caducidad || $doc->fecha_caducidad >= $hoy,
                        'por_vencer'          => $doc->fecha_caducidad
                            && $doc->fecha_caducidad < $hoy->copy()->addDays(30)
                            && $doc->fecha_caducidad >= $hoy,
                        'caducado'            => $doc->fecha_caducidad && $doc->fecha_caducidad < $hoy,
                    ];
                })->values()->toArray();

                return [
                    'id'         => $td->id,
                    'nombre'     => $td->nombre,
                    'codigo'     => $td->codigo,
                    'subido'     => $subidos->isNotEmpty(),
                    'verificado' => $subidos->contains(fn($d) => $d->valido),
                    'documentos' => $docsData,
                ];
            });

            $tipoSubido = $tiposData->firstWhere('subido', true);
            $reqCumplido = $tipoSubido !== null;
            $reqVerificado = $tiposData->contains(fn($td) => $td['subido'] && $td['verificado']);

            $programasRequisito = $req->programas;
            $tieneProgramas = $programasRequisito->count() > 0;

            if ($req->obligatorio) {
                $obligatorioParaPostulante = true;
                $noAplica = false;
            } elseif ($tieneProgramas && $idProgramaPostulante) {
                $idsProgramasReq = $programasRequisito->pluck('id')->toArray();
                $obligatorioParaPostulante = in_array($idProgramaPostulante, $idsProgramasReq);
                $noAplica = !$obligatorioParaPostulante;
            } else {
                $obligatorioParaPostulante = false;
                $noAplica = false;
            }

            return [
                'id'                          => $req->id,
                'nombre'                      => $req->nombre,
                'obligatorio'                 => $req->obligatorio,
                'orden'                       => $req->orden,
                'obligatorio_para_postulante' => $obligatorioParaPostulante,
                'no_aplica'                   => $noAplica,
                'modalidades'                => $req->modalidades->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre, 'codigo' => $m->codigo])->toArray(),
                'programas'                  => $req->programas->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombre, 'nombre_corto' => $p->nombre_corto])->toArray(),
                'tipos_documento'            => $tiposData->values()->toArray(),
                'cumplido'                    => $reqCumplido,
                'verificado'                  => $reqVerificado,
            ];
        });

        $obligatoriosPendientes = $requisitosData->filter(fn($r) => $r['obligatorio_para_postulante'] && !$r['no_aplica'] && !$r['cumplido'])->count();

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

        $revisionFinalizadaAt = $solicitudActiva?->finalizada_at;
        $revisionApto = $solicitudActiva?->apto ?? false;
        $revisionAptoAt = $solicitudActiva?->apto_at;
        $revisionRevisorId = $solicitudActiva?->revisor_id;

        if (!$solicitudActiva) {
            $ultimaCompletada = RevisionSolicitud::where('id_postulante', $postulante->id)
                ->where('estado', 'completada')
                ->latest('id')
                ->first();
            if ($ultimaCompletada) {
                $revisionFinalizadaAt = $ultimaCompletada->finalizada_at;
            }
        }

        return [
            'requisitos' => $requisitosData->values()->toArray(),
            'modalidad_postulante' => $modalidadInfo,
            'programa_postulante'   => $programaInfo,
            'postulante' => [
                'nombres'                => $postulante->nombres,
                'primer_apellido'        => $postulante->primer_apellido,
                'segundo_apellido'       => $postulante->segundo_apellido,
                'revision_solicitada'     => $revisionSolicitada,
                'revision_iniciada_at'    => $solicitudActiva?->iniciada_at,
                'revision_finalizada_at'  => $revisionFinalizadaAt,
                'revision_apto'           => $revisionApto,
                'revision_apto_at'        => $revisionAptoAt,
                'revision_revisor_id'     => $revisionRevisorId,
                'revision_estado'         => $solicitudActiva?->estado,
            ],
            'apto_revision'            => $revisionApto,
            'apto_at'                   => $revisionAptoAt,
            'obligatorios_pendientes'  => $obligatoriosPendientes,
            'total_requisitos'         => $requisitosData->count(),
            'requisitos_cumplidos'     => $requisitosData->filter(fn($r) => $r['cumplido'])->count(),
        ];
    }
}
