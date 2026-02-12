<?php
namespace App\Http\Controllers\Segundas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Modalidad;
use App\Models\Postulante;
use Illuminate\Support\Facades\Http;
use DB;

class IdentidadSegundaController extends Controller
{

    public function getCondicionesLengua()
    {
        $response = Http::get('http://localhost:8080/api/v1/condicion-lengua/select');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Error al consumir el servicio'
        ], 500);
    }

    public function getPertenenciaCultural()
    {
        $response = Http::get('http://localhost:8080/api/v1/pertenencia-cultural/select');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Error al consumir el servicio'
        ], 500);
    }

    public function getLenguaIndigena()
    {
        $response = Http::get('http://localhost:8080/api/v1/lengua-indigena/select');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Error al consumir el servicio'
        ], 500);
    }

    public function getPueblosIndigenas()
    {
        $response = Http::get('http://localhost:8080/api/v1/pueblo-indigena/select');

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Error al consumir el servicio'
        ], 500);
    }

    public function getIdentidadCulturalByPostulanteProceso($id_postulante, $id_proceso){

        $postulante = Postulante::find($id_postulante);
        if (!$postulante) {
            return response()->json([
                'message' => 'Postulante no encontrado'
            ], 404);
        }

        $response = Http::get("http://localhost:8080/api/v1/identidad-cultural/{$id_postulante}/{$id_proceso}");
        $data = [];

        $data['discapacidad'] = $postulante->discapacidad;
        $data['tipo_discapacidad'] = (int)$postulante->tipo_discapacidad;

        if ($response->successful()) {
            $identidadData = $response->json();
            return response()->json(array_merge($identidadData, $data));
        }

        return response()->json(array_merge([
            'message' => $response->status() === 404
                ? 'No se encontró información de identidad cultural'
                : 'Error al consultar el servicio'
        ], $data), 200);
    }





}
