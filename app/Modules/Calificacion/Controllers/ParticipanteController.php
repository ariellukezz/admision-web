<?php

namespace App\Modules\Calificacion\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ParticipanteController extends BaseCalificacionController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_calificacion' => 'required|integer',
        ]);

        $participantes = DB::table('grupos_filtro as g')
            ->join('inscripciones as i', 'i.grupo_filtro_id', '=', 'g.id')
            ->join('postulante as p', 'i.id_postulante', '=', 'p.id')
            ->leftJoin('asignaciones_aulas as aa', function ($join) {
                $join->on('aa.id_postulante', '=', 'i.id_postulante')
                    ->on('aa.grupo_filtro_id', '=', 'g.id');
            })
            ->leftJoin('aulas as a', 'aa.aula_id', '=', 'a.id')
            ->where('g.id_calificacion', $request->input('id_calificacion'))
            ->select(
                'i.id',
                'p.nro_doc as dni',
                'p.nombres',
                'p.primer_apellido as paterno',
                'p.segundo_apellido as materno',
                'aa.tipo_examen as tipo_examen',
                'a.nro as aula',
            )
            ->orderBy('i.id')
            ->get();

        return $this->successResponse($participantes);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:20',
            'nombres' => 'nullable|string|max:255',
            'paterno' => 'nullable|string|max:255',
            'materno' => 'nullable|string|max:255',
            'cod_puesto' => 'nullable|string|max:50',
            'puesto' => 'nullable|string|max:255',
            'unidad' => 'nullable|string|max:255',
            'cod_examen' => 'nullable|string|max:50',
            'id_proceso' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', 422, $validator->errors());
        }

        $existeDNI = DB::table('participantes')
            ->where('dni', $request->dni)
            ->where('id_proceso', $request->id_proceso)
            ->first();

        if ($existeDNI) {
            return $this->errorResponse('El DNI ya existe para otro participante en este proceso.', 409);
        }

        $id = DB::table('participantes')->insertGetId([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'paterno' => $request->paterno,
            'materno' => $request->materno,
            'cod_puesto' => $request->cod_puesto,
            'puesto' => $request->puesto,
            'unidad' => $request->unidad,
            'cod_examen' => $request->cod_examen,
            'id_proceso' => $request->id_proceso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->successResponse(
            DB::table('participantes')->where('id', $id)->first(),
            'Participante guardado correctamente',
            201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|max:20',
            'nombres' => 'nullable|string|max:255',
            'paterno' => 'nullable|string|max:255',
            'materno' => 'nullable|string|max:255',
            'cod_puesto' => 'nullable|string|max:50',
            'puesto' => 'nullable|string|max:255',
            'unidad' => 'nullable|string|max:255',
            'cod_examen' => 'nullable|string|max:50',
            'id_proceso' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', 422, $validator->errors());
        }

        $part = DB::table('participantes')->where('id', $id)->first();
        if (!$part) {
            return $this->errorResponse('Participante no encontrado.', 404);
        }

        if ($part->dni !== $request->dni) {
            $existeDNI = DB::table('participantes')
                ->where('dni', $request->dni)
                ->where('id_proceso', $request->id_proceso)
                ->where('id', '!=', $id)
                ->first();

            if ($existeDNI) {
                return $this->errorResponse('El DNI ya existe para otro participante en este proceso.', 409);
            }
        }

        DB::table('participantes')->where('id', $id)->update([
            'dni' => $request->dni,
            'nombres' => $request->nombres,
            'paterno' => $request->paterno,
            'materno' => $request->materno,
            'cod_puesto' => $request->cod_puesto,
            'puesto' => $request->puesto,
            'unidad' => $request->unidad,
            'cod_examen' => $request->cod_examen,
            'id_proceso' => $request->id_proceso,
            'updated_at' => now(),
        ]);

        return $this->successResponse(
            DB::table('participantes')->where('id', $id)->first(),
            'Participante actualizado correctamente'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $part = DB::table('participantes')->where('id', $id)->first();
        if (!$part) {
            return $this->errorResponse('Participante no encontrado.', 404);
        }

        DB::table('participantes')->where('id', $id)->delete();

        return $this->successResponse(null, 'Participante eliminado correctamente');
    }
}
