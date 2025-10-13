<?php

namespace Tests\Feature\Security;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Etapa;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioJuezSinPermisos(): Usuario
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez Sin Permisos',
            'email' => 'juez.sinpermisos@test.com',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        Juez::create([
            'nombre' => 'Juez Sin Permisos',
            'cedula' => '1-0000-0001',
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => 'juez.sinpermisos@test.com',
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        return $usuario;
    }

    private function otorgarPermiso(Usuario $usuario, string $permiso): void
    {
        $permission = Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'sanctum']);
        $usuario->givePermissionTo($permission);
    }

    public function test_asignaciones_requiere_auth_y_permiso_proyectos_ver(): void
    {
        // Sin autenticación
        $response = $this->getJson('/api/juez/asignaciones');
        $response->assertStatus(401);

        // Con autenticación pero sin permiso
        $usuario = $this->crearUsuarioJuezSinPermisos();
        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones');
        $response->assertStatus(403);

        // Con autenticación y permiso
        $this->otorgarPermiso($usuario, 'proyectos.ver');
        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones');
        $response->assertStatus(200);
    }

    public function test_calificaciones_get_requiere_permiso_calificaciones_ver(): void
    {
        $usuario = $this->crearUsuarioJuezSinPermisos();

        // Sin permiso
        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/calificaciones?asignacion_juez_id=1');
        $response->assertStatus(403);

        // Con permiso
        $this->otorgarPermiso($usuario, 'calificaciones.ver');
        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/calificaciones?asignacion_juez_id=1');
        // Ya no es 403 por falta de permiso, puede ser 200, 403 (no pertenece) o 422 (validación)
        $this->assertNotEquals(403, $response->status(), 'No debe fallar por falta de permiso');
    }

    public function test_calificaciones_post_requiere_permiso_calificaciones_crear(): void
    {
        $usuario = $this->crearUsuarioJuezSinPermisos();

        // Sin permiso
        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => 1,
                'items' => [],
            ]);
        $response->assertStatus(403);

        // Con permiso
        $this->otorgarPermiso($usuario, 'calificaciones.crear');
        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => 1,
                'items' => [],
            ]);
        // Ya no es 403 por permiso, ahora puede ser 422 por validación u otro error
        $this->assertNotEquals(403, $response->status());
    }

    public function test_consolidar_requiere_permiso_calificaciones_consolidar(): void
    {
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);
        Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $usuario = $this->crearUsuarioJuezSinPermisos();

        // Crear proyecto válido
        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test',
            'resumen' => 'Resumen',
            'area_id' => Area::first()->id,
            'categoria_id' => Categoria::first()->id,
            'institucion_id' => Institucion::first()->id,
            'modalidad_id' => Modalidad::first()->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-001',
        ]);

        // Sin permiso
        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => 1,
            ]);
        $response->assertStatus(403);

        // Con permiso
        $this->otorgarPermiso($usuario, 'calificaciones.consolidar');
        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => 1,
            ]);
        // Ya no es 403 por permiso, puede ser 422 por validación de cardinalidad
        $this->assertNotEquals(403, $response->status());
    }
}
