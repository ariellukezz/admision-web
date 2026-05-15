<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Documento;
use App\Models\Paso;
use App\Models\Cambio;
use App\Services\DocumentoStorageService;
use Illuminate\Support\Str;

class DocumentoController extends Controller
{
    private DocumentoStorageService $storageService;

    public function __construct(DocumentoStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function getCertificadosRevision(Request $request)
    {
        $query_where = [];

        $res = Documento::select(
            'documento.id as id',
            'documento.codigo as cod',
            'tipo_documento.nombre as tipo_certificado',
            'documento.verificado',
            'documento.url',
            'documento.observacion as tipo',
            'postulante.nro_doc as dni',
            'postulante.primer_apellido AS paterno',
            'postulante.segundo_apellido AS materno',
            'postulante.nombres'
        )
            ->leftjoin('tipo_documento', 'documento.id_tipo_documento', 'tipo_documento.id')
            ->leftjoin('postulante', 'postulante.id', 'documento.id_postulante')
            ->where($query_where)
            ->where('tipo_documento.id', '=', 1)
            ->where(function ($query) use ($request) {
                return $query
                    ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.primer_apellido', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.segundo_apellido', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('tipo_documento.nombre', 'LIKE', '%' . $request->term . '%');
            })->paginate($request->paginasize);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function cambiarEstado(Request $request)
    {
        $documento = Documento::find($request->id);
        if (!$documento) {
            $this->response['titulo'] = '!ERROR!';
            $this->response['mensaje'] = 'Documento no encontrado';
            $this->response['estado'] = false;
            return response()->json($this->response, 404);
        }
        $copy = $documento;
        $this->cambio('documentos', 'verificado', $copy->verificado, $request->estado, $documento->id);
        $documento->verificado = $request->estado;
        $documento->id_usuario = auth()->id();
        $documento->save();

        $this->response['titulo'] = '!CERTIFICADO ACTUALIZADO!';
        $this->response['mensaje'] = 'Estado Cambiado con exito';
        $this->response['estado'] = true;
        $this->response['datos'] = $documento;
        return response()->json($this->response, 200);
    }

    public function cambiarCodigo(Request $request)
    {
        $documento = Documento::find($request->id);
        $documento->codigo = $request->codigo;
        $documento->save();

        $this->response['titulo'] = '!CERTIFICADO ACTUALIZADO!';
        $this->response['mensaje'] = 'Estado Cambiado con exito';
        $this->response['estado'] = true;
        $this->response['datos'] = $documento;
        return response()->json($this->response, 200);
    }

    public function getDocumentosAdmin(Request $request)
    {
        $query_where = [];
        $res = Documento::select(
            'documento.id',
            'documento.codigo',
            'documento.nombre',
            'postulante.nro_doc as dni',
            DB::raw('CONCAT(postulante.nombres," ",postulante.primer_apellido," ",postulante.segundo_apellido) as postulante'),
            'documento.estado',
            'documento.verificado',
            'tipo_documento.nombre as tipo',
            'documento.observacion',
            'documento.extension',
            'documento.mime',
            'documento.size',
            'documento.hash',
        )
            ->join('postulante', 'postulante.id', 'documento.id_postulante')
            ->leftjoin('tipo_documento', 'documento.id_tipo_documento', 'tipo_documento.id')
            ->where($query_where)
            ->where(function ($query) use ($request) {
                return $query
                    ->orWhere('postulante.nro_doc', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('postulante.nombres', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('documento.codigo', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('documento.nombre', 'LIKE', '%' . $request->term . '%');
            })
            ->paginate(20);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function saveDocumentoAdmin(Request $request)
    {
        $documento = null;
        if (!$request->id) {
            $documento = Documento::create([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'id_usuario' => auth()->id()
            ]);
            $this->response['titulo'] = 'REGISTRO NUEVO';
            $this->response['mensaje'] = 'Documento ' . $documento->nombre . ' CREADA CON EXITO';
            $this->response['estado'] = true;
            $this->response['datos'] = $documento;
        } else {
            $documento = Documento::find($request->id);
            $documento->nombre = $request->nombre;
            $documento->codigo = $request->codigo;
            $documento->observacion = $request->observacion;
            $documento->id_usuario = auth()->id();
            $documento->save();

            $this->response['titulo'] = '!REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'DOCUMENTO ' . $documento->nombre . ' MODIFICADO';
            $this->response['estado'] = true;
            $this->response['datos'] = $documento;
        }

        return response()->json($this->response, 200);
    }

    public function downloadDocumento(int $id)
    {
        try {
            return $this->storageService->downloadFile($id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    public function verifyDocumento(Request $request, int $id)
    {
        $request->validate([
            'verificado' => 'required|integer',
        ]);

        try {
            $documento = $this->storageService->verifyFile($id, $request->verificado);

            return response()->json([
                'success' => true,
                'mensaje' => 'Estado de verificación actualizado',
                'datos' => $documento,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    public function updateDocumento(Request $request, int $id)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        try {
            $documento = $this->storageService->updateFile($id, $request->file('archivo'), auth()->id());

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

    public function deleteDocumento(int $id)
    {
        try {
            $result = $this->storageService->deleteFile($id, auth()->id());

            return response()->json([
                'success' => true,
                'mensaje' => $result['message'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'mensaje' => $e->getMessage()], 404);
        }
    }

    private function cambio($tabla, $campo, $anterior, $nuevo, $id_registro)
    {
        $cambio = Cambio::create([
            'tabla' => $tabla,
            'campo' => $campo,
            'valor_anterior' => $anterior,
            'valor_nuevo' => $nuevo,
            'id_registro' => $id_registro,
            'id_usuario' => auth()->id()
        ]);
    }

    public function getCodigoDNI($dni)
    {
        $fechas = ['2024-03-25', '2024-03-26', '2024-03-27', '2024-03-28', '2024-04-01', '2024-04-02'];

        $res = Documento::select('documento.codigo as label', 'documento.codigo as value')
            ->join('postulante', 'postulante.id', '=', 'documento.id_postulante')
            ->whereIn(DB::raw('DATE(documento.created_at)'), $fechas)
            ->where('postulante.nro_doc', $dni)
            ->distinct()
            ->get();

        if (count($res) == 0) {
            $this->response['estado'] = false;
        } else {
            $this->response['estado'] = true;
            $this->response['datos'] = $res;
        }

        return response()->json($this->response, 200);
    }

    public function descargarReglamento($id_proceso)
    {
        $res = DB::select("SELECT reg.url FROM reglamento reg
        JOIN procesos pro ON reg.id = pro.id_reglamento
        WHERE pro.id = $id_proceso");

        if (count($res) > 0) {
            $archivo = public_path($res[0]->url);
            return response()->download($archivo);
        } else {
            $this->response['estado'] = false;
            $this->response['message'] = "Reglamento no encontrado";
            return response()->json($this->response, 200);
        }
    }
}
