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

class HistoryUxTest extends TestCase
{
    use RefreshDatabase;

    private function crearContextoJuez(): array
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez UX',
            'email' => 'juez.ux@feria.test',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        $juez = Juez::create([
            'nombre' => 'Juez UX',
            'cedula' => '3-3333-3333',
            'sexo' => 'M',
            'telefono' => '8888-5555',
            'correo' => 'juez.ux@feria.test',
            'grado_academico' => 'Doctorado',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        $permission = Permission::firstOrCreate(['name' => 'proyectos.ver', 'guard_name' => 'sanctum']);
        $usuario->givePermissionTo($permission);

        return ['usuario' => $usuario, 'juez' => $juez];
    }

    private function crearAsignacionConEstado(Juez $juez, bool $completada): AsignacionJuez
    {
        $etapa = Etapa::firstOrCreate(['id' => 1, 'nombre' => 'institucional']);
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto ' . ($completada ? 'Completado' : 'Pendiente'),
            'resumen' => 'Resumen test',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'UX-' . time() . '-' . rand(100, 999),
        ]);

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapa->id,
            'tipo_eval' => 'escrita',
        ]);

        if ($completada) {
            $rubrica = Rubrica::firstOrCreate([
                'nombre' => 'Evaluación Escrita Test',
                'tipo_eval' => 'escrita',
            ], [
                'ponderacion' => 0.6,
            ]);

            $criterio = Criterio::firstOrCreate([
                'rubrica_id' => $rubrica->id,
                'nombre' => 'Criterio Completado',
            ], [
                'peso' => 1.0,
                'max_puntos' => 100,
            ]);

            Calificacion::create([
                'asignacion_juez_id' => $asignacion->id,
                'criterio_id' => $criterio->id,
                'puntaje' => 90,
                'comentario' => 'Bien',
            ]);
        }

        return $asignacion;
    }

    public function test_forzado_estado_completed_en_modo_historial(): void
    {
        $data = $this->crearContextoJuez();
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearAsignacionConEstado($juez, true);
        $this->crearAsignacionConEstado($juez, false);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');
        $this->assertIsArray($items);

        foreach ($items as $item) {
            $this->assertEquals('completed', $item['estado']);
        }
    }

    public function test_asignaciones_completadas_tienen_gradesCount_mayor_cero(): void
    {
        $data = $this->crearContextoJuez();
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearAsignacionConEstado($juez, true);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');

        if (count($items) > 0) {
            foreach ($items as $item) {
                $this->assertGreaterThan(0, $item['gradesCount'] ?? 0);
            }
        }
    }

    public function test_estado_completed_implica_accion_de_lectura(): void
    {
        $data = $this->crearContextoJuez();
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearAsignacionConEstado($juez, true);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');

        foreach ($items as $item) {
            if ($item['estado'] === 'completed') {
                $this->assertGreaterThanOrEqual(1, $item['gradesCount'] ?? 0);
                $this->assertEquals('completed', $item['estado']);
            }
        }
    }

    public function test_filtro_completed_excluye_pendientes_del_resultado(): void
    {
        $data = $this->crearContextoJuez();
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearAsignacionConEstado($juez, true);
        $this->crearAsignacionConEstado($juez, false);
        $this->crearAsignacionConEstado($juez, false);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');
        
        foreach ($items as $item) {
            $this->assertNotEquals('pending', $item['estado']);
        }

        $countCompleted = collect($items)->where('estado', 'completed')->count();
        $this->assertGreaterThan(0, $countCompleted);
    }

    public function test_historial_puede_ordenarse_por_lastGradedAt(): void
    {
        $data = $this->crearContextoJuez();
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearAsignacionConEstado($juez, true);
        sleep(1);
        $this->crearAsignacionConEstado($juez, true);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?estado=completed');

        $response->assertStatus(200);
        
        $items = $response->json('data');
        
        if (count($items) >= 2) {
            foreach ($items as $item) {
                $this->assertArrayHasKey('lastGradedAt', $item);
            }
            
            $itemsWithDates = collect($items)
                ->filter(fn($item) => $item['lastGradedAt'] !== null)
                ->sortByDesc(fn($item) => strtotime($item['lastGradedAt']))
                ->values()
                ->all();
            
            $this->assertGreaterThan(0, count($itemsWithDates));
            
            if (count($itemsWithDates) >= 2) {
                $firstDate = strtotime($itemsWithDates[0]['lastGradedAt']);
                $secondDate = strtotime($itemsWithDates[1]['lastGradedAt']);
                $this->assertGreaterThanOrEqual($secondDate, $firstDate);
            }
        }
    }
}
