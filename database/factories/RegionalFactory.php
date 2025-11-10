<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Regional;

class RegionalFactory extends Factory
{
    protected $model = Regional::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Regional ' . $this->faker->unique()->city(),
            'activo' => true,
        ];
    }
}
