<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Proceso;
use App\Models\CarrerasPrevias;
use App\Models\Ingresante;
use App\Models\Postulante;
use App\Models\Apoderado;
use App\Models\Paso;
use App\Models\Cambio;
use Inertia\Inertia;

class PostulanteController extends Controller
{

  public function getPostulanteXDni(Request $request)
  {
    $res = DB::table('postulante as p')
      ->select('p.id','p.primer_apellido','p.segundo_apellido','p.nombres','p.email as correo','p.celular','p.fec_nacimiento',
          'p.ubigeo_nacimiento as ubigeo','p.ubigeo_residencia','p.direccion','p.sexo','p.estado_civil',
          DB::raw("CONCAT(p.ubigeo_nacimiento,'-',depN.nombre,'/',provN.nombre,'/',distN.nombre) AS nacimiento"),
          DB::raw("CONCAT(p.ubigeo_residencia,'-',depR.nombre,'/',provR.nombre,'/',distR.nombre) AS residencia"))
      ->leftJoin('ubigeo as un', 'p.ubigeo_nacimiento', '=', 'un.ubigeo')
      ->leftJoin('departamento as depN', 'un.id_departamento', '=', 'depN.id')
      ->leftJoin('provincia as provN', 'un.id_provincia', '=', 'provN.id')
      ->leftJoin('distritos as distN', 'un.id_distrito', '=', 'distN.id')
      ->leftJoin('ubigeo as ur', 'p.ubigeo_residencia', '=', 'ur.ubigeo')
      ->leftJoin('departamento as depR', 'ur.id_departamento', '=', 'depR.id')
      ->leftJoin('provincia as provR', 'ur.id_provincia', '=', 'provR.id')
      ->leftJoin('distritos as distR', 'ur.id_distrito', '=', 'distR.id')
      ->where('p.nro_doc', $request->nro_doc)
      ->get();

      if(count($res) > 0){
        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
      }
      else{
        $this->response['estado'] = false;
        return response()->json($this->response, 200);
      }

  }


  public function getPostulanteXDni2(Request $request)
  {
    $res = DB::table('postulante as p')
        ->select('p.id','p.primer_apellido','p.segundo_apellido','p.nombres','p.email as correo','p.celular','p.fec_nacimiento',
            'p.anio_egreso as egreso','p.ubigeo_nacimiento as ubigeo','p.ubigeo_residencia','p.direccion','p.sexo','p.estado_civil',
            DB::raw("CONCAT(p.ubigeo_nacimiento,'-',depN.nombre,'/',provN.nombre,'/',distN.nombre) AS nacimiento"),
            DB::raw("CONCAT(p.ubigeo_residencia,'-',depR.nombre,'/',provR.nombre,'/',distR.nombre) AS residencia"))
        ->leftJoin('ubigeo as un', 'p.ubigeo_nacimiento', '=', 'un.ubigeo')
        ->leftJoin('departamento as depN', 'un.id_departamento', '=', 'depN.id')
        ->leftJoin('provincia as provN', 'un.id_provincia', '=', 'provN.id')
        ->leftJoin('distritos as distN', 'un.id_distrito', '=', 'distN.id')
        ->leftJoin('ubigeo as ur', 'p.ubigeo_residencia', '=', 'ur.ubigeo')
        ->leftJoin('departamento as depR', 'ur.id_departamento', '=', 'depR.id')
        ->leftJoin('provincia as provR', 'ur.id_provincia', '=', 'provR.id')
        ->leftJoin('distritos as distR', 'ur.id_distrito', '=', 'distR.id')
        ->where('p.nro_doc', $request->nro_doc)
        ->get();

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);

  }

  public function saveDniPostulante(Request $request) {

      $postulante = Postulante::create([
        'tipo_doc' => $request->tipo_doc,
        'nro_doc' => $request->nro_doc,
        'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
        'primer_apellido' => $request->paterno,
        'segundo_apellido' => $request->materno,
        'nombres' => $request->nombres,
        'ubigeo_residencia' => $request->ubigeo_nacimiento
      ]);
      $this->response['tipo'] = 'success';
      $this->response['titulo'] = 'REGISTRO NUEVO';
      $this->response['estado'] = true;
      $this->response['datos'] = $postulante;

      return response()->json($this->response, 200);
  }

  public function savePostulante(Request $request) {

    $solo_unapellido = 1;
    if($request->segundo_apellido === null){
      $solo_unapellido = 0;
    }

    $postulante = null;
    if (!$request->id) {
        $postulante = Postulante::create([
            'tipo_doc' => $request->tipo_doc,
            'nro_doc' => $request->nro_doc,
            'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
            'sexo' => $request->sexo,
            'estado_civil' => $request->estado_civil,
            'primer_apellido' => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'solo_un_apellido' => $solo_unapellido,
            'nombres' => $request->nombres,
            'email' => $request->correo,
            'celular' => $request->celular,
            'fec_nacimiento' => $request->fec_nacimiento,
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
        $postulante->nro_doc = $request->nro_doc;
        $postulante->ubigeo_nacimiento = $request->ubigeo_nacimiento;
        $postulante->sexo = $request->sexo;
        $postulante->estado_civil = $request->estado_civil;
        $postulante->primer_apellido = $request->primer_apellido;
        $postulante->segundo_apellido = $request->segundo_apellido;
        $postulante->solo_un_apellido = $solo_unapellido;
        $postulante->nombres = $request->nombres;
        $postulante->email = $request->correo;
        $postulante->celular = $request->celular;
        $postulante->fec_nacimiento = $request->fec_nacimiento;
        $postulante->id_usuario = auth()->id();
        $postulante->save();

        if( $temp == $postulante ) {
          $this->response['estado'] = false;
        }else
        {
          $this->response['tipo'] = 'info';
          $this->response['titulo'] = '!REGISTRO MODIFICADO!';
          $this->response['mensaje'] = 'Datos del '.$postulante->nombres.' actualizados';
          $this->response['estado'] = true;
          $this->response['datos'] = $postulante;
        }

      }

      return response()->json($this->response, 200);
    }

    public function saveResidencia(Request $request ) {

      // $validator = $request->validate([
      //   'ubigeo_residencia' => 'required',
      //   'direccion' => 'required',
      // ]);

      $postulante = Postulante::find($request->id);
      $temp = $postulante;
      if($request->pais){
        $postulante->id_pais = $request->pais;
        $postulante->direccion = $request->direccion;
      }else{
        $postulante->ubigeo_residencia = $request->ubigeo_residencia;
        $postulante->direccion = $request->direccion;
      }

      $postulante->save();

      if( $temp == $postulante ) {
        $this->response['estado'] = false;
      } else {
        $this->response['tipo'] = 'info';
        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = 'La residencia del '.$postulante->nombres.' se actualizó.';
        $this->response['estado'] = true;
        $this->response['datos'] = $postulante;
      }

        return response()->json($this->response, 200);
    }


    public function saveColegio(Request $request ) {

      $postulante = Postulante::find($request->id);
      $temp = $postulante;
      $postulante->anio_egreso = $request->anio_egreso;
      $postulante->id_colegio = $request->colegio;
      $postulante->save();

      if( $temp == $postulante ) {
        $this->response['estado'] = false;
      } else {
        $this->response['tipo'] = 'info';
        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = 'Los dato del colegio de '.$postulante->nombres.' se actualizó.';
        $this->response['estado'] = true;
        $this->response['datos'] = $postulante;
      }

      if($request->actualizar == 'si'){
        $this->savePasos("Datos colegio registrados", 3, 48, $request->id, $request->proceso);
      }

        return response()->json($this->response, 200);
    }


    private function savePasos($nom, $num, $avan, $pos, $pro) {

      $pasos = null;
      $pasos = Paso::create([
          'nombre' => $nom,
          'nro' => $num,
          'avance' => $avan,
          'postulante' => $pos,
          'proceso' => $pro
      ]);
      $this->response['tipo'] = 'success';
      $this->response['titulo'] = 'PASO REGISTRADO';
      $this->response['mensaje'] = 'Proceso '.$pasos->nombre.' creado con exito';
      $this->response['estado'] = true;
      $this->response['datos'] = $pasos;

      return response()->json($this->response, 200);
    }



  //END PASO 1 PRE INSCRIPCION


  //REVISOR


  public function getPostulantesBiometrico(Request $request)
  {
    // SELECT
    //   pos.primer_apellido, pos.segundo_apellido,
    //   pos.nombres, pro.nombre AS programa,
    //   pro.area AS areas, mo.nombre AS modalidad
    // FROM resultados res
    // JOIN postulante pos ON pos.nro_doc = res.dni_postulante
    // JOIN inscripciones ins ON ins.id_postulante = pos.id
    // AND ins.id_proceso = res.id_proceso AND ins.estado = 0
    // JOIN modalidad mo ON ins.id_modalidad = mo.id
    // JOIN programa pro ON ins.id_programa = pro.id
    // WHERE res.id_proceso = 7
    // ORDER BY pro.area, pro.nombre

      $query_where = [];
      $res = Ingresante::select(
        'postulante.id as id_postulante', 'postulante.nro_doc as dni',
        'postulante.primer_apellido', 'postulante.segundo_apellido',
        'postulante.nombres', 'programa.nombre as programa',
        'programa.area', 'modalidad.nombre as modalidad',
        'control_biometrico.codigo_ingreso as codigo'
      )
      ->join('postulante','postulante.nro_doc','resultados.dni_postulante')
      ->join('inscripciones','inscripciones.id_postulante','postulante.id')
      ->join('modalidad','modalidad.id','inscripciones.id_modalidad')
      ->join('programa','programa.id','inscripciones.id_programa')
      ->leftjoin('control_biometrico','control_biometrico.id_postulante','postulante.id')
      ->where('inscripciones.id_proceso','=',auth()->user()->id_proceso)
      ->where('resultados.id_proceso','=',auth()->user()->id_proceso)
      ->where('inscripciones.estado','=',0)
      ->where($query_where)
      ->where(function ($query) use ($request) {
          return $query
            ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
            ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
            ->orWhere(DB::raw("CONCAT(postulante.nombres, ' ', postulante.primer_apellido, ' ', postulante.segundo_apellido)"), 'LIKE', '%' . $request->term . '%')
            ->orWhere(DB::raw("CONCAT(postulante.primer_apellido, ' ', postulante.segundo_apellido, ' ', postulante.nombres)"), 'LIKE', '%' . $request->term . '%');
      })->orderBy('programa.area','ASC')
      ->orderBy('programa.nombre','ASC')
      ->paginate(20);

      $this->response['estado'] = true;
      $this->response['datos'] = $res;
      return response()->json($this->response, 200);
  }


  public function actualizarDatos(Request $request) {


      $registroActual = Postulante::find($request->id);
      $cambios = [];

      $fillable = ['primer_apellido', 'segundo_apellido', 'apellido_casada', 'nombres', 'sexo', 'fec_nacimiento'];

      foreach ($fillable as $campo) {
          if ($registroActual->$campo != $request->$campo) {
              $cambios[$campo] = [
                  'anterior' => $registroActual->$campo,
                  'nuevo' => $request->$campo,
              ];
              $registroActual->$campo = $request->$campo;
          }
      }

      foreach ($cambios as $campo => $valores) {
        if($valores['nuevo'] != null){
          $this->saveCambios('usuarios',$campo, $valores['anterior'], $valores['nuevo'], $request->id);
        }
      }

      $registroActual->save();

      $this->response['tipo'] = 'success';
      $this->response['titulo'] = 'REGISTRO ACTUALIZADO';
      $this->response['estado'] = true;
      return response()->json($this->response, 200);
  }


  public function actualizarDatosIngresante(Request $request) {

    $registroActual = Postulante::find($request->id);
    $registroActual->nombres = $request->nombres;
    $registroActual->primer_apellido = $request->paterno;
    $registroActual->segundo_apellido = $request->materno;
    $registroActual->sexo = $request->sexo;
    $registroActual->tipo_doc = $request->tipo_doc;
    $registroActual->fec_nacimiento = $request->fec_nacimiento;
    $registroActual->save();

    $this->response['tipo'] = 'success';
    $this->response['titulo'] = 'REGISTRO ACTUALIZADO';
    $this->response['estado'] = true;
    return response()->json($this->response, 200);
}

  private function saveCambios($tabla, $campo, $anterior, $nuevo, $id ){
    $cambio = Cambio::create([
      'tabla' => $tabla,
      'campo' => $campo,
      'valor_anterior' => $anterior,
      'valor_nuevo' => $nuevo,
      'id_usuario' => auth()->id(),
      'id_registro' => $id
    ]);
  }


  //CRUD
  public function getPostulantesAdmin(Request $request)
  {
    $query_where = [];

    $res = Postulante::select(
      'id', 'tipo_doc', 'nro_doc', 'primer_apellido', 'segundo_apellido', 'apellido_casada', 'nombres', 'sexo', 'fec_nacimiento',
      'ubigeo_nacimiento', 'ubigeo_residencia', 'celular', 'email', 'estado_civil','direccion','anio_egreso',
      'correo_institucional', 'cod_orcid', 'observaciones', 'id_colegio',
    )
    ->where($query_where)
    ->where(function ($query) use ($request) {
        return $query
            ->orWhere('nro_doc', 'LIKE', '%' . $request->term . '%')
            ->orWhere('email', 'LIKE', '%' . $request->term . '%')
            ->orWhere('celular', 'LIKE', '%' . $request->term . '%')
            ->orWhere(DB::raw("CONCAT(nombres, ' ', primer_apellido, ' ', segundo_apellido)"), 'LIKE', '%' . $request->term . '%')
            ->orWhere(DB::raw("CONCAT(primer_apellido, ' ', segundo_apellido, ' ', nombres)"), 'LIKE', '%' . $request->term . '%');
    })
    ->paginate(20);

    $this->response['estado'] = true;
    $this->response['datos'] = $res;
    return response()->json($this->response, 200);
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

  public function deleteModalidad($id){
    $modalidad = Modalidad::find($id);
    $p = $modalidad;
    $modalidad->delete();

    $this->response['titulo'] = '!REGISTRO ELIMINADO!';
    $this->response['mensaje'] = 'MODALIDAD '.$p->nombre.' ELIMINADA CON EXITO';
    $this->response['estado'] = true;
    $this->response['datos'] = $p;
    return response()->json($this->response, 200);
  }

  public function participa($dni){
    $existeRegistro = DB::table('puntajes')
      ->where('dni', $dni)
      ->where('apto', 'SI')
      ->exists();
    $sancionados = DB::table('sancionados')
      ->where('dni', $dni)
      ->exists();
    if($existeRegistro || $sancionados ){
      $this->response['estado'] = 0;
      return response()->json($this->response, 200);
    }else {
      $this->response['estado'] = 1;
      return response()->json($this->response, 200);
    }
  }


  public function getDataPrisma($dni)
  {
      $url = "https://erpprisma.com/rucdni/l_dni.php?dni=" . $dni;

      $response = Http::get($url);
      $data = explode("|", $response->body());

      if(count($data) >= 5){
          $resultado = [
            'dni' => $data[1],
            'nombre' => $data[2],
            'paterno' => $data[3],
            'materno' => $data[4],
        ];
        $this->response['datos'] = $resultado;
        $this->response['estado'] = true;
        return response()->json($this->response, 200);
      }
      else{
        $this->response['estado'] = false;
        return response()->json($this->response, 200);
      }

  }

  public function registrarCarreras(Request $request){

    $postulante = Postulante::where('nro_doc', $request->dni)->first();
    if($postulante){
      $postulante->carreras_previas = count($request->carreras);
      $postulante->save();
    }

    foreach ($request->carreras as $item) {
      $carrera = CarrerasPrevias::firstOrNew([
          'dni_postulante' => $request->dni,
          'cod_car' => $item['careerId']
      ], [
          'nombre' => $item['career'],
          'fecha' => now(),
          'codigo' => $item['code'],
          'condicion' => $item['cond1tion']
      ]);

      if (!$carrera->exists) {
          $carrera->save();
      }
  }

    $this->response['estado'] = true;
    return response()->json($this->response, 200);

  }

  public function verificarPadres (Request $request){
      $cantidad = Apoderado::join('postulante as pos', 'apoderado.id_postulante', '=', 'pos.id')
      ->whereIn('apoderado.nro_documento', [$request->dnipadre, $request->dnimadre])
      ->where('pos.nro_doc', $request->dni)
      ->count();

      // if($cantidad >= 1){
      //   return response()->json(['estado' => true]);
      // }else {
      //   return response()->json(['estado' => false]);
      // }

      return true;

  }


  public function getCarrerasPrevias(Request $request)
  {
      $participante = $request->input('participante', null);
      $formState = $request->input('formState', null);
      $dni = $participante ? $participante['dni'] : ($formState ? $formState : null);

      if (!$dni) {
          return response()->json([
              'anteriores' => [],
              'loading' => false,
              'modalSancionado' => false,
              'confirmacion' => false,
              'message' => 'No se proporcionaron datos válidos'
          ], 400);
      }

      $payload = [ 'doc_' => $formState, 'nom_' => 'DIRECCIÓN', 'app_' => 'ADMISIÓN', 'apm_' => 'UNAP'];

      try {
          $response = Http::timeout(5)->withHeaders([
              'Content-Type' => 'application/json'
          ])->post('https://service2.unap.edu.pe/TieneCarrerasPrevias/', $payload);

          $data = $response->json();
      } catch (\Exception $e) {
          return response()->json([
              'anteriores' => [],
              'loading' => false,
              'modalSancionado' => false,
              'confirmacion' => false,
              'message' => 'Servicio no disponible'
          ]);
      }

      if ((is_array($data) || $data instanceof Countable) && count($data) > 0) {
          foreach ($data as $item) {
              CarrerasPrevias::updateOrCreate(
                  [
                      'dni_postulante' => $dni,
                      'cod_car' => $item['careerId'],
                  ],
                  [
                      'codigo' => $item['code'],
                      'nombre' => $item['name'],
                      'condicion' => $item['cond1tion'],
                  ]
              );
          }

          if (count($data) == 1) {
              return response()->json([
                  'anteriores' => [],
                  'loading' => false,
                  'modalSancionado' => false,
                  'confirmacion' => false,
                  'message' => 'No tiene carreras previas'
              ]);
          }

          return response()->json([
              'anteriores' => $data,
              'loading' => false,
              'modalSancionado' => false,
              'confirmacion' => false,
              'message' => 'Tiene carreras previas'
          ]);
      }

      return response()->json([
          'anteriores' => [],
          'loading' => false,
          'modalSancionado' => false,
          'confirmacion' => false,
          'message' => 'No tiene carreras previas'
      ]);
  }


    //segundas
    public function savePostulanteSegundas(Request $request) {
        DB::beginTransaction();
        try {
            $solo_unapellido = $request->segundo_apellido === null ? 0 : 1;

            if (!$request->id) {
                // Crear nuevo postulante
                $postulante = Postulante::create([
                    'tipo_doc' => $request->tipo_doc,
                    'nro_doc' => $request->nro_doc,
                    'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
                    'ubigeo_residencia' => $request->ubigeo_residencia,
                    'sexo' => $request->sexo,
                    'estado_civil' => $request->estado_civil,
                    'primer_apellido' => $request->primer_apellido,
                    'segundo_apellido' => $request->segundo_apellido,
                    'solo_un_apellido' => $solo_unapellido,
                    'direccion'=> $request->direccion,
                    'anio_egreso'=> $request->egreso,
                    'nombres' => $request->nombres,
                    'email' => $request->correo,
                    'celular' => $request->celular,
                    'fec_nacimiento' => $request->fec_nacimiento,
                ]);

                $paso = Paso::firstOrCreate(
                    ['nombre' => 'REGISTRO DE DATOS PERSONALES SEGUNDAS', 'proceso' => $request->proceso],
                    ['nro' => 5, 'avance' => 80, 'postulante' => $postulante->id]
                );

                $this->response = [
                    'tipo' => 'success',
                    'titulo' => 'REGISTRO NUEVO',
                    'mensaje' => 'Proceso ' . $postulante->nombres . ' creado con éxito',
                    'estado' => true,
                    'datos' => $postulante
                ];
            } else {

                $postulante = Postulante::findOrFail($request->id);
                $original = clone $postulante;

                $postulante->update([
                    'tipo_doc' => $request->tipo_doc,
                    'nro_doc' => $request->nro_doc,
                    'ubigeo_nacimiento' => $request->ubigeo_nacimiento,
                    'ubigeo_residencia' => $request->ubigeo_residencia,
                    'sexo' => $request->sexo,
                    'estado_civil' => $request->estado_civil,
                    'anio_egreso' => $request->egreso,
                    'primer_apellido' => $request->primer_apellido,
                    'segundo_apellido' => $request->segundo_apellido,
                    'solo_un_apellido' => $solo_unapellido,
                    'nombres' => $request->nombres,
                    'email' => $request->correo,
                    'direccion' => $request->direccion,
                    'celular' => $request->celular,
                    'fec_nacimiento' => $request->fec_nacimiento,
                    'id_usuario' => auth()->id()
                ]);

                Paso::firstOrCreate(
                  ['nombre' => 'REGISTRO DE DATOS PERSONALES SEGUNDAS', 'proceso' => $request->proceso],
                  ['nro' => 5, 'avance' => 80, 'postulante' => $postulante->id]
                );

                if ($original->getAttributes() == $postulante->getAttributes()) {
                    $this->response['estado'] = false;
                } else {
                    $this->response = [
                        'tipo' => 'info',
                        'titulo' => '!REGISTRO MODIFICADO!',
                        'mensaje' => 'Datos de ' . $postulante->nombres . ' actualizados',
                        'estado' => true,
                        'datos' => $postulante
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'tipo' => 'error',
                'titulo' => 'ERROR',
                'mensaje' => 'Ocurrió un problema: ' . $e->getMessage(),
                'estado' => false
            ], 500);
        }

        return response()->json($this->response, 200);
    }


}
