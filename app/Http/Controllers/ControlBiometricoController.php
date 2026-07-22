<?php

namespace App\Http\Controllers;
use App\Models\ControlBiometrico;
use App\Models\Proceso;
use App\Exports\ControlBiometricoExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Excel;
use Mpdf\Mpdf;
class ControlBiometricoController extends Controller
{

    public function getControlPosterior(Request $request) {

        $procesoId = auth()->user()->id_proceso;
        $pageSize = 50; 

        $result = ControlBiometrico::select('control_biometrico.id', 'control_biometrico.codigo_ingreso', 'control_biometrico.estado',
            'control_biometrico.correo_institucional', 'control_biometrico.tiene_correo', 'control_biometrico.segunda_carrera',
            'postulante.nro_doc', 'postulante.primer_apellido',
            'postulante.segundo_apellido', 'postulante.nombres', 'programa.nombre_corto AS programa', 
            'programa.nombre as programa_estudio', 'modalidad.nombre AS modalidad',
            'puntajes.puntaje AS puntaje', 'puntajes.puntaje_vocacional', 
            DB::raw('COALESCE(puntajes.puntaje_vocacional, 0) AS puntaje_vocacional'),
            DB::raw('(puntajes.puntaje + COALESCE(puntajes.puntaje_vocacional, 0)) AS puntaje_total'),
            DB::raw("CONCAT('/documentos/',".$procesoId.",'/control_biometrico/constancias/',postulante.nro_doc,'.pdf') AS url"))
            ->join('postulante', 'postulante.id', '=', 'control_biometrico.id_postulante')
            ->join('inscripciones', function ($join) use ($procesoId) {
                $join->on('inscripciones.id_postulante', '=', 'postulante.id')
                    ->where('inscripciones.id_proceso', '=', $procesoId)
                    ->where('inscripciones.estado', '=', 0);
            })
            ->join('programa', 'programa.id', '=', 'inscripciones.id_programa')
            ->join('modalidad', 'modalidad.id', '=', 'inscripciones.id_modalidad')
            ->join('puntajes', function ($join) use ($procesoId) {
                $join->on('puntajes.dni', '=', 'postulante.nro_doc')
                    ->where('puntajes.id_proceso', '=', $procesoId)
                    ->where('puntajes.apto', '=', 'SI');
            })
            ->where('control_biometrico.id_proceso', '=', $procesoId)
            ->where(function ($query) use ($request) {
                $query->where('postulante.nro_doc', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.primer_apellido', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.segundo_apellido', 'LIKE', "%$request->term%")
                    ->orWhere('postulante.nombres', 'LIKE', "%$request->term%")
                    ->orWhere('programa.nombre', 'LIKE', "%$request->term%")
                    ->orWhere(DB::raw("CONCAT(postulante.primer_apellido, ' ', postulante.segundo_apellido, ' ', postulante.nombres)"), 'LIKE', "%$request->term%")
                    ->orWhere(DB::raw("CONCAT(postulante.nombres,' ',postulante.primer_apellido, ' ', postulante.segundo_apellido)"), 'LIKE', "%$request->term%")
                    ->orWhere('modalidad.nombre', 'LIKE', "%$request->term%");
            })
            ->when($request->filled('fecha'), function ($q) use ($request) {
                $q->whereDate('control_biometrico.created_at', $request->fecha);
            })
            ->when($request->filled('fecha_inicio') && $request->filled('fecha_fin'), function ($q) use ($request) {
                $q->whereBetween('control_biometrico.created_at', [$request->fecha_inicio . ' 00:00:00', $request->fecha_fin . ' 23:59:59']);
            })
            ->when($request->filled('programa'), function ($q) use ($request) {
                $q->where('programa.id', $request->programa);
            })
            ->when($request->filled('modalidad'), function ($q) use ($request) {
                $q->where('modalidad.id', $request->modalidad);
            })
            ->when($request->filled('area'), function ($q) use ($request) {
                $q->where('programa.area', $request->area);
            })
            ->orderBy('programa.nombre', 'ASC')
            ->orderBy(DB::raw('(puntajes.puntaje + puntajes.puntaje_vocacional)'), 'DESC')
            ->paginate($pageSize);
    
        $response = [ 'estado' => true, 'datos' => $result];
        return response()->json($response, 200);

    }

    /**
     * Construye la consulta base de control biométrico con filtros.
     */
    private function construirConsultaExport($procesoId, $filtros)
    {
        $query = DB::table('control_biometrico AS cb')
            ->join('postulante AS pos', function ($join) use ($procesoId) {
                $join->on('pos.id', '=', 'cb.id_postulante')
                    ->where('cb.id_proceso', '=', $procesoId);
            })
            ->join('inscripciones AS ins', function ($join) use ($procesoId) {
                $join->on('ins.id_postulante', '=', 'pos.id')
                    ->where('ins.id_proceso', '=', $procesoId)
                    ->where('ins.estado', '=', 0);
            })
            ->join('programa AS pro', 'pro.id', '=', 'ins.id_programa')
            ->join('modalidad AS moda', 'moda.id', '=', 'ins.id_modalidad')
            ->join('procesos AS proc', 'proc.id', '=', 'ins.id_proceso')
            ->join('resultados AS re', function ($join) use ($procesoId) {
                $join->on('re.dni_postulante', '=', 'pos.nro_doc')
                    ->where('re.id_proceso', '=', $procesoId)
                    ->where('re.apto', '=', 'SI');
            })
            ->leftJoin('ubigeo AS ubi', 'ubi.ubigeo', '=', 'pos.ubigeo_nacimiento')
            ->leftJoin('departamento AS dep', 'dep.id', '=', 'ubi.id_departamento')
            ->leftJoin('provincia AS prov', 'prov.id', '=', 'ubi.id_provincia')
            ->leftJoin('distritos AS dist', 'dist.id', '=', 'ubi.id_distrito')
            ->leftJoin('colegios AS col', 'col.id', '=', 'pos.id_colegio')
            ->select(
                'cb.codigo_ingreso AS codigo',
                'pos.nro_doc AS dni',
                'pos.primer_apellido',
                'pos.segundo_apellido',
                'pos.nombres',
                're.puntaje',
                're.puesto',
                'pro.nombre AS programa',
                'pro.area',
                'moda.nombre AS modalidad',
                'proc.nombre AS proceso'
            )
            ->where('ins.id_proceso', '=', $procesoId);

        if (!empty($filtros['fecha'])) {
            $query->whereDate('cb.created_at', $filtros['fecha']);
        }

        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query->whereBetween('cb.created_at', [$filtros['fecha_inicio'] . ' 00:00:00', $filtros['fecha_fin'] . ' 23:59:59']);
        }

        if (!empty($filtros['programa'])) {
            $query->where('pro.id', $filtros['programa']);
        }

        if (!empty($filtros['modalidad'])) {
            $query->where('moda.id', $filtros['modalidad']);
        }

        if (!empty($filtros['area'])) {
            $query->where('pro.area', $filtros['area']);
        }

        $query->orderBy('pro.nombre', 'ASC')->orderBy('re.puesto', 'ASC');

        return $query;
    }

    /**
     * Exportar a Excel con filtros.
     */
    public function exportarExcel(Request $request)
    {
        $procesoId = auth()->user()->id_proceso;

        $filtros = [
            'fecha' => $request->input('fecha'),
            'fecha_inicio' => $request->input('fecha_inicio'),
            'fecha_fin' => $request->input('fecha_fin'),
            'programa' => $request->input('programa'),
            'modalidad' => $request->input('modalidad'),
            'area' => $request->input('area'),
        ];

        $datos = $this->construirConsultaExport($procesoId, $filtros)->get();

        $collection = collect($datos)->map(function ($item) {
            return [
                'codigo' => $item->codigo,
                'dni' => $item->dni,
                'primer_apellido' => $item->primer_apellido,
                'segundo_apellido' => $item->segundo_apellido,
                'nombres' => $item->nombres,
                'puntaje' => $item->puntaje,
                'puesto' => $item->puesto,
                'programa' => $item->programa,
                'modalidad' => $item->modalidad,
                'area' => $item->area,
                'proceso' => $item->proceso,
            ];
        });

        $proceso = Proceso::find($procesoId);
        $nombreArchivo = 'control_biometrico_' . str_replace(' ', '_', $proceso->nombre ?? 'proceso') . '.xlsx';

        return Excel::download(new ControlBiometricoExport($collection), $nombreArchivo);
    }

    /**
     * Exportar a PDF con filtros, una página por cada programa.
     */
public function exportarPdf(Request $request)
{
    $procesoId = auth()->user()->id_proceso;

    $filtros = [
        'fecha'         => $request->input('fecha'),
        'fecha_inicio'  => $request->input('fecha_inicio'),
        'fecha_fin'     => $request->input('fecha_fin'),
        'programa'      => $request->input('programa'),
        'modalidad'     => $request->input('modalidad'),
        'area'          => $request->input('area'),
    ];

    $datos = $this->construirConsultaExport($procesoId, $filtros)->get();

    $agrupado = collect($datos)->groupBy('programa');

    $proceso = Proceso::find($procesoId);

    $filtrosTexto = [];

    if (!empty($filtros['fecha'])) {
        $filtrosTexto[] = 'Fecha: ' . $filtros['fecha'];
    }

    if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
        $filtrosTexto[] = 'Desde: ' . $filtros['fecha_inicio'] . ' hasta ' . $filtros['fecha_fin'];
    }

    if (!empty($filtros['programa'])) {
        $filtrosTexto[] = 'Programa: ' . ($agrupado->keys()->first() ?? '');
    }

    if (!empty($filtros['modalidad'])) {
        $modalidad = DB::table('modalidad')
            ->where('id', $filtros['modalidad'])
            ->value('nombre');

        $filtrosTexto[] = 'Modalidad: ' . ($modalidad ?? $filtros['modalidad']);
    }

    if (empty($filtrosTexto)) {
        $filtrosTexto[] = 'Todos los registros';
    }

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',
        'default_font' => 'dejavusanscondensed',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 38,
        'margin_bottom' => 18,
        'margin_header' => 8,
        'margin_footer' => 8,
    ]);

    $mpdf->SetTitle('Control Biométrico - ' . ($proceso->nombre ?? ''));
    $mpdf->SetAuthor('Sistema de Admisión UNAP');
    $mpdf->SetDisplayMode('fullpage');

    $headerHtml = View::make('Reportes.control_biometrico_header', compact('proceso', 'filtrosTexto'))->render();
    $footerHtml = View::make('Reportes.control_biometrico_footer')->render();
    $mpdf->SetHTMLHeader($headerHtml);
    $mpdf->SetHTMLFooter($footerHtml);

    foreach ($agrupado as $programa => $postulantes) {
        $mpdf->AddPage();
        $chunk = View::make('Reportes.control_biometrico_item', compact('programa', 'postulantes'))->render();
        $mpdf->WriteHTML($chunk);
    }

    $filename = 'control_biometrico_' . now()->format('YmdHis') . '.pdf';

    return response(
        $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]
    );
}

    /**
     * Actualizar un registro de control biométrico.
     */
    public function actualizar(Request $request, $id)
    {
        $registro = ControlBiometrico::findOrFail($id);

        $validated = $request->validate([
            'codigo_ingreso'     => 'nullable|string|max:8',
            'estado'             => 'nullable|in:0,1',
            'correo_institucional' => 'nullable|string|max:150',
            'tiene_correo'       => 'nullable|in:0,1',
            'segunda_carrera'    => 'nullable|integer',
        ]);

        $registro->update($validated);

        return response()->json([
            'estado' => true,
            'mensaje' => 'Registro actualizado correctamente',
            'datos' => $registro
        ], 200);
    }

    /**
     * Listado de programas para el filtro (API).
     */
    public function getProgramas(Request $request)
    {
        $procesoId = auth()->user()->id_proceso;

        $programas = DB::table('programa AS pro')
            ->join('inscripciones AS ins', function ($join) use ($procesoId) {
                $join->on('ins.id_programa', '=', 'pro.id')
                    ->where('ins.id_proceso', '=', $procesoId)
                    ->where('ins.estado', '=', 0);
            })
            ->select('pro.id', 'pro.nombre', 'pro.area')
            ->distinct()
            ->orderBy('pro.nombre', 'ASC')
            ->get();

        return response()->json(['estado' => true, 'datos' => $programas], 200);
    }

    /**
     * Listado de modalidades para el filtro (API).
     */
    public function getModalidades(Request $request)
    {
        $procesoId = auth()->user()->id_proceso;

        $modalidades = DB::table('modalidad AS m')
            ->join('inscripciones AS ins', function ($join) use ($procesoId) {
                $join->on('ins.id_modalidad', '=', 'm.id')
                    ->where('ins.id_proceso', '=', $procesoId)
                    ->where('ins.estado', '=', 0);
            })
            ->select('m.id', 'm.nombre')
            ->distinct()
            ->orderBy('m.nombre', 'ASC')
            ->get();

        return response()->json(['estado' => true, 'datos' => $modalidades], 200);
    }

}
