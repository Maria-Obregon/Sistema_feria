<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rubrica>
 */
class RubricaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->words(3, true),
            'tipo_eval' => $this->faker->randomElement(['escrito', 'exposicion', 'integral']),
            'ponderacion' => 1.00,
        ];
    }
}
