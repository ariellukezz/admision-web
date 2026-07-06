<?php

namespace Database\Factories;

use App\Models\Asignatura;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignaturaFactory extends Factory
{
    protected $model = Asignatura::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'orden' => $this->faker->numberBetween(1, 50),
            'estado' => true,
        ];
    }
}
