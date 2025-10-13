<?php

namespace Database\Factories;

use App\Models\AsignacionJuez;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionJuezFactory extends Factory
{
    protected $model = AsignacionJuez::class;

    public function definition(): array
    {
        return [
            'tipo_eval' => fake()->randomElement(['escrita', 'oral']),
            'asignado_en' => now(),
        ];
    }
}
