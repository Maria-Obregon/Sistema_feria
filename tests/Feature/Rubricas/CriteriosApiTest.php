<?php

namespace Tests\Feature\Rubricas;

use App\Models\Criterio;
use App\Models\Rubrica;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CriteriosApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioAutenticado(): Usuario
    {
        return Usuario::create([
            'nombre' => 'Usuario Test',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);
    }

    private function seedRubrica(string $tipoEval, int $numCriterios = 2): Rubrica
    {
        $rubrica = Rubrica::create([
            'nombre' => "Rúbrica {$tipoEval}",
            'tipo_eval' => $tipoEval,
            'ponderacion' => $tipoEval === 'escrita' ? 0.60 : 0.40,
        ]);

        $peso = 1.0 / $numCriterios;
        for ($i = 1; $i <= $numCriterios; $i++) {
            Criterio::create([
                'rubrica_id' => $rubrica->id,
                'nombre' => "Criterio {$i}",
                'peso' => $peso,
                'max_puntos' => 100,
            ]);
        }

        return $rubrica;
    }

    public function test_criterios_escrita_200(): void
    {
        $usuario = $this->crearUsuarioAutenticado();
        $this->seedRubrica('escrita', 3);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/rubricas/escrita/criterios');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nombre', 'peso', 'max_puntos'],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_criterios_oral_200(): void
    {
        $usuario = $this->crearUsuarioAutenticado();
        $this->seedRubrica('oral', 2);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/rubricas/oral/criterios');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'nombre', 'peso', 'max_puntos'],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_criterios_tipo_invalido_422(): void
    {
        $usuario = $this->crearUsuarioAutenticado();

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/rubricas/invalido/criterios');

        $response->assertStatus(422);
    }
}
