<?php

namespace App\Http\Controllers;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exports\ReporteSuneduExport;
use Excel;
use Pdf;
use DB;

class ReporteController extends Controller
{
    public function reportePrograma(Request $request) {

        $proceso = Proceso::find(auth()->user()->id_proceso);

        $res = DB::select("SELECT 
            pre.cod_pro AS codigo,
            COALESCE(pre.programa, ins.programa) AS programa,
            COALESCE(ins.inscripciones, 0) AS inscripciones,
            COALESCE(pre.preinscripciones, 0) AS preinscripciones,
            COALESCE(pre.preinscripciones, 0) - COALESCE(ins.inscripciones, 0) AS diferencia
        FROM (
            SELECT pro.id AS cod_pro, pro.nombre AS programa, COUNT(*) AS preinscripciones FROM pre_inscripcion pre
            JOIN programa pro ON pre.id_programa = pro.id
            WHERE pre.id_proceso = ".auth()->user()->id_proceso." AND pre.estado = 1
            GROUP BY pro.id, pro.nombre
        ) pre
        INNER JOIN (
            SELECT pro.nombre AS programa, COUNT(*) AS inscripciones
            FROM inscripciones ins
            JOIN programa pro ON ins.id_programa = pro.id
            WHERE ins.id_proceso = ".auth()->user()->id_proceso." AND ins.estado = 0
            GROUP BY pro.nombre
        ) ins ON pre.programa = ins.programa
        ORDER BY inscripciones desc;");

        $totales = DB::select("SELECT
            SUM(COALESCE(ins.inscripciones, 0)) AS total_inscripciones,
            SUM(COALESCE(pre.preinscripciones, 0)) AS total_preinscripciones,
            SUM(COALESCE(pre.preinscripciones, 0)) - SUM(COALESCE(ins.inscripciones, 0)) AS total_diferencia
        FROM (
            SELECT COUNT(*) AS preinscripciones
            FROM pre_inscripcion pre
            WHERE pre.id_proceso = ".auth()->user()->id_proceso." AND pre.estado = 1
            ) pre
        INNER JOIN (
        SELECT COUNT(*) AS inscripciones
            FROM inscripciones ins
            WHERE ins.id_proceso = ".auth()->user()->id_proceso." AND ins.estado = 0
            ) ins;");

        if($request->descargar == 1){
            $pdf = Pdf::loadView('Reportes.programa_pre_ins', compact('res', 'proceso','totales'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/".auth()->user()->id_proceso."/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteProgramas_' . date('Y-m-d_H-i-s') .auth()->id(). '.pdf';        
            if (!file_exists($rutaCarpeta)) { mkdir($rutaCarpeta, 0755, true); }
        
            file_put_contents($rutaArchivo, $pdf->output());
            return $pdf->stream(date('d/m/Y H:i:s')." ".auth()->id()." Resumen por programa.pdf");
        }else{
            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }

    }


    public function reporteProgramaDiario(Request $request)
    {
        $proceso = Proceso::find(auth()->user()->id_proceso);
        $fechas = DB::table('inscripciones')
            ->selectRaw('DATE(created_at) as fecha')
            ->where('id_proceso', auth()->user()->id_proceso)
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->pluck('fecha')
            ->toArray();

        if (empty($fechas)) {
            return response()->json(['message' => 'No hay datos disponibles para este proceso'], 404);
        }
    
        $columnasFecha = implode(", ", array_map(function ($fecha) {
            return "COALESCE(SUM(CASE WHEN DATE(ins.created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `ins_$fecha`";
        }, $fechas));
    
        $query = "SELECT p.nombre AS programa, p.id AS codigo, $columnasFecha, COUNT(ins.id) AS inscripciones
            FROM programa p
            LEFT JOIN inscripciones ins ON p.id = ins.id_programa 
                AND ins.id_proceso = ? 
            WHERE p.id_filial = $proceso->id_sede_filial 
            AND p.nivel_academico = 'CARRERA PROFESIONAL'
            AND p.estado = 1
            GROUP BY p.nombre, p.id 
            ORDER BY p.id";

        $res = DB::select($query, [auth()->user()->id_proceso]);
        
        $columnasTotales = implode(", ", array_map(function ($fecha) {
            return "COALESCE(SUM(CASE WHEN DATE(created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `ins_$fecha`";
        }, $fechas));
    
        $totales = DB::select("SELECT $columnasTotales, COUNT(id) AS total_inscripciones FROM inscripciones 
            WHERE id_proceso = ? ", [auth()->user()->id_proceso]);
    
        if($request->descargar == 1){

            $pdf = Pdf::loadView('Reportes.programa_ins', compact('res', 'proceso', 'totales', 'fechas')); 
            $pdf->getDomPDF()->set_option("isPhpEnabled", true); 
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true); 
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/".auth()->user()->id_proceso."/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteProgramas_' . date('Y-m-d_H-i-s') . auth()->id() . '.pdf';
        
            if (!file_exists($rutaCarpeta)) { mkdir($rutaCarpeta, 0755, true); } 
            file_put_contents($rutaArchivo, $pdf->output()); 
            return $pdf->stream(date('d/m/Y H:i:s')." Reporte inscripciones diarias por programa.pdf"); 

        }else{

            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['fechas'] = $fechas;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }

    }



    public function reporteUsuarios(Request $request)
    {
        $sim = auth()->user()->id_proceso;
        $proceso = Proceso::find($sim);

        $fechas = DB::table('inscripciones')
            ->select(DB::raw('DATE(created_at) as fecha'))
            ->where('id_proceso', $sim)
            ->where('estado', 0)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->pluck('fecha')
            ->toArray();

        if (empty($fechas)) {
            return response()->json(['message' => 'No hay datos disponibles para este proceso'], 404);
        }

        $columnas = [];
        foreach ($fechas as $fecha) {
            $columnas[] = "COALESCE(SUM(CASE WHEN DATE(ins.created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `$fecha`";
        }

        $columnas[] = "COALESCE(SUM(CASE WHEN DATE(ins.created_at) IN ('" . implode("','", $fechas) . "') THEN 1 ELSE 0 END), 0) AS total";


        $query = "
            SELECT 
                upper(users.name) as name,
                upper(users.paterno) as paterno,
                " . implode(", ", $columnas) . "
            FROM inscripciones ins
            JOIN users ON users.id = ins.id_usuario
            WHERE ins.estado = 0 AND ins.id_proceso = ?
            GROUP BY users.paterno, users.name
            ORDER BY Total DESC
        ";


        $columnasTotales = [];
        foreach ($fechas as $fecha) {
            $columnasTotales[] = "COALESCE(SUM(CASE WHEN DATE(created_at) = '$fecha' THEN 1 ELSE 0 END), 0) AS `$fecha`";
        }
        $columnasTotales[] = "COUNT(id) AS total_inscripciones";

        $totales = DB::select("
            SELECT " . implode(", ", $columnasTotales) . "
            FROM inscripciones 
            WHERE id_proceso = ? AND estado = 0
        ", [$sim]);

        $res = DB::select($query, [$sim]);

        
        if($request->descargar == 1){

            $pdf = Pdf::loadView('Reportes.usuarios_inscripciones', compact('res', 'fechas', 'proceso','totales'));
            $pdf->getDomPDF()->set_option("isPhpEnabled", true);
            $pdf->getDomPDF()->set_option("isHtml5ParserEnabled", true);
            $pdf->setPaper('A4', 'portrait');

            $rutaCarpeta = public_path("/documentos/$sim/reportes/");
            $rutaArchivo = $rutaCarpeta . 'ReporteUsuarios_' . date('Y-m-d_H-i-s') . auth()->id() . '.pdf';

            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0755, true);
            }

            file_put_contents($rutaArchivo, $pdf->output());

            return $pdf->stream(date('d/m/Y H:i:s')." Reporte inscripciones diarias del usuario.pdf");

        }else{

            $this->response['estado'] = true;
            $this->response['datos'] = $res;
            $this->response['fechas'] = $fechas;
            $this->response['totales'] = $totales;
            return response()->json($this->response, 200);
        }


    }

    /**
     * Reporte SUNEDU con Identidad Cultural
     * Devuelve JSON con los datos del reporte, o descarga Excel si descargar=1
     */
    public function reporteSunedu(Request $request)
    {
        $id_proceso = auth()->user()->id_proceso;
        $incluirIdentidad = filter_var($request->input('incluir_identidad', false), FILTER_VALIDATE_BOOLEAN);

        try {
            // 1️⃣ Obtener el reporte principal
            $reporte = DB::select("
                SELECT
                    fil.codigo AS 'CODIGO_SEDE_FILIAL',
                    '1' AS 'TIPO_PROCESO',
                    CONCAT(proc.anio,'-',proc.ciclo) AS 'PROCESO_ADMISION',
                    '' AS 'NUMERO_CONVOCATORIA',
                    pos.tipo_doc AS 'TIPO_DOCUMENTO',
                    pos.nro_doc AS 'NRO_DOCUMENTO',
                    UPPER(pos.nombres) AS 'NOMBRES',
                    UPPER(pos.primer_apellido) AS 'PRIMER_APELLIDO',
                    UPPER(pos.segundo_apellido) AS 'SEGUNDO_APELLIDO',
                    pos.apellido_casada AS 'APELLIDO_CASADA',
                    IF(pos.segundo_apellido IS NULL, 1, 0) AS 'SOLO_UN_APELLIDO',
                    pos.sexo AS 'SEXO',
                    DATE_FORMAT(pos.fec_nacimiento, '%d/%m/%Y') AS 'FECHA_NACIMIENTO',
                    pais.codigo AS 'PAIS_NACIMIENTO',
                    pais.nacionalidad AS 'NACIONALIDAD',
                    pos.ubigeo_nacimiento AS 'UBIGEO_NACIMIENTO',
                    pos.ubigeo_residencia AS 'UBIGEO_DOMICILIO',
                    IF(pos.tipo_discapacidad IS NOT NULL AND pos.tipo_discapacidad != '' AND pos.tipo_discapacidad != '0', 1, 0) AS 'CONDICION_DISCAPACIDAD',
                    pos.tipo_discapacidad AS 'TIPO_DISCAPACIDAD',
                    pos.celular AS 'CELULAR',
                    pos.email AS 'CORREO_PERSONAL',
                    fac.codigo AS 'CODIGO_FACULTAD_UNIDAD',
                    pro.codigo_sunedu AS 'CODIGO_PROGRAMA_OPCIONES',
                    DATE_FORMAT(DATE(ins.created_at), '%d/%m/%Y') AS 'FECHA_REGISTRO',
                    (pun.puntaje + pun.puntaje_vocacional) AS 'PUNTAJE_OBTENIDO',
                    mo.codigo AS 'MODALIDAD_ADMISION',
                    mo.nombre AS 'NOMBRE_MODALIDAD',
                    pro.nombre AS 'NOMBRE_PROGRAMA',
                    IF(pun.apto = 'SI', 1, '') AS 'MODALIDAD_ESTUDIO',
                    IF(pun.apto = 'SI', 1, 0) AS 'ES_INGRESANTE',
                    IF(pun.apto = 'SI', fac.codigo, '') AS 'CODIGO_FACULTAD_UNIDAD_INGRESO',
                    IF(pun.apto = 'SI', pro.codigo_sunedu, '') AS 'CODIGO_PROGRAMA_INGRESO',
                    IF(pun.apto = 'SI', DATE_FORMAT(DATE(pun.fecha),'%d/%m/%Y'), '') AS 'FECHA_INGRESO',
                    IF(cb.id IS NOT NULL, pos.correo_institucional, '') AS 'CORREO_INSTITUCIONAL',
                    '' AS 'CODIGO_ORCID',
                    CASE
                        WHEN pos.celular IS NULL OR pos.celular = '' OR LENGTH(pos.celular) < 9 THEN 'CELULAR_INCOMPLETO'
                        WHEN pos.celular NOT REGEXP '^[0-9]{9}$' THEN 'CELULAR_INVALIDO'
                        ELSE 'OK'
                    END AS 'VALIDACION_CELULAR',
                    CASE
                        WHEN pos.email IS NULL OR pos.email = '' THEN 'EMAIL_VACIO'
                        WHEN pos.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN 'EMAIL_INVALIDO'
                        ELSE 'OK'
                    END AS 'VALIDACION_EMAIL',
                    pos.id AS 'POSTULANTE_ID',
                    ins.id_proceso AS 'PROCESO_ID'
                FROM inscripciones ins
                JOIN procesos proc ON proc.id = ins.id_proceso
                JOIN filial fil ON fil.id = proc.id_sede_filial
                JOIN postulante pos ON pos.id = ins.id_postulante
                JOIN paises pais ON pais.id = pos.id_pais
                JOIN programa pro ON pro.id = ins.id_programa
                JOIN facultad fac ON fac.id = pro.id_facultad
                JOIN modalidad mo ON ins.id_modalidad = mo.id
                LEFT JOIN puntajes pun ON pun.id_proceso = :proceso_id1 AND pun.dni = pos.nro_doc AND pun.apto IN ('SI','NO')
                LEFT JOIN control_biometrico cb ON cb.id_proceso = :proceso_id2 AND cb.id_postulante = pos.id
                WHERE ins.id_proceso = :proceso_id3 AND ins.estado = 0
                ORDER BY pun.apto DESC, pro.id
            ", [
                'proceso_id1' => $id_proceso,
                'proceso_id2' => $id_proceso,
                'proceso_id3' => $id_proceso,
            ]);

            if (empty($reporte)) {
                if ($request->descargar == 1) {
                    $collection = collect([]);
                    return Excel::download(new ReporteSuneduExport($collection), 'reporte_sunedu_vacio.xlsx');
                }
                $this->response['estado'] = true;
                $this->response['datos'] = [];
                $this->response['total'] = 0;
                return response()->json($this->response, 200);
            }

            // 2️⃣ Obtener todos los IDs de postulantes
            $postulantesIds = array_unique(array_column($reporte, 'POSTULANTE_ID'));

            // 3️⃣ Consumir el servicio de identidad cultural (solo si se solicita)
            $identidades = [];
            if ($incluirIdentidad) {
                try {
                    $response = Http::timeout(10)
                        ->post(
                            "https://test-admision.unap.edu.pe/service_identidad/api/v1/identidad-cultural/by-postulantes",
                            [
                                'postulante_ids' => $postulantesIds,
                                'proceso_id' => (int) $id_proceso,
                            ]
                        );
                    $identidades = $response->successful() ? $response->json() : [];
                } catch (\Exception $ex) {
                    Log::warning('Servicio identidad cultural no disponible: ' . $ex->getMessage());
                }
            }

            // 4️⃣ Crear un mapa para búsqueda rápida por postulante_id
            $mapaIdentidades = [];
            foreach ($identidades as $identidad) {
                $postulanteId = $identidad['id_postulante'] ?? null;
                if ($postulanteId) {
                    $mapaIdentidades[$postulanteId] = $identidad;
                }
            }

            // 5️⃣ Unir los datos del reporte con las identidades culturales
            $reporteFinal = array_map(function ($row) use ($mapaIdentidades, $incluirIdentidad) {
                if ($incluirIdentidad) {
                    $postulanteId = $row->POSTULANTE_ID;
                    $identidad = $mapaIdentidades[$postulanteId] ?? [];

                    $row->ID_IDENTIDAD_CULTURAL = $identidad['id'] ?? null;
                    $row->ID_PUEBLO_INDIGENA = $identidad['id_pueblo_indigena'] ?? null;
                    $row->ID_LENGUA_INDIGENA = $identidad['id_lengua_indigena'] ?? null;
                    $row->ID_CONDICION_LENGUA = $identidad['id_condicion_lengua'] ?? null;
                    $row->ID_PERTENENCIA_CULTURAL = $identidad['id_pertenencia_cultural'] ?? null;
                    $row->FECHA_REGISTRO_IDENTIDAD = $identidad['created_at'] ?? null;
                    $row->FECHA_ACTUALIZACION_IDENTIDAD = $identidad['updated_at'] ?? null;
                    $row->ESTADO_IDENTIDAD = isset($identidad['id']) ? 'REGISTRADO' : 'NO_REGISTRADO';
                }

                return $row;
            }, $reporte);

            // 📋 Devolver JSON
            $this->response['estado'] = true;
            $this->response['datos'] = $reporteFinal;
            $this->response['total'] = count($reporteFinal);
            return response()->json($this->response, 200);

        } catch (\Exception $e) {
            Log::error('Error en reporte SUNEDU: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al generar reporte',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Descargar Excel del Reporte SUNEDU (con códigos)
     */
    public function descargarExcelSunedu(Request $request)
    {
        $id_proceso = auth()->user()->id_proceso;
        $incluirIdentidad = filter_var($request->input('incluir_identidad', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $reporte = DB::select("
                SELECT
                    fil.codigo AS 'CODIGO_SEDE_FILIAL',
                    '1' AS 'TIPO_PROCESO',
                    CONCAT(proc.anio,'-',proc.ciclo) AS 'PROCESO_ADMISION',
                    '' AS 'NUMERO_CONVOCATORIA',
                    pos.tipo_doc AS 'TIPO_DOCUMENTO',
                    pos.nro_doc AS 'NRO_DOCUMENTO',
                    UPPER(pos.nombres) AS 'NOMBRES',
                    UPPER(pos.primer_apellido) AS 'PRIMER_APELLIDO',
                    UPPER(pos.segundo_apellido) AS 'SEGUNDO_APELLIDO',
                    pos.apellido_casada AS 'APELLIDO_CASADA',
                    IF(pos.segundo_apellido IS NULL, 1, 0) AS 'SOLO_UN_APELLIDO',
                    pos.sexo AS 'SEXO',
                    DATE_FORMAT(pos.fec_nacimiento, '%d/%m/%Y') AS 'FECHA_NACIMIENTO',
                    pais.codigo AS 'PAIS_NACIMIENTO',
                    pais.nacionalidad AS 'NACIONALIDAD',
                    pos.ubigeo_nacimiento AS 'UBIGEO_NACIMIENTO',
                    pos.ubigeo_residencia AS 'UBIGEO_DOMICILIO',
                    IF(pos.tipo_discapacidad IS NOT NULL AND pos.tipo_discapacidad != '' AND pos.tipo_discapacidad != '0', 1, 0) AS 'CONDICION_DISCAPACIDAD',
                    pos.tipo_discapacidad AS 'TIPO_DISCAPACIDAD',
                    pos.celular AS 'CELULAR',
                    pos.email AS 'CORREO_PERSONAL',
                    fac.codigo AS 'CODIGO_FACULTAD_UNIDAD',
                    pro.codigo_sunedu AS 'CODIGO_PROGRAMA_OPCIONES',
                    DATE_FORMAT(DATE(ins.created_at), '%d/%m/%Y') AS 'FECHA_REGISTRO',
                    (pun.puntaje + pun.puntaje_vocacional) AS 'PUNTAJE_OBTENIDO',
                    mo.codigo AS 'MODALIDAD_ADMISION',
                    IF(pun.apto = 'SI', 1, '') AS 'MODALIDAD_ESTUDIO',
                    IF(pun.apto = 'SI', 1, 0) AS 'ES_INGRESANTE',
                    IF(pun.apto = 'SI', fac.codigo, '') AS 'CODIGO_FACULTAD_UNIDAD_INGRESO',
                    IF(pun.apto = 'SI', pro.codigo_sunedu, '') AS 'CODIGO_PROGRAMA_INGRESO',
                    IF(pun.apto = 'SI', DATE_FORMAT(DATE(pun.fecha),'%d/%m/%Y'), '') AS 'FECHA_INGRESO',
                    IF(cb.id IS NOT NULL, pos.correo_institucional, '') AS 'CORREO_INSTITUCIONAL',
                    '' AS 'CODIGO_ORCID',
                    CASE
                        WHEN pos.celular IS NULL OR pos.celular = '' OR LENGTH(pos.celular) < 9 THEN 'CELULAR_INCOMPLETO'
                        WHEN pos.celular NOT REGEXP '^[0-9]{9}$' THEN 'CELULAR_INVALIDO'
                        ELSE 'OK'
                    END AS 'VALIDACION_CELULAR',
                    CASE
                        WHEN pos.email IS NULL OR pos.email = '' THEN 'EMAIL_VACIO'
                        WHEN pos.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN 'EMAIL_INVALIDO'
                        ELSE 'OK'
                    END AS 'VALIDACION_EMAIL',
                    pos.id AS 'POSTULANTE_ID',
                    ins.id_proceso AS 'PROCESO_ID'
                FROM inscripciones ins
                JOIN procesos proc ON proc.id = ins.id_proceso
                JOIN filial fil ON fil.id = proc.id_sede_filial
                JOIN postulante pos ON pos.id = ins.id_postulante
                JOIN paises pais ON pais.id = pos.id_pais
                JOIN programa pro ON pro.id = ins.id_programa
                JOIN facultad fac ON fac.id = pro.id_facultad
                JOIN modalidad mo ON ins.id_modalidad = mo.id
                LEFT JOIN puntajes pun ON pun.id_proceso = :proceso_id1 AND pun.dni = pos.nro_doc AND pun.apto IN ('SI','NO')
                LEFT JOIN control_biometrico cb ON cb.id_proceso = :proceso_id2 AND cb.id_postulante = pos.id
                WHERE ins.id_proceso = :proceso_id3 AND ins.estado = 0
                ORDER BY pun.apto DESC, pro.id
            ", [
                'proceso_id1' => $id_proceso,
                'proceso_id2' => $id_proceso,
                'proceso_id3' => $id_proceso,
            ]);

            // Obtener identidades culturales (solo si se solicita)
            $identidades = [];
            if ($incluirIdentidad && !empty($reporte)) {
                $postulantesIds = array_unique(array_column($reporte, 'POSTULANTE_ID'));
                try {
                    $response = Http::timeout(10)
                        ->post(
                            "https://test-admision.unap.edu.pe/service_identidad/api/v1/identidad-cultural/by-postulantes",
                            [
                                'postulante_ids' => $postulantesIds,
                                'proceso_id' => (int) $id_proceso,
                            ]
                        );
                    $identidades = $response->successful() ? $response->json() : [];
                } catch (\Exception $ex) {
                    Log::warning('Servicio identidad cultural no disponible: ' . $ex->getMessage());
                }
            }

            $mapaIdentidades = [];
            foreach ($identidades as $identidad) {
                $pid = $identidad['id_postulante'] ?? null;
                if ($pid) {
                    $mapaIdentidades[$pid] = $identidad;
                }
            }

            $collection = collect($reporte)->map(function ($row) use ($mapaIdentidades, $incluirIdentidad) {
                $identidad = $mapaIdentidades[$row->POSTULANTE_ID] ?? [];
                $data = [
                    $row->CODIGO_SEDE_FILIAL ?? '',
                    $row->TIPO_PROCESO ?? '',
                    $row->PROCESO_ADMISION ?? '',
                    $row->NUMERO_CONVOCATORIA ?? '',
                    $row->TIPO_DOCUMENTO ?? '',
                    $row->NRO_DOCUMENTO ?? '',
                    $row->NOMBRES ?? '',
                    $row->PRIMER_APELLIDO ?? '',
                    $row->SEGUNDO_APELLIDO ?? '',
                    $row->APELLIDO_CASADA ?? '',
                    $row->SOLO_UN_APELLIDO ?? '',
                    $row->SEXO ?? '',
                    $row->FECHA_NACIMIENTO ?? '',
                    $row->PAIS_NACIMIENTO ?? '',
                    $row->NACIONALIDAD ?? '',
                    $row->UBIGEO_NACIMIENTO ?? '',
                    $row->UBIGEO_DOMICILIO ?? '',
                    $row->CONDICION_DISCAPACIDAD ?? '',
                    $row->TIPO_DISCAPACIDAD ?? '',
                    $row->CELULAR ?? '',
                    $row->CORREO_PERSONAL ?? '',
                    $row->CODIGO_FACULTAD_UNIDAD ?? '',
                    $row->CODIGO_PROGRAMA_OPCIONES ?? '',
                    $row->FECHA_REGISTRO ?? '',
                    $row->PUNTAJE_OBTENIDO ?? '',
                    $row->MODALIDAD_ADMISION ?? '',
                    $row->MODALIDAD_ESTUDIO ?? '',
                    $row->ES_INGRESANTE ?? '',
                    $row->CODIGO_FACULTAD_UNIDAD_INGRESO ?? '',
                    $row->CODIGO_PROGRAMA_INGRESO ?? '',
                    $row->FECHA_INGRESO ?? '',
                    $row->CORREO_INSTITUCIONAL ?? '',
                    $row->CODIGO_ORCID ?? '',
                ];

                if ($incluirIdentidad) {
                    $data[] = $row->VALIDACION_CELULAR ?? '';
                    $data[] = $row->VALIDACION_EMAIL ?? '';
                    $data[] = $identidad['id'] ?? '';
                    $data[] = $identidad['id_pueblo_indigena'] ?? '';
                    $data[] = $identidad['id_lengua_indigena'] ?? '';
                    $data[] = $identidad['id_condicion_lengua'] ?? '';
                    $data[] = $identidad['id_pertenencia_cultural'] ?? '';
                    $data[] = $identidad['created_at'] ?? '';
                    $data[] = $identidad['updated_at'] ?? '';
                }

                return $data;
            });

            $proceso = Proceso::find($id_proceso);
            $nombreArchivo = 'reporte_sunedu_' . str_replace(' ', '_', $proceso->nombre ?? 'proceso') . '.xlsx';

            return Excel::download(new ReporteSuneduExport($collection, $incluirIdentidad), $nombreArchivo);

        } catch (\Exception $e) {
            Log::error('Error al exportar Excel SUNEDU: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al generar el Excel',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reporte de errores de datos del proceso activo
     */
    public function erroresDatos(Request $request)
    {
        $id_proceso = auth()->user()->id_proceso;
        $tipo = $request->input('tipo', '');

        $queries = [
            'celular_vacio' => [
                'titulo' => 'Celular vacío o incompleto',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.celular, 'Celular vacío o incompleto' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.celular IS NULL OR pos.celular = '' OR LENGTH(pos.celular) < 9)",
            ],
            'celular_invalido' => [
                'titulo' => 'Celular con formato inválido',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.celular, 'Celular no tiene 9 dígitos' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.celular IS NOT NULL AND pos.celular != '' AND LENGTH(pos.celular) >= 9
                    AND pos.celular NOT REGEXP '^[0-9]{9}$'",
            ],
            'email_vacio' => [
                'titulo' => 'Correo electrónico vacío',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.email, 'Sin correo electrónico' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.email IS NULL OR pos.email = '')",
            ],
            'email_invalido' => [
                'titulo' => 'Correo electrónico inválido',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.email, 'Formato de correo inválido' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.email IS NOT NULL AND pos.email != ''
                    AND pos.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\\\.[A-Za-z]{2,}$'",
            ],
            'sin_fecha_nacimiento' => [
                'titulo' => 'Sin fecha de nacimiento',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.fec_nacimiento, 'Sin fecha de nacimiento' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.fec_nacimiento IS NULL OR pos.fec_nacimiento < '1900-01-01')",
            ],
            'sin_sexo' => [
                'titulo' => 'Sin sexo registrado',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.sexo, 'Sin sexo' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.sexo IS NULL OR pos.sexo = '' OR pos.sexo = '0')",
            ],
            'sin_ubigeo_nacimiento' => [
                'titulo' => 'Sin ubigeo de nacimiento',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.ubigeo_nacimiento, 'Sin ubigeo de nacimiento' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.ubigeo_nacimiento IS NULL OR pos.ubigeo_nacimiento = '' OR pos.ubigeo_nacimiento = '000000')",
            ],
            'sin_ubigeo_residencia' => [
                'titulo' => 'Sin ubigeo de residencia',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.ubigeo_residencia, 'Sin ubigeo de residencia' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.ubigeo_residencia IS NULL OR pos.ubigeo_residencia = '' OR pos.ubigeo_residencia = '000000')",
            ],
            'sin_pais' => [
                'titulo' => 'Sin país de nacimiento',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.id_pais, 'Sin país asignado' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.id_pais IS NULL OR pos.id_pais = 0)",
            ],
            'sin_apellido' => [
                'titulo' => 'Sin primer apellido',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, 'Sin primer apellido' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.primer_apellido IS NULL OR pos.primer_apellido = '')",
            ],
            'sin_nombres' => [
                'titulo' => 'Sin nombres registrados',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, 'Sin nombres' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.nombres IS NULL OR pos.nombres = '')",
            ],
            'verificacion_reniec' => [
                'titulo' => 'Mayor de edad sin verificación RENIEC vigente',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.revisado, pos.updated_at AS fecha_verificacion, TIMESTAMPDIFF(YEAR, pos.fec_nacimiento, CURDATE()) AS edad, 'Mayor de edad sin verificación RENIEC o verificación mayor a 1 año' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.fec_nacimiento IS NOT NULL AND pos.fec_nacimiento >= '1900-01-01'
                    AND TIMESTAMPDIFF(YEAR, pos.fec_nacimiento, CURDATE()) >= 18
                    AND (
                        pos.revisado IS NULL OR pos.revisado = 0
                        OR (pos.revisado = 1 AND pos.updated_at < DATE_SUB(NOW(), INTERVAL 1 YEAR))
                    )",
            ],
            'sin_puntaje' => [
                'titulo' => 'Inscritos sin puntaje registrado',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, 'Sin registro de puntaje' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    LEFT JOIN puntajes pun ON pun.id_proceso = ins.id_proceso AND pun.dni = pos.nro_doc
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pun.id IS NULL",
            ],
            // ── Inconsistencias de datos ──
            'discapacidad_inconsistente' => [
                'titulo' => 'Tiene tipo de discapacidad pero no marca discapacidad',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.discapacidad, pos.tipo_discapacidad, 'Tiene tipo_discapacidad pero discapacidad=0' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.tipo_discapacidad IS NOT NULL AND pos.tipo_discapacidad != '' AND pos.tipo_discapacidad != '0')
                    AND (pos.discapacidad IS NULL OR pos.discapacidad = 0)",
            ],
            'discapacidad_sin_tipo' => [
                'titulo' => 'Marca discapacidad pero no tiene tipo registrado',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.discapacidad, pos.tipo_discapacidad, 'discapacidad=1 pero sin tipo_discapacidad' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.discapacidad = 1
                    AND (pos.tipo_discapacidad IS NULL OR pos.tipo_discapacidad = '' OR pos.tipo_discapacidad = '0')",
            ],
            'extranjero_con_ubigeo' => [
                'titulo' => 'Extranjero con ubigeo de nacimiento peruano',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.tipo_doc, pos.ubigeo_nacimiento, pais.codigo, 'Extranjero con ubigeo de nacimiento' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    JOIN paises pais ON pais.id = pos.id_pais
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.tipo_doc IN (2,3)
                    AND pos.ubigeo_nacimiento IS NOT NULL AND pos.ubigeo_nacimiento != '' AND pos.ubigeo_nacimiento != '000000'",
            ],
            'peruano_sin_ubigeo_nac' => [
                'titulo' => 'Peruano sin ubigeo de nacimiento',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.tipo_doc, pos.ubigeo_nacimiento, 'Peruano sin ubigeo de nacimiento' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    JOIN paises pais ON pais.id = pos.id_pais
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.tipo_doc = 1
                    AND (pos.ubigeo_nacimiento IS NULL OR pos.ubigeo_nacimiento = '' OR pos.ubigeo_nacimiento = '000000')",
            ],
            'sin_tipo_doc' => [
                'titulo' => 'Sin tipo de documento',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.tipo_doc, 'Sin tipo de documento' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND (pos.tipo_doc IS NULL OR pos.tipo_doc = '' OR pos.tipo_doc = 0)",
            ],
            'menor_edad' => [
                'titulo' => 'Postulante menor de edad',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pos.fec_nacimiento, TIMESTAMPDIFF(YEAR, pos.fec_nacimiento, CURDATE()) AS edad, 'Postulante menor de 18 años' AS error
                    FROM inscripciones ins JOIN postulante pos ON pos.id = ins.id_postulante
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND pos.fec_nacimiento IS NOT NULL AND pos.fec_nacimiento >= '1900-01-01'
                    AND TIMESTAMPDIFF(YEAR, pos.fec_nacimiento, CURDATE()) < 18",
            ],
            'sin_pago' => [
                'titulo' => 'Inscripciones sin pago registrado',
                'sql' => "SELECT pos.nro_doc, pos.nombres, pos.primer_apellido, pos.segundo_apellido, pro.nombre AS programa, 'Inscripción sin pago asociado' AS error
                    FROM inscripciones ins
                    JOIN postulante pos ON pos.id = ins.id_postulante
                    JOIN programa pro ON pro.id = ins.id_programa
                    WHERE ins.id_proceso = ? AND ins.estado = 0
                    AND NOT EXISTS (
                        SELECT 1 FROM pagos_general pg
                        WHERE pg.dni = pos.nro_doc COLLATE utf8mb4_0900_ai_ci
                        AND pg.proceso = ins.id_proceso
                    )",
            ],
        ];

        try {
            // Si se pide un tipo específico, devolver el detalle
            if ($tipo && isset($queries[$tipo])) {
                $q = $queries[$tipo];
                $datos = DB::select($q['sql'], [$id_proceso]);
                return response()->json([
                    'estado' => true,
                    'titulo' => $q['titulo'],
                    'datos' => $datos,
                    'total' => count($datos),
                ]);
            }

            // Devolver resumen de todos los errores
            $resumen = [];
            $totalErrores = 0;
            foreach ($queries as $key => $q) {
                $count = DB::select("SELECT COUNT(*) AS cant FROM (" . $q['sql'] . ") AS sub", [$id_proceso]);
                $cantidad = $count[0]->cant ?? 0;
                $resumen[] = [
                    'key' => $key,
                    'titulo' => $q['titulo'],
                    'cantidad' => $cantidad,
                ];
                $totalErrores += $cantidad;
            }

            return response()->json([
                'estado' => true,
                'resumen' => $resumen,
                'total_errores' => $totalErrores,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en erroresDatos: ' . $e->getMessage());
            return response()->json([
                'estado' => false,
                'mensaje' => $e->getMessage(),
            ], 500);
        }
    }

    public function buscarPagosDNI($dni)
    {
        $pagos = [];

        // Banco
        $bancoPagos = DB::select("
            SELECT bp.secuencia AS operacion, bp.fch_pag AS fecha, bp.imp_pag AS monto
            FROM banco_pagos bp
            WHERE bp.fch_pag > '2025-12-01'
            AND bp.concepto IN ('00000026', '00000039', '00000028', '00000027')
            AND substr(bp.num_doc, 8, 8) = ?
            AND NOT EXISTS (
                SELECT 1 FROM pagos_general pg WHERE pg.operacion = bp.secuencia
            )
        ", [$dni]);

        foreach ($bancoPagos as $bp) {
            $pagos[] = [
                'origen' => 'Banco',
                'operacion' => $bp->operacion,
                'fecha' => $bp->fecha,
                'monto' => $bp->monto,
            ];
        }

        // Caja
        try {
            $response = Http::get('http://tesoreria.unap.edu.pe/services/document/?w=' . $dni . '&d=2025-12-01');
            if ($response->successful()) {
                $datosCaja = $response->json('data');
                if (is_array($datosCaja)) {
                    $pagadosCaja = DB::table('pagos_general')
                        ->where('dni', $dni)
                        ->where('medio', 'Caja')
                        ->pluck('operacion')
                        ->toArray();
                    foreach ($datosCaja as $item) {
                        $operacion = $item['paymentTitle'] ?? null;
                        if ($operacion && !in_array($operacion, $pagadosCaja)) {
                            $pagos[] = [
                                'origen' => 'Caja',
                                'operacion' => $operacion,
                                'fecha' => $item['paymentDatetime'] ?? null,
                                'monto' => $item['paymentAmount'] ?? null,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silenciar
        }

        return response()->json(['estado' => true, 'pagos' => $pagos]);
    }

}
