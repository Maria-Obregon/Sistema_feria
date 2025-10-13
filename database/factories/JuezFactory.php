<?php

namespace Database\Factories;

use App\Models\Juez;
use Illuminate\Database\Eloquent\Factories\Factory;

class JuezFactory extends Factory
{
    protected $model = Juez::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'cedula' => fake()->unique()->numerify('##########'),
            'sexo' => fake()->randomElement(['M', 'F']),
            'telefono' => fake()->phoneNumber(),
            'correo' => fake()->unique()->safeEmail(),
            'grado_academico' => fake()->randomElement(['Licenciatura', 'Maestría', 'Doctorado']),
        ];
    }
}
