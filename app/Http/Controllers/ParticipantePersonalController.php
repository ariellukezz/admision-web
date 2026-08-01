<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ParticipantePersonal;
use App\Models\TipoPersonal;
use Illuminate\Support\Facades\Storage;

class ParticipantePersonalController extends Controller
{
    public function index()
    {
        $tipos = TipoPersonal::select('id as value', 'nombre as label')->orderBy('nombre')->get();

        return Inertia::render('Participantes/index', [
            'tipos' => $tipos,
        ]);
    }

    public function getParticipantes(Request $request)
    {
        $perPage = $request->input('pageSize', 50);

        $res = ParticipantePersonal::select(
            'participantes_personales.id',
            'participantes_personales.dni',
            'participantes_personales.nombres',
            'participantes_personales.paterno',
            'participantes_personales.materno',
            'participantes_personales.codigo_trabajador',
            'participantes_personales.condicion',
            'participantes_personales.dependencia',
            'participantes_personales.foto',
            'participantes_personales.estado',
            'participantes_personales.id_tipo_personal',
            'tipo_personal.nombre as tipo_personal'
        )
            ->leftJoin('tipo_personal', 'tipo_personal.id', '=', 'participantes_personales.id_tipo_personal')
            ->when($request->term, function ($q, $term) {
                return $q->where(function ($query) use ($term) {
                    $query->orWhere('participantes_personales.dni', 'LIKE', '%' . $term . '%')
                          ->orWhere('participantes_personales.nombres', 'LIKE', '%' . $term . '%')
                          ->orWhere('participantes_personales.paterno', 'LIKE', '%' . $term . '%')
                          ->orWhere('participantes_personales.materno', 'LIKE', '%' . $term . '%')
                          ->orWhere('participantes_personales.codigo_trabajador', 'LIKE', '%' . $term . '%')
                          ->orWhere('tipo_personal.nombre', 'LIKE', '%' . $term . '%');
                });
            })
            ->when($request->filled('id_tipo_personal'), function ($q) use ($request) {
                return $q->where('participantes_personales.id_tipo_personal', $request->id_tipo_personal);
            })
            ->when($request->filled('condicion'), function ($q) use ($request) {
                return $q->where('participantes_personales.condicion', $request->condicion);
            })
            ->orderBy('participantes_personales.id', 'DESC')
            ->paginate($perPage);

        $this->response['estado'] = true;
        $this->response['datos'] = $res;
        return response()->json($this->response, 200);
    }

    public function buscarReniec($dni)
    {
        $reniec = app(ReniecController::class)->consultarReniecPorDni($dni);

        if (!$reniec) {
            return response()->json(['estado' => false, 'mensaje' => 'No se pudo obtener datos de RENIEC.'], 200);
        }

        $fotoPath = null;
        if (!empty($reniec['foto_base64'])) {
            $imageData = base64_decode($reniec['foto_base64']);
            if ($imageData !== false) {
                $filename  = $dni . '.jpg';
                $fotoPath  = 'participantes/fotos/' . $filename;
                Storage::disk('public')->put($fotoPath, $imageData);
                $fotoPath  = '/storage/' . $fotoPath;
            }
        }

        return response()->json([
            'estado'     => true,
            'nombres'    => $reniec['nombres'],
            'paterno'    => $reniec['ap_paterno'],
            'materno'    => $reniec['ap_materno'],
            'foto'       => $fotoPath,
        ], 200);
    }

    public function subirFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $file     = $request->file('foto');
        $dni      = $request->input('dni', 'temp');
        $filename = $dni . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('participantes/fotos', $filename, 'public');

        $fotoPath = '/storage/' . $path;

        return response()->json([
            'estado' => true,
            'foto'   => $fotoPath,
        ], 200);
    }

    public function saveParticipante(Request $request)
    {
        $request->validate([
            'dni'               => 'required|string|max:12',
            'nombres'           => 'required|string|max:255',
            'paterno'           => 'nullable|string|max:255',
            'materno'           => 'nullable|string|max:255',
            'id_tipo_personal'  => 'nullable|integer',
            'codigo_trabajador' => 'nullable|string|max:50',
            'condicion'         => 'nullable|string|max:100',
            'dependencia'       => 'nullable|string|max:255',
            'foto'              => 'nullable|string',
            'estado'            => 'nullable|boolean',
        ]);

        if (!$request->id) {
            $participante = ParticipantePersonal::create([
                'dni'               => $request->dni,
                'nombres'           => $request->nombres,
                'paterno'           => $request->paterno,
                'materno'           => $request->materno,
                'id_tipo_personal'  => $request->id_tipo_personal,
                'codigo_trabajador' => $request->codigo_trabajador,
                'condicion'         => $request->condicion,
                'dependencia'       => $request->dependencia,
                'foto'              => $request->foto,
                'estado'            => $request->estado ? 1 : 0,
                'id_usuario'        => auth()->id(),
            ]);

            $this->response['titulo']  = 'REGISTRO CREADO';
            $this->response['mensaje'] = 'Participante "' . $participante->nombres . '" creado con éxito';
        } else {
            $participante = ParticipantePersonal::findOrFail($request->id);

            if ($participante->foto && $request->foto && $participante->foto !== $request->foto) {
                $oldPath = str_replace('/storage/', '', $participante->foto);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $participante->dni               = $request->dni;
            $participante->nombres           = $request->nombres;
            $participante->paterno           = $request->paterno;
            $participante->materno           = $request->materno;
            $participante->id_tipo_personal  = $request->id_tipo_personal;
            $participante->codigo_trabajador = $request->codigo_trabajador;
            $participante->condicion         = $request->condicion;
            $participante->dependencia       = $request->dependencia;
            $participante->foto              = $request->foto;
            $participante->estado            = $request->estado ? 1 : 0;
            $participante->id_usuario        = auth()->id();
            $participante->save();

            $this->response['titulo']  = '¡REGISTRO MODIFICADO!';
            $this->response['mensaje'] = 'Participante "' . $participante->nombres . '" actualizado con éxito';
        }

        $this->response['estado'] = true;
        $this->response['datos']  = $participante;
        return response()->json($this->response, 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $participante = ParticipantePersonal::findOrFail($id);
        $participante->estado = $request->estado ? 1 : 0;
        $participante->save();

        $this->response['estado']  = true;
        $this->response['titulo']  = 'ESTADO ACTUALIZADO';
        $this->response['mensaje'] = 'Participante ' . $participante->nombres . ' ' . ($participante->estado ? 'activado' : 'desactivado');
        return response()->json($this->response, 200);
    }

    public function deleteParticipante($id)
    {
        $participante = ParticipantePersonal::findOrFail($id);

        if ($participante->foto) {
            $path = str_replace('/storage/', '', $participante->foto);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $nombre = $participante->nombres;
        $participante->delete();

        $this->response['titulo']  = '!REGISTRO ELIMINADO!';
        $this->response['mensaje'] = 'Participante "' . $nombre . '" eliminado con éxito';
        $this->response['estado']  = true;
        return response()->json($this->response, 200);
    }
}
