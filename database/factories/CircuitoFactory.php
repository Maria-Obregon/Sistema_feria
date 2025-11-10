<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Circuito;
use App\Models\Regional;

class CircuitoFactory extends Factory
{
    protected $model = Circuito::class;

    public function definition(): array
    {
        return [
            'nombre'      => 'Circuito ' . $this->faker->unique()->numerify('##-##'),
            'codigo'      => $this->faker->unique()->bothify('CIR-####'),
            'regional_id' => Regional::factory(),
            'activo'      => true,
        ];
    }
}
