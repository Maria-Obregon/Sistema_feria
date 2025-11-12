<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Criterio>
 */
class CriterioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rubrica_id' => \App\Models\Rubrica::factory(),
            'nombre' => $this->faker->unique()->sentence(3),
            'peso' => $this->faker->randomFloat(2, 0.1, 1.0),
            'max_puntos' => $this->faker->numberBetween(10, 100),
        ];
    }
}
