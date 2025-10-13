<?php

namespace Database\Factories;

use App\Models\Etapa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EtapaFactory extends Factory
{
    protected $model = Etapa::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Institucional', 'Circuital', 'Regional', 'Nacional']),
        ];
    }
}
