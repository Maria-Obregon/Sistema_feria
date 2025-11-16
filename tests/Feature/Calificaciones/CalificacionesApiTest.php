<?php

namespace Tests\Feature\Calificaciones;

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
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CalificacionesApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioJuezConPermisos(array $permisos = []): array
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez Test',
            'email' => 'juez.test@feria.test',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        $juez = Juez::create([
            'nombre' => 'Juez Test',
            'cedula' => '1-0000-0001',
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => 'juez.test@feria.test',
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        foreach ($permisos as $permiso) {
            $permission = Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'sanctum']);
            $usuario->givePermissionTo($permission);
        }

        return ['usuario' => $usuario, 'juez' => $juez];
    }

    private function seedRubricaConCriterios(string $tipoEval, int $numCriterios = 2): array
    {
        $rubrica = Rubrica::create([
            'nombre' => "Rúbrica {$tipoEval}",
            'tipo_eval' => $tipoEval,
            'ponderacion' => 0.60,
        ]);

        $criterios = [];
        $peso = 1.0 / $numCriterios;
        for ($i = 1; $i <= $numCriterios; $i++) {
            $criterios[] = Criterio::create([
                'rubrica_id' => $rubrica->id,
                'nombre' => "Criterio {$i}",
                'peso' => $peso,
                'max_puntos' => 100,
            ]);
        }

        return ['rubrica' => $rubrica, 'criterios' => $criterios];
    }

    private function crearAsignacion(Juez $juez, string $tipoEval): AsignacionJuez
    {
        Etapa::firstOrCreate(['id' => 1], ['nombre' => 'institucional']);

        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test',
            'resumen' => 'Resumen de prueba',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-' . time(),
        ]);

        return AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => 1,
            'tipo_eval' => $tipoEval,
        ]);
    }

    public function test_get_calificaciones_por_asignacion_200_y_scope_al_juez(): void
    {
        $data = $this->crearUsuarioJuezConPermisos(['calificaciones.ver']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $rubricaData = $this->seedRubricaConCriterios('escrita', 2);
        $asignacion = $this->crearAsignacion($juez, 'escrita');

        // Crear calificación
        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $rubricaData['criterios'][0]->id,
            'puntaje' => 85,
            'comentario' => 'Bien',
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson("/api/calificaciones?asignacion_juez_id={$asignacion->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'criterio_id', 'puntaje'],
                ],
            ]);
    }

    public function test_post_bulk_upsert_guarda_y_actualiza_idempotente(): void
    {
        $data = $this->crearUsuarioJuezConPermisos(['calificaciones.crear']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $rubricaData = $this->seedRubricaConCriterios('escrita', 2);
        $asignacion = $this->crearAsignacion($juez, 'escrita');

        $items = [
            [
                'criterio_id' => $rubricaData['criterios'][0]->id,
                'puntaje' => 80,
                'comentario' => 'Bueno',
            ],
            [
                'criterio_id' => $rubricaData['criterios'][1]->id,
                'puntaje' => 90,
                'comentario' => 'Excelente',
            ],
        ];

        // Primera vez: crear
        $response1 = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => $items,
            ]);

        $response1->assertStatus(200);
        $this->assertEquals(2, $response1->json('total'));

        // Segunda vez: actualizar (idempotente)
        $items[0]['puntaje'] = 85;
        $response2 = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => $items,
            ]);

        $response2->assertStatus(200);
        $this->assertEquals(2, $response2->json('total'));

        // Verificar actualización
        $calificacion = Calificacion::where('asignacion_juez_id', $asignacion->id)
            ->where('criterio_id', $rubricaData['criterios'][0]->id)
            ->first();
        $this->assertEquals(85, $calificacion->puntaje);
    }

    public function test_post_puntaje_fuera_de_rango_422(): void
    {
        $data = $this->crearUsuarioJuezConPermisos(['calificaciones.crear']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $rubricaData = $this->seedRubricaConCriterios('escrita', 1);
        $asignacion = $this->crearAsignacion($juez, 'escrita');

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $rubricaData['criterios'][0]->id,
                        'puntaje' => 150, // Excede max_puntos=100
                        'comentario' => 'Test',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'El puntaje 150 excede el máximo permitido de 100 para el criterio ' . $rubricaData['criterios'][0]->id,
            ]);
    }

    public function test_post_criterio_que_no_pertenece_a_rubrica_422(): void
    {
        $data = $this->crearUsuarioJuezConPermisos(['calificaciones.crear']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->seedRubricaConCriterios('escrita', 1);
        $asignacion = $this->crearAsignacion($juez, 'escrita');

        // Crear otro criterio de otra rúbrica
        $otraRubrica = Rubrica::create([
            'nombre' => 'Otra Rúbrica',
            'tipo_eval' => 'oral',
            'ponderacion' => 0.40,
        ]);
        $criterioOtraRubrica = Criterio::create([
            'rubrica_id' => $otraRubrica->id,
            'nombre' => 'Criterio Otra',
            'peso' => 1.0,
            'max_puntos' => 100,
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $criterioOtraRubrica->id,
                        'puntaje' => 80,
                        'comentario' => 'Test',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "El criterio {$criterioOtraRubrica->id} no pertenece a la rúbrica del tipo de evaluación",
            ]);
    }

    public function test_post_bloqueado_si_etapa_cerrada_422(): void
    {
        $data = $this->crearUsuarioJuezConPermisos(['calificaciones.crear']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $rubricaData = $this->seedRubricaConCriterios('escrita', 1);
        $asignacion = $this->crearAsignacion($juez, 'escrita');

        // Mock del servicio para simular etapa cerrada
        $this->partialMock(GradeConsolidationService::class, function ($mock) use ($asignacion) {
            $mock->shouldReceive('isStageClosed')
                ->with($asignacion->proyecto_id, $asignacion->etapa_id)
                ->andReturn(true);
        });

        $response = $this->actingAs($usuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $rubricaData['criterios'][0]->id,
                        'puntaje' => 80,
                        'comentario' => 'Test',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Etapa cerrada; no se permiten cambios',
            ]);
    }
}
