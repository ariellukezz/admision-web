<?php

namespace App\Http\Controllers;

use App\Models\Puntaje;
use App\Models\Proceso;
use App\Models\Programa;
use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;
use App\Exports\PuntajePlantillaExport;
use App\Exports\PuntajeExport;
use App\Imports\PuntajesImport;

class GestorPuntajeController extends Controller
{
    /**
     * Construye la consulta base de puntajes con filtros.
     */
    private function construirConsulta(Request $request)
    {
        $query = Puntaje::query();

        if ($request->filled('id_proceso')) {
            $query->where('id_proceso', $request->id_proceso);
        }
        if ($request->filled('programa')) {
            $query->where('programa', $request->programa);
        }
        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('dni', 'LIKE', "%{$s}%")
                  ->orWhere('paterno', 'LIKE', "%{$s}%")
                  ->orWhere('nombres', 'LIKE', "%{$s}%");
            });
        }

        return $query->orderByDesc('puntaje');
    }

    public function index(Request $request)
    {
        $resultados = $this->construirConsulta($request)->paginate(50);

        return response()->json([
            'estado' => true,
            'datos' => $resultados,
        ]);
    }

    /**
     * Exportar a Excel con filtros.
     */
    public function exportarExcel(Request $request)
    {
        $datos = $this->construirConsulta($request)->get();

        $collection = $datos->map(function ($item) {
            return [
                'dni'                 => $item->dni,
                'paterno'             => $item->paterno,
                'materno'             => $item->materno,
                'nombres'             => $item->nombres,
                'programa'            => $item->programa,
                'modalidad'           => $item->modalidad,
                'area'                => $item->area,
                'puntaje'             => $item->puntaje,
                'puntaje_vocacional'  => $item->puntaje_vocacional,
                'apto'                => $item->apto,
                'puesto'              => $item->puesto,
                'fecha'               => $item->fecha,
            ];
        });

        $nombreProceso = '';
        if ($request->filled('id_proceso')) {
            $nombreProceso = Proceso::find($request->id_proceso)?->nombre ?? '';
        }

        $nombreArchivo = 'puntajes_' . str_replace(' ', '_', $nombreProceso ?: 'export') . '.xlsx';

        return Excel::download(new PuntajeExport($collection), $nombreArchivo);
    }

    /**
     * Exportar a PDF con filtros, una página por cada programa.
     */
    public function exportarPdf(Request $request)
    {
        $datos = $this->construirConsulta($request)->get();
        $agrupado = $datos->groupBy('programa');

        $proceso = $request->filled('id_proceso') ? Proceso::find($request->id_proceso) : null;

        $filtrosTexto = [];
        if ($proceso) {
            $filtrosTexto[] = 'Proceso: ' . $proceso->nombre;
        }
        if ($request->filled('programa')) {
            $filtrosTexto[] = 'Programa: ' . $request->programa;
        }
        if ($request->filled('modalidad')) {
            $filtrosTexto[] = 'Modalidad: ' . $request->modalidad;
        }
        if (empty($filtrosTexto)) {
            $filtrosTexto[] = 'Todos los registros';
        }

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'orientation'    => 'P',
            'default_font'   => 'dejavusanscondensed',
            'margin_left'    => 10,
            'margin_right'   => 10,
            'margin_top'     => 38,
            'margin_bottom'  => 18,
            'margin_header'  => 8,
            'margin_footer'  => 8,
        ]);

        $mpdf->SetTitle('Puntajes - ' . ($proceso->nombre ?? ''));
        $mpdf->SetAuthor('Sistema de Admisión UNAP');
        $mpdf->SetDisplayMode('fullpage');

        $headerHtml = View::make('Reportes.puntaje_header', compact('proceso', 'filtrosTexto'))->render();
        $footerHtml = View::make('Reportes.puntaje_footer')->render();
        $mpdf->SetHTMLHeader($headerHtml);
        $mpdf->SetHTMLFooter($footerHtml);

        $esPrimero = true;
        foreach ($agrupado as $programa => $postulantes) {
            if (!$esPrimero) {
                $mpdf->AddPage();
            }
            $esPrimero = false;
            $chunk = View::make('Reportes.puntaje_item', compact('programa', 'postulantes'))->render();
            $mpdf->WriteHTML($chunk);
        }

        $filename = 'puntajes_' . now()->format('YmdHis') . '.pdf';

        return response(
            $mpdf->Output($filename, \Mpdf\Output\Destination::STRING_RETURN),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }

    public function getSelectores()
    {
        $procesos = Proceso::select('id', 'nombre')->orderByDesc('id')->get();

        $programas = Puntaje::whereNotNull('programa')
            ->where('programa', '!=', '')
            ->select('programa')
            ->distinct()
            ->orderBy('programa')
            ->pluck('programa');

        $modalidades = Puntaje::whereNotNull('modalidad')
            ->where('modalidad', '!=', '')
            ->select('modalidad')
            ->distinct()
            ->orderBy('modalidad')
            ->pluck('modalidad');

        return response()->json([
            'estado' => true,
            'procesos' => $procesos,
            'programas' => $programas->map(fn($p) => ['id' => $p, 'nombre' => $p]),
            'modalidades' => $modalidades->map(fn($m) => ['id' => $m, 'nombre' => $m]),
        ]);
    }

    public function importar(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'id_proceso' => 'required|integer',
        ]);

        try {
            $import = new PuntajesImport($request->id_proceso);
            Excel::import($import, $request->file('file'));

            return response()->json([
                'estado' => true,
                'mensaje' => "Importación completada. Procesados: {$import->getTotal()}. Nuevos: {$import->getInsertados()}. Actualizados: {$import->getActualizados()}.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'estado' => false,
                'mensaje' => 'Error al importar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function plantilla()
    {
        return Excel::download(new PuntajePlantillaExport(), 'plantilla_puntajes.xlsx');
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'dni'             => 'required|string',
            'id_proceso'      => 'required|integer',
            'fecha'           => 'nullable|date',
            'paterno'         => 'nullable|string',
            'materno'         => 'nullable|string',
            'nombres'         => 'nullable|string',
            'puntaje'         => 'nullable|numeric',
            'puntaje_vocacional' => 'nullable|numeric',
            'apto'            => 'nullable|string',
            'programa'        => 'nullable|string',
            'area'            => 'nullable|string',
            'modalidad'       => 'nullable|string',
            'id_inscripcion'  => 'nullable|integer',
            'puesto'          => 'nullable|integer',
        ]);

        $data = $request->only([
            'dni', 'id_proceso', 'fecha', 'paterno', 'materno', 'nombres',
            'puntaje', 'puntaje_vocacional', 'apto', 'programa', 'area',
            'modalidad', 'id_inscripcion', 'puesto',
        ]);

        if ($request->filled('id')) {
            $registro = Puntaje::find($request->id);
            if (!$registro) {
                return response()->json(['estado' => false, 'mensaje' => 'Registro no encontrado'], 404);
            }
            $registro->update($data);
            return response()->json(['estado' => true, 'mensaje' => 'Registro actualizado correctamente']);
        }

        Puntaje::create($data);
        return response()->json(['estado' => true, 'mensaje' => 'Registro creado correctamente']);
    }

    public function eliminarRegistro(Request $request)
    {
        $request->validate(['id' => 'required|integer']);

        Puntaje::where('id', $request->id)->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => 'Registro eliminado correctamente.',
        ]);
    }

    public function eliminarTodo(Request $request)
    {
        $request->validate(['id_proceso' => 'required|integer']);

        $eliminados = Puntaje::where('id_proceso', $request->id_proceso)->delete();

        return response()->json([
            'estado' => true,
            'mensaje' => "Se eliminaron {$eliminados} registros del proceso seleccionado.",
        ]);
    }
}
