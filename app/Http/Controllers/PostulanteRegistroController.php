<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\Apoderado;
use App\Models\Colegio;
use App\Models\Paso;
use App\Models\AvancePostulante;
use App\Models\Proceso;
use App\Models\PreInscripcion;
use App\Models\Inscripcion;
use App\Models\Ingresante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PostulanteRegistroController extends Controller
{
    // ─── HELPER: Obtener postulante del usuario autenticado ────────────

    private function getPostulante(Request $request)
    {
        $user = $request->user();

        // La relación User↔Postulante es por DNI: User.dni = Postulante.nro_doc
        if ($user->dni) {
            return Postulante::where('nro_doc', $user->dni)->first();
        }

        return null;
    }

    // ─── HELPER: Obtener/crear avance_postulante ─────────────────────

    private function getAvance(Request $request, $dni)
    {
        $idProceso = $request->user()->id_proceso;
        if (!$idProceso || !$dni) return null;

        return AvancePostulante::where('dni_postulante', $dni)
            ->where('id_proceso', $idProceso)
            ->first();
    }

    private function actualizarAvance($dni, $idProceso, $paso)
    {
        if (!$idProceso || !$dni) return;

        AvancePostulante::updateOrCreate(
            [
                'dni_postulante' => $dni,
                'id_proceso' => $idProceso,
            ],
            [
                'avance' => $paso,
                'id_usuario' => request()->user()->id,
            ]
        );
    }

    // ─── CONSULTAS JSON (axios) ──────────────────────────────────────

    public function consultarReniec($dni)
    {
        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            return response()->json(['success' => false, 'mensaje' => 'DNI inválido'], 422);
        }

        $datosReniec = $this->consultarReniecInterno($dni);

        if (!$datosReniec) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudieron obtener los datos de RENIEC',
            ]);
        }

        $mayorEdad = true;
        if (isset($datosReniec['fecha_nacimiento'])) {
            $mayorEdad = Carbon::parse($datosReniec['fecha_nacimiento'])->age >= 18;
        }

        if (!$mayorEdad) {
            return response()->json([
                'success'    => true,
                'mayor_edad' => false,
                'mensaje'    => 'Es menor de edad, puede ingresar sus datos manualmente',
            ]);
        }

        return response()->json([
            'success'    => true,
            'mayor_edad' => true,
            'datos'      => $datosReniec,
        ]);
    }

    public function getDepartamentos()
    {
        $res = DB::table('departamento')
            ->select('codigo as key', 'nombre as value')
            ->orderBy('nombre', 'ASC')
            ->get();
        return response()->json(['success' => true, 'datos' => $res]);
    }

    public function getProvincias($departamento)
    {
        $res = DB::table('provincia')
            ->select('codigo as key', 'nombre as value')
            ->where('id_dep', function ($query) use ($departamento) {
                $query->select('id')
                    ->from('departamento')
                    ->where('codigo', $departamento)
                    ->limit(1);
            })
            ->orderBy('nombre', 'ASC')
            ->get();
        return response()->json(['success' => true, 'datos' => $res]);
    }

    public function getDistritos($departamento, $provincia)
    {
        $res = DB::table('distritos')
            ->select('distritos.codigo as key', 'distritos.nombre as value')
            ->join('ubigeo', 'distritos.id', '=', 'ubigeo.id_distrito')
            ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
            ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
            ->where('departamento.codigo', $departamento)
            ->where('provincia.codigo', $provincia)
            ->orderBy('distritos.nombre', 'ASC')
            ->get();
        return response()->json(['success' => true, 'datos' => $res]);
    }

    public function buscarUbigeo(Request $request)
    {
        $term = $request->input('term', '');
        $res = DB::table('ubigeo')
            ->select('ubigeo.ubigeo as key', DB::raw('CONCAT(ubigeo.ubigeo, " - ", departamento.nombre, " / ", provincia.nombre, " / ", distritos.nombre) as value'))
            ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
            ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
            ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
            ->where(function ($query) use ($term) {
                $query->orWhere('ubigeo.ubigeo', 'LIKE', '%' . $term . '%')
                    ->orWhere('departamento.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere('provincia.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere('distritos.nombre', 'LIKE', '%' . $term . '%')
                    ->orWhere(DB::raw('CONCAT(departamento.nombre, " / ", provincia.nombre, " / ", distritos.nombre)'), 'LIKE', '%' . $term . '%')
                    ->orWhere(DB::raw('CONCAT(ubigeo.ubigeo, " - ", departamento.nombre, " / ", provincia.nombre, " / ", distritos.nombre)'), 'LIKE', '%' . $term . '%');
            })
            ->orderBy('departamento.nombre')
            ->orderBy('provincia.nombre')
            ->orderBy('distritos.nombre')
            ->paginate(50);

        return response()->json(['success' => true, 'datos' => $res]);
    }

    public function getColegios($ubigeo)
    {
        $res = Colegio::select('id as value', 'nombre as label')
            ->where('ubigeo', $ubigeo)
            ->orderBy('nombre', 'ASC')
            ->get();
        return response()->json(['success' => true, 'datos' => $res]);
    }

    public function validarCorreo(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = $request->user();
        $existe = Postulante::where('email', $request->email)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();
        $existeUser = false;
        if (!$existe) {
            $existeUser = \App\Models\User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();
        }
        return response()->json(['success' => true, 'existe' => $existe || $existeUser]);
    }

    public function validarCelular(Request $request)
    {
        $request->validate(['celular' => 'required|string|min:9']);
        $existe = Postulante::where('celular', $request->celular)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();
        $existeUser = false;
        if (!$existe) {
            $existeUser = \App\Models\User::where('cellular', $request->celular)
                ->where('id', '!=', $request->user()->id)
                ->exists();
        }
        return response()->json(['success' => true, 'existe' => $existe || $existeUser]);
    }

    // ─── PÁGINAS (GET) — con datos existentes ──────────────────────

    /**
     * "Mis Datos" — Vista inteligente:
     *   - Si no tiene postulante → redirige a paso 1
     *   - Si completó los 4 pasos y confirmó → vista solo lectura
     *   - Si está en proceso → redirige al paso que le falta
     */
    public function misDatos(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if (!$postulante) {
            return redirect()->route('postulante.paso1');
        }

        $avance = $this->calcularAvance($request, $postulante);

        if ($avance < 4) {
            return redirect()->route('postulante.paso' . ($avance + 1));
        }

        $apoderados = Apoderado::where('id_postulante', $postulante->id)->get();
        $colegio = $postulante->id_colegio ? Colegio::find($postulante->id_colegio) : null;

        $ubigeoNacimiento = $postulante->ubigeo_nacimiento ? $this->getUbigeoLabel($postulante->ubigeo_nacimiento) : null;
        $ubigeoResidencia = $postulante->ubigeo_residencia ? $this->getUbigeoLabel($postulante->ubigeo_residencia) : null;

        $confirmado = $avance >= 5;

        return Inertia::render('Postulante/MisDatos', [
            'postulante' => [
                'id' => $postulante->id,
                'nro_doc' => $postulante->nro_doc,
                'tipo_doc' => $postulante->tipo_doc,
                'primer_apellido' => $postulante->primer_apellido,
                'segundo_apellido' => $postulante->segundo_apellido,
                'apellido_casada' => $postulante->apellido_casada,
                'nombres' => $postulante->nombres,
                'fec_nacimiento' => $postulante->fec_nacimiento,
                'sexo' => $postulante->sexo,
                'estado_civil' => $postulante->estado_civil,
                'ubigeo_nacimiento' => $postulante->ubigeo_nacimiento,
                'ubigeo_nacimiento_label' => $ubigeoNacimiento,
                'email' => $postulante->email,
                'celular' => $postulante->celular,
                'direccion' => $postulante->direccion,
                'ubigeo_residencia' => $postulante->ubigeo_residencia,
                'ubigeo_residencia_label' => $ubigeoResidencia,
                'id_colegio' => $postulante->id_colegio,
                'anio_egreso' => $postulante->anio_egreso,
            ],
            'colegio' => $colegio ? ['id' => $colegio->id, 'nombre' => $colegio->nombre] : null,
            'apoderados' => $apoderados,
            'confirmado' => $confirmado,
            'avance' => $avance,
        ]);
    }

    public function dashboard(Request $request)
    {
        $postulante = $this->getPostulante($request);
        $avance = 0;
        $revisionSolicitada = false;

        if ($postulante) {
            $avanceRecord = $this->getAvance($request, $postulante->nro_doc);
            $avance = $avanceRecord ? $avanceRecord->avance : 0;

            // Check paso records for granular progress
            $pasosCompletados = Paso::where('postulante', $postulante->id)
                ->where('proceso', $request->user()->id_proceso)
                ->pluck('nro')
                ->toArray();

            // Override avance based on actual paso records if they exist
            if (!empty($pasosCompletados)) {
                $avance = max($pasosCompletados);
            }

            // Check if document revision was requested
            $revisionSolicitada = $postulante->tiene_revision_activa ?? false;
        }

        // Map avance to progress data
        // avance 0 = no steps, 1 = datos personales, 2 = contacto, 3 = colegio, 4 = apoderado, 5 = confirmado
        // + revision_solicitada = step 2 (docs) done
        $completedSteps = 0;
        if ($avance >= 5) $completedSteps = 1; // step 1 done
        if ($revisionSolicitada) $completedSteps = 2; // steps 1+2 done
        $progressPercent = min($completedSteps * 20, 100);
        $currentStepLabel = match(true) {
            $completedSteps >= 2 => 'Documentos enviados',
            $avance >= 5 => 'Paso 2 de 5',
            $avance >= 1 => 'Paso ' . ($avance + 1) . ' de 5',
            default => 'Paso 1 de 5',
        };

        // Build timeline steps with status
        $timelineSteps = [
            [
                'nro' => 1,
                'titulo' => 'Completa tus datos',
                'descripcion' => 'Registra datos personales, contacto, colegio, apoderado y confirma.',
                'status' => $avance >= 5 ? 'done' : ($avance === 0 ? 'active' : 'pending'),
                'route' => route('postulante.mis-datos'),
                'subSteps' => [
                    ['label' => 'Datos personales', 'done' => $avance >= 1],
                    ['label' => 'Datos de contacto', 'done' => $avance >= 2],
                    ['label' => 'Datos del colegio', 'done' => $avance >= 3],
                    ['label' => 'Apoderado', 'done' => $avance >= 4],
                    ['label' => 'Verificación', 'done' => $avance >= 5],
                ],
            ],
            [
                'nro' => 2,
                'titulo' => 'Sube tus documentos',
                'descripcion' => 'Sube DNI, certificados y documentos requeridos.',
                'status' => $revisionSolicitada ? 'done' : 'pending',
                'route' => route('postulante.documentos'),
            ],
            [
                'nro' => 3,
                'titulo' => 'Verificación de documentos',
                'descripcion' => 'Un administrador revisará y aprobará tus documentos.',
                'status' => 'pending',
            ],
            [
                'nro' => 4,
                'titulo' => 'Verificación biométrica',
                'descripcion' => 'Toma de foto y huella digital para validar tu identidad.',
                'status' => 'pending',
            ],
            [
                'nro' => 5,
                'titulo' => 'Inscríbete a un proceso',
                'descripcion' => 'Selecciona proceso de admisión y realiza tu pago.',
                'status' => 'pending',
            ],
        ];

        // Determine the active timeline step (first non-done)
        $activeStepFound = false;
        foreach ($timelineSteps as &$step) {
            if ($step['status'] === 'pending' && !$activeStepFound) {
                $step['status'] = 'active';
                $activeStepFound = true;
            }
        }
        unset($step);

        return Inertia::render('Postulante/Dashboard', [
            'postulante' => $postulante ? [
                'id' => $postulante->id,
                'nro_doc' => $postulante->nro_doc,
                'nombres' => $postulante->nombres,
                'primer_apellido' => $postulante->primer_apellido,
            ] : null,
            'avance' => $avance,
            'progressPercent' => $progressPercent,
            'currentStepLabel' => $currentStepLabel,
            'timelineSteps' => $timelineSteps,
            'tienePostulante' => $postulante !== null,
        ]);
    }

    public function paso1(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $avance = $postulante ? $this->getAvance($request, $postulante->nro_doc) : null;

        return Inertia::render('Postulante/Paso1DatosPersonales', [
            'postulante' => $postulante ? [
                'id' => $postulante->id,
                'nro_doc' => $postulante->nro_doc,
                'primer_apellido' => $postulante->primer_apellido,
                'segundo_apellido' => $postulante->segundo_apellido,
                'nombres' => $postulante->nombres,
                'fec_nacimiento' => $postulante->fec_nacimiento,
                'sexo' => $postulante->sexo,
                'estado_civil' => $postulante->estado_civil,
                'ubigeo_nacimiento' => $postulante->ubigeo_nacimiento,
            ] : null,
            'avance' => $avance ? $avance->avance : 0,
        ]);
    }

    public function paso2(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $avance = $postulante ? $this->getAvance($request, $postulante->nro_doc) : null;

        return Inertia::render('Postulante/Paso2DatosContacto', [
            'postulante' => $postulante ? [
                'id' => $postulante->id,
                'email' => $postulante->email,
                'celular' => $postulante->celular,
                'direccion' => $postulante->direccion,
                'ubigeo_residencia' => $postulante->ubigeo_residencia,
                'nro_doc' => $postulante->nro_doc,
            ] : null,
            'avance' => $avance ? $avance->avance : 0,
        ]);
    }

    public function paso3(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $avance = $postulante ? $this->getAvance($request, $postulante->nro_doc) : null;

        $colegioNombre = null;
        $colegioUbigeo = null;
        if ($postulante?->id_colegio) {
            $colegio = Colegio::find($postulante->id_colegio);
            $colegioNombre = $colegio?->nombre;
            $colegioUbigeo = $colegio?->ubigeo;
        }

        return Inertia::render('Postulante/Paso3DatosColegio', [
            'postulante' => $postulante ? [
                'id' => $postulante->id,
                'id_colegio' => $postulante->id_colegio,
                'colegio_nombre' => $colegioNombre,
                'colegio_ubigeo' => $colegioUbigeo,
                'anio_egreso' => $postulante->anio_egreso,
            ] : null,
            'avance' => $avance ? $avance->avance : 0,
        ]);
    }

    public function paso4(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $avance = $postulante ? $this->getAvance($request, $postulante->nro_doc) : null;

        $apoderados = [];
        if ($postulante) {
            $apoderados = Apoderado::where('id_postulante', $postulante->id)
                ->get()
                ->map(fn($a) => [
                    'tipo_apoderado' => $a->tipo_apoderado,
                    'nro_documento' => $a->nro_documento,
                    'nombres' => $a->nombres,
                    'paterno' => $a->paterno,
                    'materno' => $a->materno,
                ])->toArray();
        }

        return Inertia::render('Postulante/Paso4Apoderado', [
            'postulante' => $postulante ? [
                'id' => $postulante->id,
            ] : null,
            'apoderados_existentes' => $apoderados,
            'avance' => $avance ? $avance->avance : 0,
        ]);
    }

    // ─── GUARDADO (POST) ────────────────────────────────────────────

    public function guardarPaso1(Request $request)
    {
        $postulante = $this->getPostulante($request);
        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $validated = $request->validate([
            'nro_doc'           => 'required|string|size:8',
            'tipo_doc'          => 'nullable|integer',
            'primer_apellido'   => 'required|string|max:100',
            'segundo_apellido'  => 'nullable|string|max:100',
            'nombres'           => 'required|string|max:200',
            'fec_nacimiento'    => 'required|date',
            'ubigeo_nacimiento' => 'required|string|max:6',
            'sexo'              => 'required|string|max:1',
            'estado_civil'      => 'nullable|string|max:20',
            'mayor_edad'        => 'required|boolean',
        ]);

        $user = $request->user();
        $postulanteExistente = Postulante::where('nro_doc', $validated['nro_doc'])->first();

        if ($postulanteExistente && $postulanteExistente->nro_doc != $user->dni && $postulanteExistente->id_usuario != $user->id) {
            return back()->withErrors([
                'nro_doc' => 'Su DNI ya se encuentra registrado en otra cuenta. Por favor, acerque presencialmente a la oficina de admisión.',
            ]);
        }

        $safeUpper = fn($v) => $v ? mb_strtoupper($v, 'UTF-8') : $v;
        $estadoCivil = $this->mapearEstadoCivil($validated['estado_civil'] ?? null);

        if ($postulanteExistente && ($postulanteExistente->nro_doc == $user->dni || $postulanteExistente->id_usuario == $user->id)) {
            $postulanteExistente->update([
                'tipo_doc'          => $validated['tipo_doc'] ?? 1,
                'primer_apellido'   => $safeUpper($validated['primer_apellido']),
                'segundo_apellido'  => $safeUpper($validated['segundo_apellido']),
                'nombres'           => $safeUpper($validated['nombres']),
                'fec_nacimiento'    => $validated['fec_nacimiento'],
                'ubigeo_nacimiento' => $validated['ubigeo_nacimiento'],
                'sexo'              => $validated['sexo'],
                'estado_civil'      => $estadoCivil,
            ]);
            $postulante = $postulanteExistente;
        } else {
            $postulante = Postulante::create([
                'tipo_doc'          => $validated['tipo_doc'] ?? 1,
                'nro_doc'           => $validated['nro_doc'],
                'primer_apellido'   => $safeUpper($validated['primer_apellido']),
                'segundo_apellido'  => $safeUpper($validated['segundo_apellido']),
                'nombres'           => $safeUpper($validated['nombres']),
                'fec_nacimiento'    => $validated['fec_nacimiento'],
                'ubigeo_nacimiento' => $validated['ubigeo_nacimiento'],
                'ubigeo_residencia' => $validated['ubigeo_nacimiento'],
                'sexo'              => $validated['sexo'],
                'estado_civil'      => $estadoCivil,
                'id_usuario'        => $user->id,
            ]);
        }

        // Vincular el DNI al usuario para que getPostulante() lo encuentre
        if (!$user->dni) {
            $user->dni = $postulante->nro_doc;
            $user->save();
        }

        // Actualizar avance_postulante
        $idProceso = $user->id_proceso;
        if ($idProceso) {
            $this->actualizarAvance($postulante->nro_doc, $idProceso, 1);
            $this->registrarPaso('DATOS PERSONALES REGISTRADOS', 1, 20, $postulante->id, $idProceso);
        }

        return redirect()->route('postulante.paso2');
    }

    public function guardarPaso2(Request $request)
    {
        $postulante = $this->getPostulante($request);
        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $validated = $request->validate([
            'id_postulante'     => 'required|integer|exists:postulante,id',
            'email'             => 'required|email',
            'celular'           => 'required|string|min:9',
            'direccion'         => 'required|string|max:255',
            'ubigeo_residencia' => 'required|string|max:6',
        ]);

        $postulante = Postulante::find($validated['id_postulante']);

        if ($postulante->nro_doc != $request->user()->dni) {
            return back()->withErrors(['auth' => 'No autorizado para modificar este postulante']);
        }

        $correoEnUso = Postulante::where('email', $validated['email'])
            ->where('id', '!=', $postulante->id)->exists();
        if ($correoEnUso) {
            return back()->withErrors(['email' => 'El correo electrónico ya está registrado por otro postulante']);
        }

        $celularEnUso = Postulante::where('celular', $validated['celular'])
            ->where('id', '!=', $postulante->id)->exists();
        if ($celularEnUso) {
            return back()->withErrors(['celular' => 'El número de celular ya está registrado por otro postulante']);
        }

        $postulante->update([
            'email'             => $validated['email'],
            'celular'           => $validated['celular'],
            'direccion'         => mb_strtoupper($validated['direccion'], 'UTF-8'),
            'ubigeo_residencia' => $validated['ubigeo_residencia'],
        ]);

        $idProceso = $request->user()->id_proceso;
        if ($idProceso) {
            $this->actualizarAvance($postulante->nro_doc, $idProceso, 2);
            $this->registrarPaso('DATOS DE CONTACTO REGISTRADOS', 2, 40, $postulante->id, $idProceso);
        }

        return redirect()->route('postulante.paso3');
    }

    public function guardarPaso3(Request $request)
    {
        $postulante = $this->getPostulante($request);
        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $validated = $request->validate([
            'id_postulante' => 'required|integer|exists:postulante,id',
            'id_colegio'    => 'required|integer|exists:colegios,id',
            'anio_egreso'   => 'nullable|string|max:4',
        ]);

        $postulante = Postulante::find($validated['id_postulante']);

        if ($postulante->nro_doc != $request->user()->dni) {
            return back()->withErrors(['auth' => 'No autorizado para modificar este postulante']);
        }

        $postulante->update([
            'id_colegio'  => $validated['id_colegio'],
            'anio_egreso' => $validated['anio_egreso'],
        ]);

        $idProceso = $request->user()->id_proceso;
        if ($idProceso) {
            $this->actualizarAvance($postulante->nro_doc, $idProceso, 3);
            $this->registrarPaso('DATOS DEL COLEGIO REGISTRADOS', 3, 60, $postulante->id, $idProceso);
        }

        return redirect()->route('postulante.paso4');
    }

    public function guardarPaso4(Request $request)
    {
        $postulante = $this->getPostulante($request);
        if ($postulante && $this->calcularAvance($request, $postulante) >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $validated = $request->validate([
            'id_postulante'              => 'required|integer|exists:postulante,id',
            'tiene_apoderado'            => 'required|boolean',
            'apoderados'                 => 'nullable|array',
            'apoderados.*.nro_documento' => 'required_with:apoderados|string|size:8',
            'apoderados.*.paterno'       => 'required_with:apoderados|string|max:100',
            'apoderados.*.materno'       => 'nullable|string|max:100',
            'apoderados.*.nombres'       => 'required_with:apoderados|string|max:200',
            'apoderados.*.tipo_apoderado' => 'required_with:apoderados|integer|in:1,2,3',
        ]);

        $postulante = Postulante::find($validated['id_postulante']);

        if ($postulante->nro_doc != $request->user()->dni) {
            return back()->withErrors(['auth' => 'No autorizado para modificar este postulante']);
        }

        $safeUpper = fn($v) => $v ? mb_strtoupper($v, 'UTF-8') : $v;

        if ($validated['tiene_apoderado'] && !empty($validated['apoderados'])) {
            foreach ($validated['apoderados'] as $apoderadoData) {
                Apoderado::updateOrCreate(
                    [
                        'id_postulante'   => $postulante->id,
                        'tipo_apoderado'  => $apoderadoData['tipo_apoderado'],
                    ],
                    [
                        'tipo_doc'      => 1,
                        'nro_documento' => $apoderadoData['nro_documento'],
                        'paterno'       => $safeUpper($apoderadoData['paterno']),
                        'materno'       => $safeUpper($apoderadoData['materno'] ?? null),
                        'nombres'       => $safeUpper($apoderadoData['nombres']),
                        'id_usuario'    => $request->user()->id,
                    ]
                );
            }
        }

        $idProceso = $request->user()->id_proceso;
        if ($idProceso) {
            $this->actualizarAvance($postulante->nro_doc, $idProceso, 4);
            $this->registrarPaso('DATOS DEL APODERADO REGISTRADOS', 4, 80, $postulante->id, $idProceso);
        }

        return redirect()->route('postulante.paso5');
    }

    public function paso5(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if (!$postulante) {
            return redirect()->route('postulante.paso1');
        }

        $avance = $this->calcularAvance($request, $postulante);

        if ($avance < 4) {
            return redirect()->route('postulante.paso' . ($avance + 1));
        }

        if ($avance >= 5) {
            return redirect()->route('postulante.mis-datos');
        }

        $apoderados = Apoderado::where('id_postulante', $postulante->id)->get();
        $colegio = $postulante->id_colegio ? Colegio::find($postulante->id_colegio) : null;

        $ubigeoNacimiento = $postulante->ubigeo_nacimiento ? $this->getUbigeoLabel($postulante->ubigeo_nacimiento) : null;
        $ubigeoResidencia = $postulante->ubigeo_residencia ? $this->getUbigeoLabel($postulante->ubigeo_residencia) : null;

        return Inertia::render('Postulante/Paso5Verificacion', [
            'postulante' => [
                'id' => $postulante->id,
                'nro_doc' => $postulante->nro_doc,
                'tipo_doc' => $postulante->tipo_doc,
                'primer_apellido' => $postulante->primer_apellido,
                'segundo_apellido' => $postulante->segundo_apellido,
                'apellido_casada' => $postulante->apellido_casada,
                'nombres' => $postulante->nombres,
                'fec_nacimiento' => $postulante->fec_nacimiento,
                'sexo' => $postulante->sexo,
                'estado_civil' => $postulante->estado_civil,
                'ubigeo_nacimiento_label' => $ubigeoNacimiento,
                'email' => $postulante->email,
                'celular' => $postulante->celular,
                'direccion' => $postulante->direccion,
                'ubigeo_residencia_label' => $ubigeoResidencia,
                'anio_egreso' => $postulante->anio_egreso,
            ],
            'colegio' => $colegio ? ['id' => $colegio->id, 'nombre' => $colegio->nombre] : null,
            'apoderados' => $apoderados,
        ]);
    }

    public function confirmarDatos(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if (!$postulante) {
            return redirect()->route('postulante.paso1');
        }

        $avance = $this->calcularAvance($request, $postulante);

        if ($avance < 4) {
            return redirect()->route('postulante.paso' . ($avance + 1));
        }

        $idProceso = $request->user()->id_proceso;
        if ($idProceso) {
            $this->actualizarAvance($postulante->nro_doc, $idProceso, 5);
            $this->registrarPaso('DATOS CONFIRMADOS POR POSTULANTE', 5, 100, $postulante->id, $idProceso);
        }

        return redirect()->route('postulante.confirmacion', ['dni' => $postulante->nro_doc]);
    }

    public function confirmacion($dni)
    {
        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante || $postulante->nro_doc != request()->user()->dni) {
            return redirect()->route('postulante.dashboard');
        }

        return Inertia::render('Postulante/Confirmacion', [
            'dni'      => $postulante->nro_doc,
            'nombre'   => $postulante->primer_apellido . ' ' . $postulante->nombres,
            'programa' => $postulante->id_colegio ? Colegio::find($postulante->id_colegio)?->nombre : '—',
        ]);
    }

    // ─── API: Obtener datos del postulante para dashboard ──────────

    public function getMisDatos(Request $request)
    {
        $postulante = $this->getPostulante($request);

        if (!$postulante) {
            return response()->json(['success' => false, 'mensaje' => 'No tiene datos registrados']);
        }

        $idProceso = $request->user()->id_proceso;
        $avance = $this->getAvance($request, $postulante->nro_doc);

        $apoderados = Apoderado::where('id_postulante', $postulante->id)->get();

        $colegio = $postulante->id_colegio ? Colegio::find($postulante->id_colegio) : null;

        return response()->json([
            'success' => true,
            'datos' => [
                'postulante' => $postulante,
                'colegio' => $colegio ? ['id' => $colegio->id, 'nombre' => $colegio->nombre] : null,
                'apoderados' => $apoderados,
                'avance' => $avance ? $avance->avance : 0,
            ],
        ]);
    }

    // ─── MÉTODOS PRIVADOS ──────────────────────────────────────────

    private function calcularAvance(Request $request, Postulante $postulante): int
    {
        $avanceRecord = $this->getAvance($request, $postulante->nro_doc);
        $avance = $avanceRecord ? $avanceRecord->avance : 0;

        $pasosCompletados = Paso::where('postulante', $postulante->id)
            ->where('proceso', $request->user()->id_proceso)
            ->pluck('nro')
            ->toArray();

        if (!empty($pasosCompletados)) {
            $avance = max($pasosCompletados);
        }

        return (int) $avance;
    }

    private function getUbigeoLabel(string $ubigeoCode): ?string
    {
        $row = DB::table('ubigeo')
            ->join('departamento', 'ubigeo.id_departamento', '=', 'departamento.id')
            ->join('provincia', 'ubigeo.id_provincia', '=', 'provincia.id')
            ->join('distritos', 'ubigeo.id_distrito', '=', 'distritos.id')
            ->where('ubigeo.ubigeo', $ubigeoCode)
            ->selectRaw("CONCAT(departamento.nombre, ' / ', provincia.nombre, ' / ', distritos.nombre) as label")
            ->first();

        return $row?->label;
    }

    private function consultarReniecInterno($dni)
    {
        $consulta = DB::table('consultas_reniec')
            ->orderBy('cant', 'asc')
            ->first();

        if (!$consulta) return null;

        if (!Carbon::now()->isSameDay($consulta->updated_at)) {
            DB::table('consultas_reniec')
                ->where('id', $consulta->id)
                ->update(['cant' => 0, 'updated_at' => now()]);
            $consulta->cant = 0;
        }

        if ($consulta->cant >= $consulta->maximo) return null;

        DB::table('consultas_reniec')
            ->where('id', $consulta->id)
            ->increment('cant');

        try {
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$consulta->token}",
            ])->get("https://service7.unap.edu.pe/api/v1/reniec/consulta/{$dni}");

            if (!$response->ok() || empty($response['data'])) return null;

            $data = $response['data'];

            return [
                'dni'               => $dni,
                'nombres'           => mb_strtoupper($data['prenombres'] ?? '', 'UTF-8'),
                'ap_paterno'       => mb_strtoupper($data['apPrimer'] ?? '', 'UTF-8'),
                'ap_materno'       => mb_strtoupper($data['apSegundo'] ?? '', 'UTF-8'),
                'direccion'         => $data['direccion'] ?? null,
                'fecha_nacimiento'  => $data['fechaNacimiento'] ?? null,
                'sexo'              => $data['sexo'] ?? null,
                'estado_civil'      => $this->mapearEstadoCivil($data['estadoCivil'] ?? null),
                'ubigeo_nacimiento' => $data['ubigeoNacimiento'] ?? null,
                'foto_base64'      => $data['foto'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapearEstadoCivil($valor): ?int
    {
        if (!$valor) return null;

        if (is_numeric($valor)) return (int) $valor;

        $valor = mb_strtoupper(trim($valor), 'UTF-8');

        $mapa = [
            'SOLTERO'   => 1,
            'SOLTERA'   => 1,
            'CASADO'    => 2,
            'CASADA'    => 2,
            'VIUDO'     => 3,
            'VIUDA'     => 3,
            'DIVORCIADO'=> 4,
            'DIVORCIADA'=> 4,
            'CONVIVIENTE'=> 5,
            'CONVIVIENTE LEGAL' => 5,
        ];

        foreach ($mapa as $texto => $codigo) {
            if (str_contains($valor, $texto)) {
                return $codigo;
            }
        }

        return null;
    }

    private function registrarPaso($nombre, $nro, $avance, $postulante, $proceso)
    {
        Paso::create([
            'nombre'     => $nombre,
            'nro'        => $nro,
            'avance'     => $avance,
            'postulante' => $postulante,
            'proceso'    => $proceso,
        ]);
    }

    /**
     * API: Obtener procesos disponibles para el postulante
     * - Procesos donde ya participó (inscripción, pre-inscripción, resultados)
     * - Procesos activos vigentes
     */
    public function getMisProcesos(Request $request)
    {
        $user = $request->user();
        $dni = $user->dni;

        // IDs de procesos donde ya participó
        $procesosParticipadosIds = collect();

        if ($dni) {
            $postulante = Postulante::where('nro_doc', $dni)->first();

            if ($postulante) {
                $idsPre = PreInscripcion::where('id_postulante', $postulante->id)->pluck('id_proceso');
                $idsIns = Inscripcion::where('id_postulante', $postulante->id)->pluck('id_proceso');
                $procesosParticipadosIds = $idsPre->merge($idsIns)->unique();
            }

            // Procesos donde tiene resultados
            $idsRes = Ingresante::where('dni_postulante', $dni)->pluck('id_proceso');
            $procesosParticipadosIds = $procesosParticipadosIds->merge($idsRes)->unique();
        }

        // Cargar procesos participados (solo nivel 1)
        $procesosParticipados = Proceso::whereIn('id', $procesosParticipadosIds)
            ->where('nivel', 1)
            ->select('id', 'nombre', 'anio', 'estado')
            ->orderByDesc('anio')
            ->orderByDesc('id')
            ->get()
            ->toArray();

        // Procesos activos donde aún NO participó (solo nivel 1)
        $procesosActivos = Proceso::where('estado', 1)
            ->where('nivel', 1)
            ->whereNotIn('id', $procesosParticipadosIds)
            ->select('id', 'nombre', 'anio', 'estado')
            ->orderByDesc('anio')
            ->orderByDesc('id')
            ->get()
            ->toArray();

        return response()->json([
            'procesos_participados' => $procesosParticipados,
            'procesos_activos' => $procesosActivos,
            'id_proceso' => $user->id_proceso,
        ]);
    }

    /**
     * API: Guardar selección de proceso
     */
    public function seleccionarProceso(Request $request)
    {
        $request->validate([
            'id_proceso' => 'required|integer|exists:procesos,id',
        ]);

        $user = $request->user();
        $user->id_proceso = $request->id_proceso;
        $user->save();

        $proceso = Proceso::find($request->id_proceso);

        return response()->json([
            'estado' => true,
            'mensaje' => 'Proceso seleccionado: ' . $proceso->nombre,
            'proceso' => [
                'id' => $proceso->id,
                'nombre' => $proceso->nombre,
                'anio' => $proceso->anio,
            ],
        ]);
    }
}
