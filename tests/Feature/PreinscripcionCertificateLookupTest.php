<?php

namespace Tests\Feature;

use App\Http\Controllers\PostulanteController;
use App\Models\Documento;
use App\Models\Postulante;
use Illuminate\Http\Request;
use Tests\TestCase;

class PreinscripcionCertificateLookupTest extends TestCase
{
    public function test_get_certificado_preinscripcion_returns_saved_certificate_data(): void
    {
        $postulante = Postulante::firstOrCreate(
            ['nro_doc' => '12345678'],
            [
                'tipo_doc' => 1,
                'primer_apellido' => 'Perez',
                'segundo_apellido' => 'Lopez',
                'nombres' => 'Juan',
                'email' => 'juan@example.com',
            ]
        );

        Documento::create([
            'codigo' => 'ABC123',
            'nombre' => 'CERT. DE ESTUDIOS',
            'id_postulante' => $postulante->id,
            'id_tipo_documento' => 1,
            'estado' => 1,
            'url' => '',
            'numero' => 1,
            'observacion' => 'CERTIFICADO BLANCO',
            'fecha' => now()->toDateString(),
        ]);

        $controller = new PostulanteController();
        $response = $controller->getCertificadoPreinscripcion(new Request(['nro_doc' => '12345678']));

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);

        $this->assertTrue($payload['estado']);
        $this->assertSame('CERTIFICADO BLANCO', $payload['datos']['tipo_certificado']);
        $this->assertSame('ABC123', $payload['datos']['codigo_certificado']);
    }
}
