<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Postulante;
use App\Models\Apoderado;
use App\Models\Colegio;
use App\Models\Paso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    // ─── PASO 1: DATOS PERSONALES ───────────────────────────────────

    /**
     * Validar si un DNI ya está registrado en otra cuenta.
     * Retorna: bloqueado (bool), datos del postulante si existe.
     */
    public function validarDni($dni)
    {
        $user = request()->user();

        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json([
                'success'   => true,
                'existe'    => false,
                'bloqueado' => false,
            ]);
        }

        // Si el postulante pertenece al usuario actual, puede continuar
        if ($postulante->id_usuario == $user->id) {
            return response()->json([
                'success'   => true,
                'existe'    => true,
                'bloqueado' => false,
                'datos'     => $postulante,
            ]);
        }

        // DNI registrado en otra cuenta
        return response()->json([
            'success'   => false,
            'existe'    => true,
            'bloqueado' => true,
            'mensaje'   => 'Su DNI ya se encuentra registrado en otra cuenta. Por favor, acerque presencialmente a la oficina de admisión.',
        ], 403);
    }

    /**
     * Consultar RENIEC si es mayor de edad.
     * Retorna datos de RENIEC solo si el DNI corresponde a un mayor de edad.
     */
    public function consultarReniec($dni)
    {
        if (strlen($dni) !== 8 || !is_numeric($dni)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'DNI inválido',
            ], 422);
        }

        // Determinar si es mayor de edad basado en el DNI
        // Los primeros 2 dígitos del DNI no permiten calcular la edad directamente,
        // pero podemos consultar RENIEC y calcular la edad desde la fecha de nacimiento
        $datosReniec = $this->consultarReniecInterno($dni);

        if (!$datosReniec) {
            return response()->json([
                'success'     => false,
                'mensaje'     => 'No se pudieron obtener los datos de RENIEC',
                'mayor_edad'  => null,
            ]);
        }

        // Calcular edad desde los datos de RENIEC (si viene fecha de nacimiento)
        $mayorEdad = true; // Por defecto se asume mayor de edad si RENIEC responde
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

    /**
     * Registrar datos personales (Paso 1).
     */
    public function registroDatosPersonales(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nro_doc'           => ['required', 'string', $request->tipo_doc == 1 ? 'size:8' : 'min:8|max:12'],
            'tipo_doc'          => 'nullable|integer',
            'primer_apellido'   => 'required|string|max:100',
            'segundo_apellido'  => 'nullable|string|max:100',
            'nombres'           => 'required|string|max:200',
            'fec_nacimiento'    => 'required|date',
            'ubigeo_nacimiento' => 'required|string|max:6',
            'sexo'              => 'required|string|max:1',
            'estado_civil'      => 'nullable|string|max:2',
            'mayor_edad'        => 'required|boolean',
            'id_proceso'        => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = request()->user();

        // Verificar que el DNI no esté en otra cuenta
        $postulanteExistente = Postulante::where('nro_doc', $request->nro_doc)->first();

        if ($postulanteExistente && $postulanteExistente->id_usuario != $user->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Su DNI ya se encuentra registrado en otra cuenta. Por favor, acerque presencialmente a la oficina de admisión.',
            ], 403);
        }

        $safeUpper = fn($v) => $v ? mb_strtoupper($v, 'UTF-8') : $v;

        if ($postulanteExistente && $postulanteExistente->id_usuario == $user->id) {
            // Actualizar postulante existente del mismo usuario
            $postulanteExistente->update([
                'tipo_doc'          => $request->tipo_doc ?? 1,
                'primer_apellido'   => $safeUpper($request->primer_apellido),
                'segundo_apellido'  => $safeUpper($request->segundo_apellido),
                'nombres'           => $safeUpper($request->nombres),
                'fec_nacimiento'    => $request->fec_nacimiento,
                'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
                'sexo'              => $request->sexo,
                'estado_civil'      => $request->estado_civil,
                'id_usuario'        => $user->id,
            ]);

            $postulante = $postulanteExistente;
        } else {
            // Crear nuevo postulante
            $postulante = Postulante::create([
                'tipo_doc'          => $request->tipo_doc ?? 1,
                'nro_doc'           => $request->nro_doc,
                'nro_doc_opcional'  => strlen($request->nro_doc) > 8 ? $request->nro_doc : null,
                'primer_apellido'   => $safeUpper($request->primer_apellido),
                'segundo_apellido'  => $safeUpper($request->segundo_apellido),
                'nombres'           => $safeUpper($request->nombres),
                'fec_nacimiento'    => $request->fec_nacimiento,
                'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
                'ubigeo_residencia' => $request->ubigeo_nacimiento,
                'sexo'              => $request->sexo,
                'estado_civil'      => $request->estado_civil,
                'id_usuario'        => $user->id,
            ]);
        }

        // Registrar avance del paso
        if ($request->id_proceso) {
            $this->registrarPaso('DATOS PERSONALES REGISTRADOS', 1, 20, $postulante->id, $request->id_proceso);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Datos personales registrados correctamente',
            'datos'   => $postulante,
        ]);
    }

    // ─── PASO 2: DATOS DE CONTACTO ───────────────────────────────────

    /**
     * Verificar si un correo electrónico ya existe.
     */
    public function validarCorreo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = request()->user();
        $existe = Postulante::where('email', $request->email)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();

        // También verificar en la tabla users
        $existeUser = false;
        if (!$existe) {
            $existeUser = \App\Models\User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'existe'  => $existe || $existeUser,
        ]);
    }

    /**
     * Verificar si un teléfono ya existe.
     */
    public function validarCelular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'celular' => 'required|string|min:9',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $existe = Postulante::where('celular', $request->celular)
            ->where('nro_doc', '!=', $request->dni)
            ->exists();

        // También verificar en la tabla users
        $existeUser = false;
        if (!$existe) {
            $existeUser = \App\Models\User::where('cellular', $request->celular)
                ->where('id', '!=', request()->user()->id)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'existe'  => $existe || $existeUser,
        ]);
    }

    /**
     * Registrar datos de contacto (Paso 2).
     */
    public function registroDatosContacto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_postulante'     => 'required|integer|exists:postulante,id',
            'email'             => 'required|email',
            'celular'           => 'required|string|min:9',
            'direccion'         => 'required|string|max:255',
            'ubigeo_residencia' => 'required|string|max:6',
            'id_proceso'        => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Validar correo único
        $correoEnUso = Postulante::where('email', $request->email)
            ->where('id', '!=', $request->id_postulante)
            ->exists();
        if ($correoEnUso) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El correo electrónico ya está registrado por otro postulante',
            ], 409);
        }

        // Validar celular único
        $celularEnUso = Postulante::where('celular', $request->celular)
            ->where('id', '!=', $request->id_postulante)
            ->exists();
        if ($celularEnUso) {
            return response()->json([
                'success' => false,
                'mensaje' => 'El número de celular ya está registrado por otro postulante',
            ], 409);
        }

        $postulante = Postulante::find($request->id_postulante);

        // Verificar que el postulante pertenece al usuario autenticado
        if ($postulante->id_usuario != request()->user()->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No autorizado para modificar este postulante',
            ], 403);
        }

        $postulante->update([
            'email'             => $request->email,
            'celular'           => $request->celular,
            'direccion'         => mb_strtoupper($request->direccion, 'UTF-8'),
            'ubigeo_residencia' => $request->ubigeo_residencia,
        ]);

        if ($request->id_proceso) {
            $this->registrarPaso('DATOS DE CONTACTO REGISTRADOS', 2, 40, $postulante->id, $request->id_proceso);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Datos de contacto registrados correctamente',
            'datos'   => $postulante,
        ]);
    }

    // ─── PASO 3: DATOS DEL COLEGIO ───────────────────────────────────

    /**
     * Obtener departamentos para ubigeo.
     */
    public function getDepartamentos()
    {
        $res = DB::table('departamento')
            ->select('codigo as key', 'nombre as value')
            ->orderBy('nombre', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'datos'   => $res,
        ]);
    }

    /**
     * Obtener provincias por departamento.
     */
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

        return response()->json([
            'success' => true,
            'datos'   => $res,
        ]);
    }

    /**
     * Obtener distritos por departamento y provincia (ubigeo completo).
     */
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

        return response()->json([
            'success' => true,
            'datos'   => $res,
        ]);
    }

    /**
     * Obtener colegios por ubigeo de distrito.
     */
    public function getColegiosPorUbigeo($ubigeo)
    {
        $res = Colegio::select('id as value', 'nombre as label')
            ->where('ubigeo', $ubigeo)
            ->orderBy('nombre', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'datos'   => $res,
        ]);
    }

    /**
     * Registrar datos del colegio (Paso 3).
     */
    public function registroDatosColegio(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_postulante' => 'required|integer|exists:postulante,id',
            'id_colegio'    => 'required|integer|exists:colegios,id',
            'anio_egreso'   => 'nullable|string|max:4',
            'id_proceso'    => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $postulante = Postulante::find($request->id_postulante);

        if ($postulante->id_usuario != request()->user()->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No autorizado para modificar este postulante',
            ], 403);
        }

        $postulante->update([
            'id_colegio'  => $request->id_colegio,
            'anio_egreso' => $request->anio_egreso,
        ]);

        if ($request->id_proceso) {
            $this->registrarPaso('DATOS DEL COLEGIO REGISTRADOS', 3, 60, $postulante->id, $request->id_proceso);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Datos del colegio registrados correctamente',
            'datos'   => $postulante,
        ]);
    }

    // ─── PASO 4: DATOS DEL APODERADO ─────────────────────────────────

    /**
     * Consultar datos del apoderado por DNI (RENIEC).
     */
    public function consultarApoderadoReniec($dni)
    {
        $datosReniec = $this->consultarReniecInterno($dni);

        if (!$datosReniec) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudieron obtener los datos de RENIEC',
            ]);
        }

        return response()->json([
            'success' => true,
            'datos'   => $datosReniec,
        ]);
    }

    /**
     * Registrar datos del apoderado (Paso 4 - opcional).
     */
    public function registroDatosApoderado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_postulante'  => 'required|integer|exists:postulante,id',
            'apoderados'     => 'nullable|array',
            'apoderados.*.nro_documento' => 'required_with:apoderados|string|min:8|max:12',
            'apoderados.*.paterno'        => 'required_with:apoderados|string|max:100',
            'apoderados.*.materno'        => 'nullable|string|max:100',
            'apoderados.*.nombres'        => 'required_with:apoderados|string|max:200',
            'apoderados.*.tipo_apoderado' => 'required_with:apoderados|integer|in:1,2,3',
            'id_proceso'     => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $postulante = Postulante::find($request->id_postulante);

        if ($postulante->id_usuario != request()->user()->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No autorizado para modificar este postulante',
            ], 403);
        }

        $safeUpper = fn($v) => $v ? mb_strtoupper($v, 'UTF-8') : $v;
        $apoderadosCreados = [];

        if ($request->apoderados && count($request->apoderados) > 0) {
            foreach ($request->apoderados as $apoderadoData) {
                $apoderado = Apoderado::updateOrCreate(
                    [
                        'id_postulante'   => $request->id_postulante,
                        'tipo_apoderado'  => $apoderadoData['tipo_apoderado'],
                    ],
                    [
                        'tipo_doc'      => 1,
                        'nro_documento' => $apoderadoData['nro_documento'],
                        'paterno'       => $safeUpper($apoderadoData['paterno']),
                        'materno'       => $safeUpper($apoderadoData['materno'] ?? null),
                        'nombres'       => $safeUpper($apoderadoData['nombres']),
                        'id_usuario'    => request()->user()->id,
                    ]
                );
                $apoderadosCreados[] = $apoderado;
            }
        }

        if ($request->id_proceso) {
            $this->registrarPaso('DATOS DEL APODERADO REGISTRADOS', 4, 80, $postulante->id, $request->id_proceso);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Datos del apoderado registrados correctamente',
            'datos'   => $apoderadosCreados,
        ]);
    }

    /**
     * Obtener apoderados registrados de un postulante.
     */
    public function getApoderados($idPostulante)
    {
        $postulante = Postulante::find($idPostulante);

        if (!$postulante || $postulante->id_usuario != request()->user()->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Postulante no encontrado o no autorizado',
            ], 403);
        }

        $apoderados = Apoderado::select('id', 'tipo_doc', 'nro_documento', 'paterno', 'materno', 'nombres', 'tipo_apoderado')
            ->where('id_postulante', $idPostulante)
            ->get();

        return response()->json([
            'success' => true,
            'datos'   => $apoderados,
        ]);
    }

    // ─── CONSULTAR DATOS REGISTRADOS ─────────────────────────────────

    /**
     * Obtener todos los datos del postulante registrado
     * para verificar que se guardaron correctamente.
     */
    public function consultarDatos($dni)
    {
        $user = request()->user();

        $postulante = Postulante::where('nro_doc', $dni)->first();

        if (!$postulante) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Postulante no encontrado',
            ], 404);
        }

        if ($postulante->id_usuario != $user->id) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No autorizado para ver estos datos',
            ], 403);
        }

        // Paso 1: Datos personales
        $datosPersonales = [
            'id'                => $postulante->id,
            'tipo_doc'          => $postulante->tipo_doc,
            'nro_doc'           => $postulante->nro_doc,
            'primer_apellido'   => $postulante->primer_apellido,
            'segundo_apellido'  => $postulante->segundo_apellido,
            'nombres'           => $postulante->nombres,
            'fec_nacimiento'    => $postulante->fec_nacimiento,
            'sexo'              => $postulante->sexo,
            'estado_civil'      => $postulante->estado_civil,
            'ubigeo_nacimiento' => $postulante->ubigeo_nacimiento,
        ];

        // Nacimiento con ubigeo legible
        $nacimiento = DB::table('postulante as p')
            ->select(DB::raw("CONCAT(dep.nombre, '/', prov.nombre, '/', dist.nombre) AS nacimiento_texto"))
            ->leftJoin('ubigeo as un', 'p.ubigeo_nacimiento', '=', 'un.ubigeo')
            ->leftJoin('departamento as dep', 'un.id_departamento', '=', 'dep.id')
            ->leftJoin('provincia as prov', 'un.id_provincia', '=', 'prov.id')
            ->leftJoin('distritos as dist', 'un.id_distrito', '=', 'dist.id')
            ->where('p.id', $postulante->id)
            ->value('nacimiento_texto');

        $datosPersonales['nacimiento_texto'] = $nacimiento;

        // Paso 2: Datos de contacto
        $datosContacto = [
            'email'             => $postulante->email,
            'celular'           => $postulante->celular,
            'direccion'         => $postulante->direccion,
            'ubigeo_residencia' => $postulante->ubigeo_residencia,
        ];

        $residencia = DB::table('postulante as p')
            ->select(DB::raw("CONCAT(dep.nombre, '/', prov.nombre, '/', dist.nombre) AS residencia_texto"))
            ->leftJoin('ubigeo as ur', 'p.ubigeo_residencia', '=', 'ur.ubigeo')
            ->leftJoin('departamento as dep', 'ur.id_departamento', '=', 'dep.id')
            ->leftJoin('provincia as prov', 'ur.id_provincia', '=', 'prov.id')
            ->leftJoin('distritos as dist', 'ur.id_distrito', '=', 'dist.id')
            ->where('p.id', $postulante->id)
            ->value('residencia_texto');

        $datosContacto['residencia_texto'] = $residencia;

        // Paso 3: Datos del colegio
        $datosColegio = null;
        if ($postulante->id_colegio) {
            $colegio = Colegio::find($postulante->id_colegio);
            if ($colegio) {
                $datosColegio = [
                    'id_colegio'   => $colegio->id,
                    'nombre'       => $colegio->nombre,
                    'cod_modular'  => $colegio->cod_modular,
                    'gestion'      => $colegio->gestion,
                    'ubigeo'       => $colegio->ubigeo,
                    'direccion'    => $colegio->direccion,
                ];
            }
        }
        $anioEgreso = $postulante->anio_egreso;

        // Paso 4: Apoderados
        $apoderados = Apoderado::select('id', 'tipo_doc', 'nro_documento', 'paterno', 'materno', 'nombres', 'tipo_apoderado')
            ->where('id_postulante', $postulante->id)
            ->get()
            ->map(function ($apo) {
                $tipos = [1 => 'Padre', 2 => 'Madre', 3 => 'Apoderado'];
                $apo->tipo_apoderado_texto = $tipos[$apo->tipo_apoderado] ?? 'Desconocido';
                return $apo;
            });

        // Avances registrados
        $avances = Paso::select('nombre', 'nro', 'avance', 'proceso')
            ->where('postulante', $postulante->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'datos'   => [
                'paso1_datos_personales' => $datosPersonales,
                'paso2_datos_contacto'   => $datosContacto,
                'paso3_datos_colegio'    => [
                    'colegio'      => $datosColegio,
                    'anio_egreso'  => $anioEgreso,
                ],
                'paso4_apoderados'       => $apoderados,
                'avances'                => $avances,
            ],
        ]);
    }

    // ─── MÉTODOS PRIVADOS ────────────────────────────────────────────

    /**
     * Consulta interna a RENIEC reutilizando la lógica existente.
     */
    private function consultarReniecInterno($dni)
    {
        $consulta = DB::table('consultas_reniec')
            ->orderBy('cant', 'asc')
            ->first();

        if (!$consulta) {
            return null;
        }

        // Reset diario
        if (!Carbon::now()->isSameDay($consulta->updated_at)) {
            DB::table('consultas_reniec')
                ->where('id', $consulta->id)
                ->update(['cant' => 0, 'updated_at' => now()]);
            $consulta->cant = 0;
        }

        if ($consulta->cant >= $consulta->maximo) {
            return null;
        }

        DB::table('consultas_reniec')
            ->where('id', $consulta->id)
            ->increment('cant');

        try {
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$consulta->token}",
            ])->get("https://service7.unap.edu.pe/api/v1/reniec/consulta/{$dni}");

            if (!$response->ok() || empty($response['data'])) {
                return null;
            }

            $data = $response['data'];

            return [
                'dni'              => $dni,
                'nombres'          => mb_strtoupper($data['prenombres'] ?? '', 'UTF-8'),
                'ap_paterno'      => mb_strtoupper($data['apPrimer'] ?? '', 'UTF-8'),
                'ap_materno'      => mb_strtoupper($data['apSegundo'] ?? '', 'UTF-8'),
                'direccion'        => $data['direccion'] ?? null,
                'fecha_nacimiento' => $data['fechaNacimiento'] ?? null,
                'sexo'             => $data['sexo'] ?? null,
                'estado_civil'     => $data['estadoCivil'] ?? null,
                'ubigeo_nacimiento' => $data['ubigeoNacimiento'] ?? null,
                'foto_base64'     => $data['foto'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Registrar avance del paso en el proceso.
     */
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
}
