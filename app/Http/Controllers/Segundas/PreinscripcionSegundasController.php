<?php
namespace App\Http\Controllers\Segundas;
use App\Http\Controllers\Controller;
use App\Models\Preinscripcion;
use App\Models\Proceso;
use Mpdf\Mpdf;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PreinscripcionSegundasController extends Controller
{

    public function getPreinscripciones(Request $request) {

        $query_where = [];
        if (auth()->user()->programas != null) {
            $array = json_decode(auth()->user()->programas, true);
            if (!empty($array)) {
                $query_where[] = ['pre_inscripcion.id_programa', $array];
            }
        }

        if ($request->programa) {
            $query_where[] = ['pre_inscripcion.id_programa', '=', $request->programa];
        }
        $query_where[] = ['pre_inscripcion.id_proceso', '=', auth()->user()->id_proceso];

        $res = Preinscripcion::select(
            'pre_inscripcion.id as id', 'postulante.id as id_postulante', 'postulante.nro_doc AS dni',
            'postulante.nombres AS nombres',
            'postulante.primer_apellido AS paterno', 'postulante.segundo_apellido AS materno',
            'programa.nombre as programa', 'pre_inscripcion.id_programa as id_programa',
            'modalidad.id as id_modalidad', 'modalidad.nombre as modalidad', 'procesos.nombre AS proceso',
            'pre_inscripcion.created_at as fecha', 'postulante.sexo',
            'inscripciones.estado',
            'pre_inscripcion.observacion'
        )
        ->join('postulante','pre_inscripcion.id_postulante', 'postulante.id')
        ->leftJoin('inscripciones', function($join) {
            $join->on('inscripciones.id_postulante', '=', 'postulante.id')
                ->where('inscripciones.id_proceso', '=', auth()->user()->id_proceso);
        })
        ->join('programa','pre_inscripcion.id_programa', 'programa.id')
        ->join('modalidad','pre_inscripcion.id_modalidad', 'modalidad.id')
        ->join('procesos','pre_inscripcion.id_proceso', 'procesos.id')
        ->when(!empty($query_where), function ($query) use ($query_where) {
            foreach ($query_where as $condition) {
                if (is_array($condition[1])) {
                    $query->whereIn($condition[0], $condition[1]);
                } else {
                    $query->where($condition[0], $condition[1], $condition[2] ?? null);
                }
            }
        })
        ->where(function ($query) use ($request) {
            return $query
                ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
                ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%')
                ->orWhere('modalidad.nombre', 'LIKE', '%' . $request->term . '%');
        })
        ->paginate($request->paginashoja);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function Actualizar(Request $request){

        $preinscripcion = Preinscripcion::find($request->id);

        if( $preinscripcion->id_programa != $request->id_programa) {
            $preinscripcion->observacion = "$preinscripcion->observacion - Cambio de programa de $preinscripcion->id_programa a $request->id_programa ";
            $preinscripcion->id_programa = $request->id_programa;
        }
        if ( $preinscripcion->id_modalidad != $request->id_modalidad ) {
            $preinscripcion->observacion = "$preinscripcion->observacion, Cambio de modalidad de $preinscripcion->id_modalidad a $request->id_modalidad";
            $preinscripcion->id_modalidad = $request->id_modalidad;
        }
        if($request->observacion != ''){
            $preinscripcion->observacion = "$preinscripcion->observacion, ( $request->observacion )";
        }
        $preinscripcion->save();
        // $this->pdfsolicitud(auth()->user()->id_proceso,$request->dni);

        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);

    }


    public function Inscribir(Request $request){

        $proceso = Proceso::find(auth()->user()->id_proceso);

        $prog = str_pad($request->id_programa, 2, '0', STR_PAD_LEFT);
        $res = Inscripcion::where('codigo', 'like', $proceso->codigo_proceso.$prog.'%')
            ->max(\DB::raw('CAST(SUBSTRING(codigo, 7) AS UNSIGNED)')) + 1;
        $res = str_pad($res, 4, '0', STR_PAD_LEFT);

        $inscripcion = Inscripcion::create([
            'codigo' => $proceso->codigo_proceso . $prog . $res,
            'id_postulante' => $request->id_postulante,
            'id_programa' => $request->id_programa,
            'id_modalidad' => $request->id_modalidad,
            'observacion' => $request->observacion,
            'estado' => 0,
            'id_proceso' => auth()->user()->id_proceso,
            'id_usuario' => auth()->id()
        ]);

        $this->response['titulo'] = '!REGISTRO ACTUALIZADO!';
        $this->response['mensaje'] = '';
        $this->response['estado'] = true;
        return response()->json($this->response, 200);

    }


    public function pdfPreinscripcion($id_proceso, $dni)
    {
        try {

        $datos = DB::table('pre_inscripcion as pre')
            ->join('procesos as proc','proc.id','=','pre.id_proceso')
            ->join('postulante as pos','pos.id','=','pre.id_postulante')
            ->join('ubigeo as ub','ub.ubigeo','=','pos.ubigeo_residencia')
            ->join('departamento as dep','dep.id','=','ub.id_departamento')
            ->join('provincia as prov','prov.id','=','ub.id_provincia')
            ->join('distritos as dist','dist.id','=','ub.id_distrito')
            ->join('paises as pais','pais.id','=','pos.id_pais')
            ->join('programa as pro','pro.id','=','pre.id_programa')
            ->join('facultad as fac','fac.id','=','pro.id_facultad')
            ->join('modalidad as mo','mo.id','=','pre.id_modalidad')

            ->selectRaw(" pos.tipo_doc, pos.nro_doc, 
            CONCAT(
                UPPER(LEFT(LOWER(pos.nombres),1)),
                SUBSTRING(LOWER(pos.nombres),2, LOCATE(' ', CONCAT(pos.nombres, ' ')) - 1),
                IF(LOCATE(' ', pos.nombres) > 0,
                    CONCAT(
                        ' ',
                        UPPER(SUBSTRING(LOWER(pos.nombres), LOCATE(' ', pos.nombres) + 1, 1)),
                        SUBSTRING(LOWER(pos.nombres), LOCATE(' ', pos.nombres) + 2)
                    ),
                    ''
                )
            ) as nombres,
            CONCAT(UPPER(LEFT(LOWER(pos.primer_apellido),1)),SUBSTRING(LOWER(pos.primer_apellido),2)) as paterno,
            CONCAT(UPPER(LEFT(LOWER(pos.segundo_apellido),1)),SUBSTRING(LOWER(pos.segundo_apellido),2)) as materno,
            pos.direccion, pos.sexo, pais.codigo, dep.nombre as departamento, prov.nombre as provincia, dist.nombre as distrito, 
            pro.nombre as programa, pos.celular, pos.email,
            DATE_FORMAT(pos.fec_nacimiento,'%d/%m/%Y') as fec_nacimiento,

            CONCAT(
            DAY(pos.fec_nacimiento),' de ',
            ELT(MONTH(pos.fec_nacimiento),
            'enero','febrero','marzo','abril','mayo','junio',
            'julio','agosto','septiembre','octubre','noviembre','diciembre'
            ),
            ' de ',
            YEAR(pos.fec_nacimiento)
            ) as fecha_nacimiento_texto,
            fac.facultad AS facultad,
            pos.ubigeo_nacimiento
            ")

            ->where('pre.id_proceso',$id_proceso)
            ->where('pos.nro_doc',$dni)
            ->first();

        // return $datos;

            if (!$datos || !isset($dni)) {
                throw new \Exception('Datos inválidos para generar el PDF');
            }
            
            if (!preg_match('/^[0-9]{8}$/', $dni)) {
                return response()->json(['error' => 'Formato de DNI inválido'], 400);
            }
            
            $data = [
                'dni' => $dni,
                'fecha_generacion' => now()->format('d/m/Y H:i:s'),
                'usuario' => auth()->user()->name ?? 'Sistema'
            ];
            
            $html = view('Segundas.Preinscripcion.anexos', $data, compact('datos'))->render();
            
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'default_font_size' => 12,
                'default_font' => 'sans-serif',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 16,
                'margin_bottom' => 16,
                'margin_header' => 9,
                'margin_footer' => 9
            ]);
            
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;
            
            $mpdf->WriteHTML($html);
            
            $output = $mpdf->Output('', 'S');
            
            $idProceso = auth()->user()->id_proceso ?? 'sin_proceso';
            $basePath = "documentos/{$idProceso}/preinscripcion/constancias";
            $rutaCarpeta = public_path($basePath);
            
            if (!File::exists($rutaCarpeta)) {
                File::makeDirectory($rutaCarpeta, 0755, true);
            }
            
            $nombreArchivo = "preinscripcion_{$dni}_" . date('Ymd_His') . ".pdf";
            $rutaCompleta = $rutaCarpeta . '/' . $nombreArchivo;
            
            // file_put_contents($rutaCompleta, $output);
            
            return response()->stream(function() use ($output) {
                echo $output;
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preinscripcion_' . $dni . '.pdf"',
                'Content-Length' => strlen($output),
                'Cache-Control' => 'public, must-revalidate, max-age=0',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }





}
