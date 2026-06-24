<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dni'      => $this->faker->numerify('########'),
            'name'     => $this->faker->firstName(),
            'paterno'  => $this->faker->lastName(),
            'materno'  => $this->faker->lastName(),
            'email'    => $this->faker->unique()->safeEmail(),
            'celular'  => $this->faker->numerify('9########'),
            'password' => Hash::make('password'),
            'id_rol'   => 2,
            'id_proceso' => 1,
            'estado'   => 1,
            'programas' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['id_rol' => 1]);
    }

    public function revisor(): static
    {
        return $this->state(fn (array $attributes) => ['id_rol' => 2]);
    }
}
