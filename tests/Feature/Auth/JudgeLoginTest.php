<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JudgeLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_ok_con_credenciales_de_demo(): void
    {
        // Crear usuario de prueba
        Usuario::create([
            'nombre' => 'Juez Demo',
            'email' => 'juez@feria.test',
            'password' => Hash::make('Juez123!'),
            'activo' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'juez@feria.test',
            'password' => 'Juez123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
        
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_falla_con_password_incorrecto(): void
    {
        Usuario::create([
            'nombre' => 'Juez Demo',
            'email' => 'juez@feria.test',
            'password' => Hash::make('Juez123!'),
            'activo' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'juez@feria.test',
            'password' => 'PasswordIncorrecto',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Credenciales inválidas',
            ]);
    }
}
