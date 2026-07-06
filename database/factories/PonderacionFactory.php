<?php

namespace Database\Factories;

use App\Models\Ponderacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PonderacionFactory extends Factory
{
    protected $model = Ponderacion::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->sentence(3),
            'total_preguntas' => 60,
            'total_ponderacion' => 3000,
            'estado' => true,
        ];
    }
}
