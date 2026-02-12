<?php
namespace App\Http\Controllers\Segundas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Preinscripcion;
use App\Models\Postulante;
use App\Models\Inscripcion;
use App\Models\DocumentoSegunda;
use App\Models\ControlBiometrico;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use DB;

class PostulanteSegundaController extends Controller
{
  public function getPostulantes(Request $request)
  {
      $query_where = [];

      if (auth()->user()->programas != null) {
          $array = json_decode(auth()->user()->programas, true);
          if (!empty($array)) {
              $query_where[] = ['pre_inscripcion.id_programa', $array];
          }
      }

      $res = Preinscripcion::select(
          'postulante.id',
          'postulante.tipo_doc',
          'postulante.nro_doc',
          'postulante.primer_apellido',
          'postulante.segundo_apellido',
          'postulante.apellido_casada',
          'postulante.nombres',
          'postulante.sexo',
          'postulante.fec_nacimiento',
          'postulante.ubigeo_nacimiento',
          'postulante.ubigeo_residencia',
          'postulante.celular',
          'postulante.email',
          'postulante.estado_civil',
          'postulante.direccion',
          'postulante.anio_egreso',
          'postulante.correo_institucional',
          'postulante.cod_orcid',
          'postulante.observaciones',
          'postulante.id_colegio',
      )
      ->join('postulante', 'postulante.id', 'pre_inscripcion.id_postulante')
      ->when(!empty($query_where), function ($query) use ($query_where) {
          // Aplicamos la condición de filtro
          foreach ($query_where as $condition) {
              $query->whereIn($condition[0], $condition[1]);
          }
      })
      ->where('pre_inscripcion.id_proceso', auth()->user()->id_proceso)
      ->where(function ($query) use ($request) {
          return $query
              ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.email', 'LIKE', '%' . $request->term . '%')
              ->orWhere('postulante.celular', 'LIKE', '%' . $request->term . '%')
              ->orWhere(DB::raw("CONCAT(postulante.nombres, ' ', postulante.primer_apellido, ' ', postulante.segundo_apellido)"), 'LIKE', '%' . $request->term . '%')
              ->orWhere(DB::raw("CONCAT(postulante.primer_apellido, ' ', postulante.segundo_apellido, ' ', postulante.nombres)"), 'LIKE', '%' . $request->term . '%');
      })
      ->paginate(20);

      // Formateamos la respuesta
      return response()->json([
          'estado' => true,
          'datos' => $res
      ], 200);
  }


  public function showPostulante($dni) {

    $postulanteInfo = Postulante::select(
        'postulante.id AS id_postulante',
        'postulante.nombres',
        'postulante.primer_apellido',
        'postulante.segundo_apellido',
        'postulante.email',
        'postulante.celular',
        'departamento.nombre AS departamento',
        'provincia.nombre AS provincia',
        'distritos.nombre AS distrito'
    )
    ->leftJoin('ubigeo', 'ubigeo.ubigeo', '=', 'postulante.ubigeo_residencia')
    ->leftJoin('departamento', 'departamento.id', '=', 'ubigeo.id_departamento')
    ->leftJoin('provincia', 'provincia.id', '=', 'ubigeo.id_provincia')
    ->leftJoin('distritos', 'distritos.id', '=', 'ubigeo.id_distrito')
    ->where('postulante.nro_doc', '=', $dni)
    ->first();

    $procesos = Inscripcion::select('procesos.id AS id_proceso','procesos.nombre AS proceso','inscripciones.codigo')
    ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
    ->where('inscripciones.id_postulante', '=', $postulanteInfo->id_postulante)
    ->orderBy('procesos.id', 'desc')
    ->get();

    $foto = DocumentoSegunda::where('id_proceso', auth()->user()->id_proceso)
    ->where('dni', $dni)
    ->where('id_tipo', 8)
    ->value('url');

    $titulo = DocumentoSegunda::where('id_proceso', auth()->user()->id_proceso)
    ->where('dni', $dni)
    ->where('id_tipo', 7)
    ->value('url');

    $countPreInscripcion = Preinscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
    $countInscripcion = Inscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
    $countControlBiometrico = ControlBiometrico::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();

    return Inertia::render('Segundas/Admin/Postulantes/Perfil',
      [
        'info' => $postulanteInfo,
        'preinscripciones'=>  $countPreInscripcion,
        'inscripciones' => $countInscripcion,
        'control_biometrico' => $countControlBiometrico,
        'foto' => "../../".$foto,
        'titulo' => "../../".$titulo,
        'pro' => $procesos
      ]);

  }


  public function getDatosPostulante($dni) {

        $postulanteInfo = Postulante::select(
            'postulante.id AS id_postulante',
            'postulante.nombres',
            'postulante.primer_apellido',
            'postulante.segundo_apellido',
            'postulante.email',
            'postulante.celular',
            'departamento.nombre AS departamento',
            'provincia.nombre AS provincia',
            'distritos.nombre AS distrito'
        )
        ->leftJoin('ubigeo', 'ubigeo.ubigeo', '=', 'postulante.ubigeo_residencia')
        ->leftJoin('departamento', 'departamento.id', '=', 'ubigeo.id_departamento')
        ->leftJoin('provincia', 'provincia.id', '=', 'ubigeo.id_provincia')
        ->leftJoin('distritos', 'distritos.id', '=', 'ubigeo.id_distrito')
        ->where('postulante.nro_doc', '=', $dni)
        ->first();


        $procesos = Inscripcion::select('procesos.id AS id_proceso','procesos.nombre AS proceso','inscripciones.codigo')
        ->join('procesos', 'procesos.id', '=', 'inscripciones.id_proceso')
        ->where('inscripciones.id_postulante', '=', $postulanteInfo->id_postulante)
        ->orderBy('procesos.id', 'desc')
        ->get();

        $foto = DocumentoSegunda::where('id_proceso', auth()->user()->id_proceso)
        ->where('dni', $dni)
        ->where('id_tipo', 8)
        ->value('url');

        $titulo = DocumentoSegunda::where('id_proceso', auth()->user()->id_proceso)
        ->where('dni', $dni)
        ->where('id_tipo', 7)
        ->value('url');

        $countPreInscripcion = Preinscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
        $countInscripcion = Inscripcion::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();
        $countControlBiometrico = ControlBiometrico::where('id_postulante', '=', $postulanteInfo->id_postulante)->count();

        return response()->json([
            'info' => $postulanteInfo,
            'preinscripciones'=>  $countPreInscripcion,
            'inscripciones' => $countInscripcion,
            'control_biometrico' => $countControlBiometrico,
            'foto' => "../../".$foto,
            'titulo' => "../../".$titulo,
            'pro' => $procesos,
            'estado' => true
        ], 200);

  }

  public function savePostulanteAdmin(Request $request ) {

            $modalidad = null;
            if (!$request->id) {
            $postulante = Postulante::create([
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'apellido_casada' => $request->apellido_casada,
                'nombres' => $request->nombres,
                'sexo' => $request->sexo,
                'fec_nacimiento' => $request->fec_nacimiento,
                'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
                'ubigeo_residencia' => $request->ubigeo_residencia,
                'celular' => $request->celular,
                'email' => $request->correo,
                'estado_civil' => $request->estado_civil,
                'direccion' => $request->direccion,
                'anio_egreso' => $request->egreso,
                'nro_doc' => $request->nro_doc,
                'tipo_doc'=> $request->tipo_doc,
                'observaciones' => $request->observaciones,
                'id_colegio' => $request->colegio,
            ]);
            $this->response['tipo'] = 'success';
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Proceso '.$postulante->nombre.' creado con exito';
            $this->response['estado'] = true;
            $this->response['datos'] = $postulante;
        } else {
            $temp = Postulante::find($request->id);
            $postulante = Postulante::find($request->id);
            $postulante->tipo_doc = $request->tipo_doc;
            $postulante->primer_apellido = $request->primer_apellido;
            $postulante->segundo_apellido = $request->segundo_apellido;
            $postulante->apellido_casada = $request->apellido_casada;
            $postulante->nombres = $request->nombres;
            $postulante->sexo = $request->sexo;
            $postulante->fec_nacimiento = $request->fec_nacimiento;
            $postulante->ubigeo_nacimiento = $request->ubigeo_nacimiento;
            $postulante->ubigeo_residencia = $request->ubigeo_residencia;
            $postulante->celular = $request->celular;
            $postulante->email = $request->correo;
            $postulante->estado_civil = $request->estado_civil;
            $postulante->direccion = $request->direccion;
            $postulante->anio_egreso = $request->egreso;
            $postulante->nro_doc = $request->nro_doc;
            $postulante->observaciones = $request->observaciones;
            $postulante->id_colegio = $request->colegio;
            $postulante->id_usuario = auth()->id();
            $postulante->save();

            if( $temp == $postulante ) {
                $this->response['estado'] = false;
            }else
            {
                $this->response['tipo'] = 'info';
                $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
                $this->response['mensaje'] = 'Datos del '.$postulante->nombres.' actualizados';
                $this->response['estado'] = true;
                $this->response['datos'] = $postulante;
            }

            }

        return response()->json($this->response, 200);

  }


  public function saveDataAdicional(Request $request)
  {
    $postulante = Postulante::find($request->id_postulante );
    $postulante->discapacidad = $request->discapacidad;
    $postulante->tipo_discapacidad = $request->tipo_discapacidad;
    $postulante->save();

    $request->validate([
        'id_postulante' => 'required|integer',
        'id_proceso' => 'required|integer',
    ]);

    $payload = [
        'id_postulante' => $request->id_postulante,
        'id_proceso' => $request->id_proceso,
        'id_pertenencia_cultural' => $request->id_pertenencia_cultural,
        'id_pueblo_indigena' => $request->id_pueblo_indigena,
        'id_condicion_lengua' => $request->id_condicion_lengua,
        'id_lengua_indigena' => $request->id_lengua_indigena,
    ];

    try {
        $response = Http::post('http://localhost:8080/api/v1/identidad-cultural', $payload);

        if ($response->successful()) {
            return response()->json([
                'estado' => true,
                'mensaje' => 'Datos enviados al servicio externo con éxito',
                'data' => $response->json()
            ]);
        }

        return response()->json([
            'estado' => false,
            'mensaje' => 'Error en el servicio externo',
            'data' => $response->json()
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'estado' => false,
            'mensaje' => 'No se pudo conectar con el servicio externo: ' . $e->getMessage()
        ], 500);
    }

  }











}
