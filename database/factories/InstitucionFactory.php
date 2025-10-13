<?php

namespace Database\Factories;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'codigo_presupuestario' => fake()->unique()->numerify('######'),
            'tipo' => fake()->randomElement(['publica', 'privada', 'subvencionada']),
            'telefono' => fake()->phoneNumber(),
            'correo' => fake()->companyEmail(),
            'direccion' => fake()->address(),
            'activo' => true,
        ];
    }
}
