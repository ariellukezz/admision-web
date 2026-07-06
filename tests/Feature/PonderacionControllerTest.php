<?php

namespace Tests\Feature;

use App\Models\Ponderacion;
use App\Models\PonderacionDetalle;
use App\Models\Asignatura;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class PonderacionControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public function test_get_ponderaciones_returns_paginated_list(): void
    {
        Ponderacion::factory()->count(3)->create();

        $response = $this->postJson('/calificacion/get-ponderaciones', [
            'term' => '',
            'paginasize' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'estado',
                'datos' => [
                    'data' => [['id', 'nombre', 'total_preguntas', 'total_ponderacion', 'estado']],
                    'total',
                ],
            ])
            ->assertJsonPath('estado', true);
    }

    public function test_get_ponderaciones_filters_by_term(): void
    {
        Ponderacion::factory()->create(['nombre' => 'Test Bio XYZ 2026']);
        Ponderacion::factory()->create(['nombre' => 'Test Ing XYZ 2026']);

        $response = $this->postJson('/calificacion/get-ponderaciones', [
            'term' => 'Test Bio XYZ',
            'paginasize' => 10,
        ]);

        $response->assertStatus(200);
        $data = collect($response->json('datos.data'));
        $this->assertTrue($data->contains('nombre', 'Test Bio XYZ 2026'));
        $this->assertFalse($data->contains('nombre', 'Test Ing XYZ 2026'));
    }

    public function test_save_creates_a_new_ponderacion(): void
    {
        $response = $this->postJson('/calificacion/save-ponderacion', [
            'nombre' => 'Test CEPREUNA XYZ',
            'estado' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('estado', true)
            ->assertJsonPath('datos.nombre', 'Test CEPREUNA XYZ');

        $this->assertDatabaseHas('ponderacion_simulacro', [
            'nombre' => 'Test CEPREUNA XYZ',
        ]);
    }

    public function test_save_updates_an_existing_ponderacion(): void
    {
        $ponderacion = Ponderacion::factory()->create(['nombre' => 'Test Original XYZ']);

        $response = $this->postJson('/calificacion/save-ponderacion', [
            'id' => $ponderacion->id,
            'nombre' => 'Test Editado XYZ',
            'estado' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('datos.nombre', 'Test Editado XYZ')
            ->assertJsonPath('datos.estado', false);

        $this->assertDatabaseHas('ponderacion_simulacro', [
            'id' => $ponderacion->id,
            'nombre' => 'Test Editado XYZ',
        ]);
    }

    public function test_save_validates_required_nombre(): void
    {
        $response = $this->postJson('/calificacion/save-ponderacion', [
            'estado' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('estado', false)
            ->assertJsonStructure(['errores' => ['nombre']]);
    }

    public function test_destroy_deletes_ponderacion_and_its_detalles(): void
    {
        $ponderacion = Ponderacion::factory()->create(['nombre' => 'Test Delete Pond XYZ']);

        PonderacionDetalle::create([
            'asignatura' => 'Test Asig XYZ',
            'numero' => 1,
            'ponderacion' => 2.5,
            'id_ponderacion_simulacro' => $ponderacion->id,
            'cantidad_preguntas' => 4,
            'subtotal' => 10.0,
        ]);

        $response = $this->deleteJson("/calificacion/delete-ponderacion/{$ponderacion->id}");

        $response->assertStatus(200)
            ->assertJsonPath('estado', true);

        $this->assertDatabaseMissing('ponderacion_simulacro', ['id' => $ponderacion->id]);
    }

    public function test_destroy_returns_404_for_nonexistent(): void
    {
        $response = $this->deleteJson('/calificacion/delete-ponderacion/999999');

        $response->assertStatus(404)
            ->assertJsonPath('estado', false);
    }

    public function test_duplicar_creates_a_copy_with_details(): void
    {
        $original = Ponderacion::factory()->create(['nombre' => 'Test Original Dup XYZ']);

        PonderacionDetalle::create([
            'asignatura' => 'Test Asig Dup XYZ',
            'numero' => 1,
            'ponderacion' => 2.5,
            'id_ponderacion_simulacro' => $original->id,
            'cantidad_preguntas' => 4,
            'subtotal' => 10.0,
        ]);

        $response = $this->postJson("/calificacion/duplicar-ponderacion/{$original->id}");

        $response->assertStatus(200)
            ->assertJsonPath('datos.nombre', 'Test Original Dup XYZ (Copia)');

        $this->assertDatabaseHas('ponderacion_simulacro', ['nombre' => 'Test Original Dup XYZ (Copia)']);
    }

    public function test_get_detalle_returns_detalles_ordered(): void
    {
        $ponderacion = Ponderacion::factory()->create();

        PonderacionDetalle::create([
            'asignatura' => 'Test B XYZ',
            'numero' => 2,
            'ponderacion' => 3.0,
            'id_ponderacion_simulacro' => $ponderacion->id,
            'cantidad_preguntas' => 5,
            'subtotal' => 15.0,
        ]);
        PonderacionDetalle::create([
            'asignatura' => 'Test A XYZ',
            'numero' => 1,
            'ponderacion' => 2.5,
            'id_ponderacion_simulacro' => $ponderacion->id,
            'cantidad_preguntas' => 4,
            'subtotal' => 10.0,
        ]);

        $response = $this->getJson("/calificacion/get-ponderacion-detalle/{$ponderacion->id}");

        $response->assertStatus(200)
            ->assertJsonPath('datos.0.numero', 1)
            ->assertJsonPath('datos.1.numero', 2);
    }

    public function test_save_detalle_creates_and_updates_totals(): void
    {
        $asignatura = Asignatura::factory()->create(['nombre' => 'Test Asig Det XYZ']);
        $ponderacion = Ponderacion::factory()->create();

        $response = $this->postJson('/calificacion/save-ponderacion-detalle', [
            'id_ponderacion' => $ponderacion->id,
            'detalles' => [
                [
                    'id_asignatura' => $asignatura->id,
                    'asignatura' => 'Test Asig Det XYZ',
                    'cantidad_preguntas' => 4,
                    'ponderacion' => 2.5,
                ],
                [
                    'id_asignatura' => null,
                    'asignatura' => 'Test Asig Det 2 XYZ',
                    'cantidad_preguntas' => 6,
                    'ponderacion' => 3.0,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('estado', true);

        $ponderacion->refresh();
        $this->assertEquals(10, $ponderacion->total_preguntas);
        // subtotal = cantidad * ponderacion: 4*2.5 + 6*3.0 = 10 + 18 = 28
        $this->assertEquals(28.0, (float) $ponderacion->total_ponderacion);
    }

    public function test_save_detalle_replaces_existing_detalles(): void
    {
        $ponderacion = Ponderacion::factory()->create();

        PonderacionDetalle::create([
            'asignatura' => 'Test Vieja Det XYZ',
            'numero' => 1,
            'ponderacion' => 1.0,
            'id_ponderacion_simulacro' => $ponderacion->id,
            'cantidad_preguntas' => 2,
            'subtotal' => 2.0,
        ]);

        $response = $this->postJson('/calificacion/save-ponderacion-detalle', [
            'id_ponderacion' => $ponderacion->id,
            'detalles' => [
                [
                    'id_asignatura' => null,
                    'asignatura' => 'Test Nueva Det XYZ',
                    'cantidad_preguntas' => 5,
                    'ponderacion' => 4.0,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('ponderacion', ['asignatura' => 'Test Vieja Det XYZ']);
        $this->assertDatabaseHas('ponderacion', ['asignatura' => 'Test Nueva Det XYZ']);
    }
}
