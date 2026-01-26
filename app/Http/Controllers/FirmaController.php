<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Inscripcion;

class FirmaController extends Controller
{

public function verificarFirma($codigo)
{
    try {
      $inscripcion = Inscripcion::join('postulante', 'inscripciones.id_postulante', '=', 'postulante.id')
          ->where('inscripciones.codigo', $codigo)
          ->select( 'inscripciones.*', 'postulante.nro_doc as postulante_dni')
          ->firstOrFail();

        $rutaPdf = public_path('/documentos/'.auth()->user()->id_proceso.'/inscripciones/constancias/'.$inscripcion->postulante_dni.'.pdf');

        if (!file_exists($rutaPdf)) {
            throw new \Exception('El PDF no existe');
        }

        if (filesize($rutaPdf) === 0) {
            throw new \Exception('El PDF está vacío');
        }

        $resultado = $this->verificarFirmaPdf($rutaPdf);

        return response()->json([
            'success' => true,
            'codigo_inscripcion' => $codigo,
            'estado' => $this->mapearEstado($resultado['estado_general'] ?? 'INDETERMINADO'),
            'total_firmas' => $resultado['total_firmas'] ?? 0,
            'firmas' => $this->mapearFirmas($resultado['firmas_validadas'] ?? []),
            'alumno' => [
                'dni' => $inscripcion->alumno->dni ?? null,
                'nombre' => $inscripcion->alumno->nombre_completo ?? null,
            ],
            'fecha_verificacion' => now()->format('d/m/Y H:i:s'),
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
        return response()->json([
            'success' => false,
            'message' => 'Inscripción no encontrada'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}


private function verificarFirmaPdf(string $rutaPdf): array
{
    $client = new Client([
        'timeout' => 60,
        'verify' => false, // si el SSL da problemas en test
    ]);

    $response = $client->post(
        'https://test-admision.unap.edu.pe/service_firma/verificar-firma/',
        [
            'multipart' => [
                [
                    'name' => 'pdf',
                    'contents' => fopen($rutaPdf, 'r'),
                    'filename' => basename($rutaPdf),
                ],
            ],
        ]
    );

    $body = $response->getBody()->getContents();

    $json = json_decode($body, true);

    if (!$json) {
        throw new \Exception('Respuesta inválida del servicio de verificación');
    }

    return $json;
}


private function mapearEstado(string $estado): string
{
    return match (strtoupper($estado)) {
        'VALIDO', 'VALIDA', 'OK' => 'VÁLIDO',
        'INVALIDO', 'INVALIDA' => 'INVÁLIDO',
        default => 'INDETERMINADO',
    };
}


private function mapearFirmas(array $firmas): array
{
    return collect($firmas)->map(function ($firma) {

        $firmante =
            $firma['subject']['Common Name']
            ?? $firma['certificado']['sujeto']
            ?? null;

        $mensaje = $firma['mensaje'] ?? '';

        // Normalizamos el mensaje: mayúsculas y quitamos acentos
        $mensaje_normalizado = strtoupper($this->removeAccents($mensaje));

        return [
            'indice' => $firma['indice'] ?? null,
            'firmante' => $firmante,
            'email' => $firma['subject']['Email Address'] ?? null,
            'algoritmo' => $firma['algoritmo'] ?? null,
            'fecha_firma' => $firma['fecha_firma'] ?? null,
            'estado_servicio' => $firma['estado'] ?? null,
            'mensaje' => $mensaje,
            // Detectamos firma válida sin importar mayúsculas o acentos
            'valida_tecnica' => str_contains($mensaje_normalizado, 'FIRMA VALIDA'),
            'certificado' => [
                'emisor' => $firma['certificado']['emisor'] ?? null,
                'valido_desde' => $firma['certificado']['validez_desde'] ?? null,
                'valido_hasta' => $firma['certificado']['validez_hasta'] ?? null,
                'numero_serie' => $firma['certificado']['numero_serie'] ?? null,
            ],
        ];
    })->toArray();
}


private function removeAccents(string $str): string
{
    $unwanted_array = [
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'Ñ'=>'N','ñ'=>'n',
        'Ü'=>'U','ü'=>'u'
    ];
    return strtr($str, $unwanted_array);
}

private function formatearTamano(int $bytes): string
{
    $kb = 1024;
    $mb = $kb * 1024;

    if ($bytes >= $mb) {
        return round($bytes / $mb, 2) . ' MB';
    }

    if ($bytes >= $kb) {
        return round($bytes / $kb, 2) . ' KB';
    }

    return $bytes . ' bytes';
}


public function verPdf($codigo)
{
    $inscripcion = Inscripcion::join(
            'postulante',
            'inscripciones.id_postulante',
            '=',
            'postulante.id'
        )
        ->where('inscripciones.codigo', $codigo)
        ->select('postulante.nro_doc as dni')
        ->firstOrFail();

    $rutaPdf = public_path(
        'documentos/' .
        auth()->user()->id_proceso .
        '/inscripciones/constancias/' .
        $inscripcion->dni .
        '.pdf'
    );

    abort_unless(file_exists($rutaPdf), 404, 'PDF no encontrado');

    // Retornamos el PDF como stream, sin forzar inline
    return response()->file($rutaPdf, [
        'Content-Type' => 'application/pdf',
        'Accept-Ranges' => 'bytes', // esencial para pdf.js
    ]);
}


public function verificacion($codigo)
{
    return Inertia::render('Publico/Firma/Index', [
        'codigo' => $codigo,
    ]);
}

}
