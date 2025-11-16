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
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HistoryApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearJuezConAsignacionCompletada(): array
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez Historial',
            'email' => 'juez.historial@feria.test',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        $juez = Juez::create([
            'nombre' => 'Juez Historial',
            'cedula' => '1-1111-1111',
            'sexo' => 'M',
            'telefono' => '8888-7777',
            'correo' => 'juez.historial@feria.test',
            'grado_academico' => 'Maestría',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        $permission = Permission::firstOrCreate(['name' => 'proyectos.ver', 'guard_name' => 'sanctum']);
        $usuario->givePermissionTo($permission);

        $etapa = Etapa::create(['id' => 1, 'nombre' => 'institucional']);
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Historial Test',
            'resumen' => 'Resumen test',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'HIST-' . time(),
        ]);

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'escrita',
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
            'max_puntos' => 100,
        ]);

        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $criterio->id,
            'puntaje' => 85,
            'comentario' => 'Excelente',
        ]);

        return [
            'usuario' => $usuario,
            'juez' => $juez,
            'asignacion' => $asignacion,
        ];
    }

    public function test_historial_lista_solo_completadas(): void
    {
        $data = $this->crearJuezConAsignacionCompletada();
        $usuario = $data['usuario'];

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');
        $this->assertIsArray($items);

        if (count($items) > 0) {
            foreach ($items as $item) {
                $this->assertEquals('completed', $item['estado']);
                $this->assertGreaterThanOrEqual(1, $item['gradesCount'] ?? 0);
                
                $this->assertArrayHasKey('lastGradedAt', $item);
                $this->assertIsString($item['lastGradedAt']);
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $item['lastGradedAt']);
                
                $this->assertArrayHasKey('criteriaTotal', $item);
                $this->assertIsInt($item['criteriaTotal']);
                $this->assertGreaterThan(0, $item['criteriaTotal']);
            }
        }
    }

    public function test_historial_no_devuelve_pendientes(): void
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez Test',
            'email' => 'juez.pend@test.com',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        $juez = Juez::create([
            'nombre' => 'Juez Test',
            'cedula' => '2-2222-2222',
            'sexo' => 'F',
            'telefono' => '8888-6666',
            'correo' => 'juez.pend@test.com',
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        $permission = Permission::firstOrCreate(['name' => 'proyectos.ver', 'guard_name' => 'sanctum']);
        $usuario->givePermissionTo($permission);

        $etapa = Etapa::firstOrCreate(['id' => 1, 'nombre' => 'institucional']);
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Pendiente',
            'resumen' => 'Resumen',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'PEND-' . time(),
        ]);

        AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'oral',
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');
        $this->assertIsArray($items);
        
        foreach ($items as $item) {
            $this->assertNotEquals('pending', $item['estado']);
        }
    }
}
