<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Regional;
use App\Models\Circuito;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition(): array
    {
        return [
            'nombre'                 => 'Colegio '.$this->faker->company(),
            'regional_id'            => Regional::factory(),
            'circuito_id'            => Circuito::factory(),
            // Campos NOT NULL en tu esquema:
            'modalidad'              => $this->faker->randomElement(['Académica','Técnica','Artística']),
            'codigo_presupuestario'  => $this->faker->unique()->numerify('##########'), // 10 dígitos (ajusta si tu migración define longitud/formato)
            // Otros que sueles tener:
            'activo'                 => true,
            'codigo' => $this->faker->unique()->bothify('COLEG-####'),

        ];
    }
}
