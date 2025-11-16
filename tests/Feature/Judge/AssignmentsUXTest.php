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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AssignmentsUXTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignments_include_grades_count(): void
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
            'titulo' => 'Proyecto Test',
            'resumen' => 'Test',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-001',
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
            'nombre' => 'Criterio 1',
            'peso' => 1.0,
            'max_puntos' => 10,
        ]);

        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $criterio->id,
            'puntaje' => 8,
            'comentario' => 'Bien',
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('gradesCount', $data[0]);
        $this->assertEquals(1, $data[0]['gradesCount']);
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
            'nombre' => 'Juez Test',
            'email' => 'juez@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $area = Area::first();

        $juez = Juez::create([
            'nombre' => 'Juez Test',
            'cedula' => '1-1111-1111',
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => 'juez@test.com',
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $usuario->setRelation('juez', $juez);

        try {
            $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
            $usuario->assignRole($role);

            $permission = Permission::firstOrCreate(['name' => 'proyectos.ver', 'guard_name' => 'sanctum']);
            $usuario->givePermissionTo($permission);
        } catch (\Exception $e) {
            // Continue without roles
        }

        return $usuario;
    }
}
