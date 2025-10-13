<?php

namespace Database\Factories;

use App\Models\Regional;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionalFactory extends Factory
{
    protected $model = Regional::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->state(),
            'codigo' => fake()->unique()->numerify('##'),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
