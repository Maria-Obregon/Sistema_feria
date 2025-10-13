<?php

namespace Database\Factories;

use App\Models\Circuito;
use Illuminate\Database\Eloquent\Factories\Factory;

class CircuitoFactory extends Factory
{
    protected $model = Circuito::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->city(),
            'codigo' => fake()->unique()->numerify('###'),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
