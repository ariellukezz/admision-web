<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use DB;

class CepreController extends Controller
{
    
    public function getParticipanteCepre($dni)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', 'https://sistemas.cepreuna.edu.pe/api/v1/'.$dni, [
                'headers' => [
                    'Authorization' => 'cepreuna_v1_api',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $participante = isset($data[0]) ? $data[0] : $data;

            if(!empty($participante) && isset($participante['habilitado']) && (string) $participante['habilitado'] === '1'){
                $this->response['estado'] = true;
                $this->response['datos'] = $participante;
                return response()->json($this->response, 200);
            }else { 
                $this->response['estado'] = false;
                return response()->json($this->response, 200);
            }

        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function getVerInscripcion(Request $request)
    {
        $proceso = 10;
        $result = DB::table('inscripciones as ins')
        ->join('postulante as pos', 'pos.id', '=', 'ins.id_postulante')
        ->where('pos.nro_doc', $request->dni)
        ->where('ins.estado', 0)
        ->where('ins.id_proceso', $proceso)
        ->selectRaw('IF(ins.id != 0, TRUE, FALSE) AS estado')
        ->value('estado');

        $estado = $result ? true : false;
        return response()->json($estado, 200);

    }


    public function getFilteredPostulantes()
    {
        // Paso 1: Obtener los DNI de la base de datos
        $inscripciones = DB::table('inscripciones as ins')
            ->join('postulante as pos', 'ins.id_postulante', '=', 'pos.id')
            ->join('colegios as col', 'col.id', '=', 'pos.id_colegio')
            ->select('pos.nro_doc', 'col.id_gestion', 'col.gestion')
            ->where('ins.id_proceso', 10)
            ->where('ins.estado', 0)
            ->limit(100);

        // Extraer los DNI en un array
        $dnis = $inscripciones->pluck('nro_doc')->toArray();

        // Paso 2: Consultar la API para cada DNI individualmente
        $colData = [];
        foreach ($dnis as $dni) {
            $response = Http::withHeaders([
                'Authorization' => 'cepreuna_v1_api',
                'Content-Type' => 'application/json',
            ])->get('https://sistemas.cepreuna.edu.pe/api/v1/' . $dni);

            // Verificar si la respuesta es exitosa
            if ($response->successful()) {
                $data = $response->json();
                // Solo agrega datos si el campo 'habilitado' es 0
                if (isset($data['habilitado'])) {
                    $colData[$dni] = [
                        'tipo_cole' => $data['tipo_cole'],
                        'habilitado' => $data['habilitado']
                    ];
                }
            } else {
                // Manejar el error de la API si es necesario
                // Podrías registrar el error o hacer algo específico
            }
        }

        $filteredResults = [];
        foreach ($inscripciones as $inscripcion) {
            $nroDoc = $inscripcion->nro_doc;

            if (isset($colData[$nroDoc]) && $colData[$nroDoc]['habilitado'] == 0) {
                $filteredResults[] = [
                    'nro_doc' => $nroDoc,
                    'id_gestion' => $inscripcion->id_gestion,
                    'gestion' => $inscripcion->gestion,
                    'tipo_cole' => $colData[$nroDoc]['tipo_cole']
                ];
            }
        }

        return response()->json($filteredResults);
    }



}
