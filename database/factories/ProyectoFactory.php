<?php

namespace Database\Factories;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProyectoFactory extends Factory
{
    protected $model = Proyecto::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(6),
            'resumen' => fake()->paragraph(3),
            'etapa_actual' => fake()->randomElement(['institucional', 'circuital', 'regional', 'nacional']),
            'estado' => fake()->randomElement(['inscrito', 'en_evaluacion', 'evaluado', 'promovido']),
            'palabras_clave' => [fake()->word(), fake()->word(), fake()->word()],
            'codigo' => fake()->unique()->regexify('[A-Z]{3}[0-9]{4}'),
        ];
    }
}
