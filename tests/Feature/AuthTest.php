<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Rol;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        $rol = Rol::create(['nombre' => 'admin']);

        $user = Usuario::create([
            'email'    => 'user@example.com',
            'password' => 'password123', // Se hashea automáticamente por el cast
            'rol_id'   => $rol->id,
            'activo'   => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'rol']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        Rol::create(['nombre' => 'admin']);

        Usuario::create([
            'email'    => 'user@example.com',
            'password' => 'password123',
            'rol_id'   => 1,
            'activo'   => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Credenciales inválidas']);
    }

    public function test_user_cannot_login_if_inactive()
    {
        Rol::create(['nombre' => 'admin']);

        Usuario::create([
            'email'    => 'user@example.com',
            'password' => 'password123',
            'rol_id'   => 1,
            'activo'   => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Credenciales inválidas']);
    }
}

