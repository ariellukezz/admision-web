<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AsignaturaControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public function test_index_returns_all_asignaturas_ordered(): void
    {
        $a1 = Asignatura::factory()->create(['nombre' => 'Test Aritmética XYZ', 'orden' => 1]);
        $a2 = Asignatura::factory()->create(['nombre' => 'Test Álgebra XYZ', 'orden' => 2]);

        $response = $this->getJson('/calificacion/asignaturas-list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'estado',
                'datos' => [['id', 'nombre', 'orden', 'estado']],
            ])
            ->assertJsonPath('estado', true);
    }

    public function test_store_creates_a_new_asignatura(): void
    {
        $response = $this->postJson('/calificacion/asignaturas', [
            'nombre' => 'Test Física XYZ',
            'orden' => 5,
            'estado' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('estado', true)
            ->assertJsonPath('datos.nombre', 'Test Física XYZ')
            ->assertJsonPath('datos.orden', 5);

        $this->assertDatabaseHas('asignaturas', ['nombre' => 'Test Física XYZ']);
    }

    public function test_store_validates_required_nombre(): void
    {
        $response = $this->postJson('/calificacion/asignaturas', [
            'orden' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('estado', false)
            ->assertJsonStructure(['errores' => ['nombre']]);
    }

    public function test_store_auto_assigns_orden_when_not_provided(): void
    {
        $maxOrden = Asignatura::max('orden') ?? 0;

        $response = $this->postJson('/calificacion/asignaturas', [
            'nombre' => 'Test Química XYZ',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('datos.orden', $maxOrden + 1);
    }

    public function test_update_modifies_an_existing_asignatura(): void
    {
        $asignatura = Asignatura::factory()->create(['nombre' => 'Test Viejo XYZ']);

        $response = $this->putJson("/calificacion/asignaturas/{$asignatura->id}", [
            'nombre' => 'Test Nuevo XYZ',
            'orden' => 3,
            'estado' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('datos.nombre', 'Test Nuevo XYZ')
            ->assertJsonPath('datos.estado', false);

        $this->assertDatabaseHas('asignaturas', [
            'id' => $asignatura->id,
            'nombre' => 'Test Nuevo XYZ',
        ]);
    }

    public function test_update_returns_404_for_nonexistent_asignatura(): void
    {
        $response = $this->putJson('/calificacion/asignaturas/999999', [
            'nombre' => 'Test',
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('estado', false);
    }

    public function test_destroy_deletes_an_asignatura(): void
    {
        $asignatura = Asignatura::factory()->create(['nombre' => 'Test Delete XYZ']);

        $response = $this->deleteJson("/calificacion/asignaturas/{$asignatura->id}");

        $response->assertStatus(200)
            ->assertJsonPath('estado', true);

        $this->assertDatabaseMissing('asignaturas', ['id' => $asignatura->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_asignatura(): void
    {
        $response = $this->deleteJson('/calificacion/asignaturas/999999');

        $response->assertStatus(404);
    }
}
