<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'codigo' => fake()->unique()->regexify('[A-Z]{3}'),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
