<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use DB;

class ReniecController extends Controller {

    public function index()
    {
        return Inertia::render('Admin/Reniec/index');
    }

    public function consultarReniecPorDni(string $dni): ?array
{
    $consulta = DB::table('consultas_reniec')
        ->orderBy('cant', 'asc')
        ->first();

    if (!$consulta) {
        return null;
    }

    // Reset diario
    if (!Carbon::now()->isSameDay($consulta->updated_at)) {
        DB::table('consultas_reniec')
            ->where('id', $consulta->id)
            ->update(['cant' => 0, 'updated_at' => now()]);
        $consulta->cant = 0;
    }

    // Validar límite
    if ($consulta->cant >= $consulta->maximo) {
        return null;
    }

    DB::table('consultas_reniec')
        ->where('id', $consulta->id)
        ->increment('cant');

    $response = Http::withHeaders([
        'Accept'        => 'application/json',
        'Authorization' => "Bearer {$consulta->token}",
    ])->get("https://service7.unap.edu.pe/api/v1/reniec/consulta/{$dni}");

    if (!$response->ok()) {
        Log::error('RENIEC error', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
        return null;
    }

    if (empty($response['data'])) {
        return null;
    }

    $data = $response['data'];

    return [
        'dni'         => $dni,
        'nombres'     => $data['prenombres'] ?? null,
        'ap_paterno'  => $data['apPrimer'] ?? null,
        'ap_materno'  => $data['apSegundo'] ?? null,
        'direccion'   => $data['direccion'] ?? null,
        'foto_base64' => $data['foto'] ?? null,
    ];
}


}
