<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\InscripcionLookupService;
use Tests\TestCase;

class ConsultaInscripcionTest extends TestCase
{
    public function test_endpoint_requires_authentication(): void
    {
        $this->postJson('/api/v1/inscripciones/consulta-inscritos', [
            'nro_docs' => ['61279694'],
        ])->assertUnauthorized();
    }

    public function test_returns_inscriptions_for_the_requested_documents(): void
    {
        $user = new User();
        $user->id = 1;
        $this->actingAs($user, 'sanctum');

        $inscripciones = collect([
            (object) [
                'nro_doc' => '61279694',
                'nombre' => 'Proceso de admisión',
                'estado' => 'INSCRITO',
            ],
        ]);

        $lookup = \Mockery::mock(InscripcionLookupService::class);
        $lookup->shouldReceive('buscarInscritos')
            ->once()
            ->with(2026, ['61279694'])
            ->andReturn($inscripciones);
        $this->app->instance(InscripcionLookupService::class, $lookup);

        $this->postJson('/api/v1/inscripciones/consulta-inscritos', [
            'id_proceso' => 2026,
            'nro_docs' => ['61279694'],
        ])
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    [
                        'nro_doc' => '61279694',
                        'nombre' => 'Proceso de admisión',
                        'estado' => 'INSCRITO',
                    ],
                ],
            ]);
    }

    public function test_validates_document_numbers(): void
    {
        $user = new User();
        $user->id = 1;
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/v1/inscripciones/consulta-inscritos', [
            'id_proceso' => 2026,
            'nro_docs' => ['123'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['nro_docs.0']);
    }
}
