<?php

namespace Tests\Feature\Judge;

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
use App\Models\Rubrica;
use App\Models\Usuario;
use App\Services\GradeConsolidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CalificacionesUXTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_closed_returns_422(): void
    {
        $this->seedBasicData();

        $usuario = $this->createJudgeUser();
        $juez = $usuario->juez;

        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Cerrado',
            'resumen' => 'Test etapa cerrada',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-CLOSED',
        ]);

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'escrita',
            'asignado_en' => now(),
        ]);

        $rubrica = Rubrica::create([
            'nombre' => 'Evaluación Escrita',
            'tipo_eval' => 'escrita',
            'ponderacion' => 0.6,
        ]);

        $criterio = Criterio::create([
            'rubrica_id' => $rubrica->id,
            'nombre' => 'Criterio Test',
            'peso' => 1.0,
            'max_puntos' => 10,
        ]);

        $mockService = $this->partialMock(GradeConsolidationService::class, function ($mock) use ($proyecto, $etapa) {
            $mock->shouldReceive('isStageClosed')
                ->with($proyecto->id, $etapa->id)
                ->andReturn(true);
        });

        $response = $this->actingAs($usuario, 'sanctum')
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

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Etapa cerrada; no se permiten cambios',
        ]);
    }

    public function test_can_load_existing_grades_without_changes_flag(): void
    {
        $this->seedBasicData();

        $usuario = $this->createJudgeUser();
        $juez = $usuario->juez;

        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Existente',
            'resumen' => 'Test carga previa',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-EXIST',
        ]);

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'escrita',
            'asignado_en' => now(),
        ]);

        $rubrica = Rubrica::create([
            'nombre' => 'Evaluación Escrita',
            'tipo_eval' => 'escrita',
            'ponderacion' => 0.6,
        ]);

        $criterio = Criterio::create([
            'rubrica_id' => $rubrica->id,
            'nombre' => 'Criterio Previo',
            'peso' => 1.0,
            'max_puntos' => 10,
        ]);

        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $criterio->id,
            'puntaje' => 7,
            'comentario' => 'Previo',
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/calificaciones?asignacion_juez_id=' . $asignacion->id);

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertEquals(7, $data[0]['puntaje']);
        $this->assertEquals('Previo', $data[0]['comentario']);
    }

    private function seedBasicData(): void
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);
        Etapa::firstOrCreate(['id' => 1], ['nombre' => 'institucional']);
        Modalidad::firstOrCreate(['nombre' => 'Individual']);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function createJudgeUser(): Usuario
    {
        $usuario = Usuario::create([
            'nombre' => 'Juez UX',
            'email' => 'juez_ux@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $area = Area::first();

        $juez = Juez::create([
            'nombre' => 'Juez UX',
            'cedula' => '2-2222-2222',
            'sexo' => 'F',
            'telefono' => '7777-7777',
            'correo' => 'juez_ux@test.com',
            'grado_academico' => 'Maestría',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $usuario->setRelation('juez', $juez);

        try {
            $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
            $usuario->assignRole($role);

            $permissions = ['proyectos.ver', 'calificaciones.ver', 'calificaciones.crear'];
            foreach ($permissions as $perm) {
                $permission = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
                $usuario->givePermissionTo($permission);
            }
        } catch (\Exception $e) {
            // Continue without roles
        }

        return $usuario;
    }
}
