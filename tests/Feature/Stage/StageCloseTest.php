<?php

namespace Tests\Feature\Stage;

use App\Models\Area;
use App\Models\AsignacionJuez;
use App\Models\Calificacion;
use App\Models\Categoria;
use App\Models\Criterio;
use App\Models\Etapa;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\ResultadoEtapa;
use App\Models\Rubrica;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StageCloseTest extends TestCase
{
    use RefreshDatabase;

    public function test_cierra_etapa_y_bloquea_edicion(): void
    {
        $this->seedBasicData();

        $usuario = $this->createUserWithPermissions(['calificaciones.consolidar', 'calificaciones.crear']);
        $juez = $this->createJudge($usuario);

        $proyecto = $this->createProyecto();
        $etapa = Etapa::where('nombre', 'institucional')->first();
        $asignacion = $this->createAsignacion($proyecto, $juez, $etapa);
        $criterio = $this->createCriterioEscrita();

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/etapas/cerrar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Etapa cerrada correctamente',
            'data' => [
                'cerrada' => true,
            ]
        ]);

        $response2 = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $criterio->id,
                        'puntaje' => 8,
                        'comentario' => 'Test',
                    ],
                ],
            ]);

        $response2->assertStatus(422);
        $response2->assertJson([
            'message' => 'Etapa cerrada; no se permiten cambios',
        ]);
    }

    public function test_reabre_etapa_y_permite_edicion(): void
    {
        $this->seedBasicData();

        $usuario = $this->createUserWithPermissions(['calificaciones.consolidar', 'calificaciones.crear']);
        $juez = $this->createJudge($usuario);

        $proyecto = $this->createProyecto();
        $etapa = Etapa::where('nombre', 'institucional')->first();
        $asignacion = $this->createAsignacion($proyecto, $juez, $etapa);
        $criterio = $this->createCriterioEscrita();

        ResultadoEtapa::create([
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'cerrada' => true,
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/etapas/abrir', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Etapa abierta correctamente',
            'data' => [
                'cerrada' => false,
            ]
        ]);

        $response2 = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $criterio->id,
                        'puntaje' => 9,
                        'comentario' => 'Bien',
                    ],
                ],
            ]);

        $response2->assertStatus(200);
    }

    public function test_show_resultado_devuelve_json_esperado(): void
    {
        $this->seedBasicData();

        $usuario = $this->createUserWithPermissions(['calificaciones.ver']);

        $proyecto = $this->createProyecto();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        ResultadoEtapa::create([
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $etapa->id,
            'nota_escrito' => 85.50,
            'nota_exposicion' => 90.00,
            'nota_final' => 87.30,
            'cerrada' => true,
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/etapas/resultado?proyecto_id=' . $proyecto->id . '&etapa_id=' . $etapa->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
                'nota_escrito' => 85.50,
                'nota_exposicion' => 90.00,
                'nota_final' => 87.30,
                'cerrada' => true,
            ]
        ]);
    }

    public function test_cerrar_requiere_permiso_consolidar(): void
    {
        $this->seedBasicData();

        $usuario = $this->createUserWithPermissions(['calificaciones.ver']);
        $proyecto = $this->createProyecto();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/etapas/cerrar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_show_requiere_permiso_ver(): void
    {
        $this->seedBasicData();

        $usuario = Usuario::create([
            'nombre' => 'Usuario Sin Permisos',
            'email' => 'sinpermisos@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $proyecto = $this->createProyecto();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/etapas/resultado?proyecto_id=' . $proyecto->id . '&etapa_id=' . $etapa->id);

        $response->assertStatus(403);
    }

    private function seedBasicData(): void
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);
        Etapa::firstOrCreate(['id' => 1], ['nombre' => 'institucional']);
        Modalidad::firstOrCreate(['nombre' => 'Individual']);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createUserWithPermissions(array $permissions): Usuario
    {
        $usuario = Usuario::create([
            'nombre' => 'Usuario Test',
            'email' => 'test' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        try {
            $role = Role::firstOrCreate(['name' => 'coordinador', 'guard_name' => 'sanctum']);
            $usuario->assignRole($role);

            foreach ($permissions as $perm) {
                $permission = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
                $usuario->givePermissionTo($permission);
            }
        } catch (\Exception $e) {
            // Continue without roles
        }

        return $usuario;
    }

    private function createJudge(Usuario $usuario): Juez
    {
        $area = Area::first();

        return Juez::create([
            'nombre' => 'Juez Test',
            'cedula' => '1-' . rand(1000, 9999) . '-1111',
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => $usuario->email,
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);
    }

    private function createProyecto(): Proyecto
    {
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();

        return Proyecto::create([
            'titulo' => 'Proyecto Test Cierre',
            'resumen' => 'Test cierre de etapa',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-CLOSE-' . uniqid(),
        ]);
    }

    private function createAsignacion(Proyecto $proyecto, Juez $juez, Etapa $etapa): AsignacionJuez
    {
        return AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'escrita',
            'asignado_en' => now(),
        ]);
    }

    private function createCriterioEscrita(): Criterio
    {
        $rubrica = Rubrica::firstOrCreate(
            ['tipo_eval' => 'escrita'],
            [
                'nombre' => 'Evaluación Escrita',
                'ponderacion' => 0.6,
            ]
        );

        return Criterio::create([
            'rubrica_id' => $rubrica->id,
            'nombre' => 'Criterio Test',
            'peso' => 1.0,
            'max_puntos' => 10,
        ]);
    }
}
