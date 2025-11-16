<?php

namespace Tests\Feature;

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
use App\Services\GradeConsolidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ConsolidationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_consolidates_with_min_1_judge(): void
    {
        config()->set('grades.min_jueces.institucional', 1);
        $this->seedBasicData();

        $juezUsuario = $this->makeJudgeUser([
            'proyectos.ver',
            'calificaciones.ver',
            'calificaciones.crear',
            'calificaciones.consolidar',
        ]);

        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        // Crear proyecto
        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test Consolidación',
            'resumen' => 'Test de consolidación con 1 juez',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-CONSOL-001',
        ]);

        $rubricaCriterios = $this->seedRubricaEscritaConCriterios(2);
        $rubrica = $rubricaCriterios['rubrica'];
        $criterios = $rubricaCriterios['criterios'];

        $juez = $juezUsuario->juez;
        $asignacion = $this->makeAsignacion($proyecto, $juez, $etapa->id, 'escrita');

        $this->calificar($asignacion, $criterios, [8, 9]);

        $response = $this->actingAs($juezUsuario, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        // Assert 200
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'proyectoId',
            'etapaId',
            'juecesPorTipo',
            'notaEscrita',
            'ponderaciones',
            'notaFinal',
        ]);

        // Verificar valores
        $data = $response->json();
        $this->assertEquals($proyecto->id, $data['proyectoId']);
        $this->assertEquals($etapa->id, $data['etapaId']);
        $this->assertEquals(1, $data['juecesPorTipo']['escrita']);

        // Calcular nota esperada: (8/10 * 0.5) + (9/10 * 0.5) = 0.85
        $notaEsperada = 0.85;
        $this->assertEquals($notaEsperada, $data['notaEscrita'], '', 0.001);
        $this->assertEquals($notaEsperada, $data['notaFinal'], '', 0.001);

        // Verificar que existe registro en resultado_etapa
        $resultado = ResultadoEtapa::where('proyecto_id', $proyecto->id)
            ->where('etapa_id', $etapa->id)
            ->first();

        $this->assertNotNull($resultado, 'Debe existir registro en resultado_etapa');
        $this->assertNotNull($resultado->nota_final, 'nota_final debe tener valor');
        $this->assertEquals(85.00, $resultado->nota_final, '', 0.01);
    }

    /**
     * Test 2: Falla con cardinalidad cuando min=3 y solo hay 2 jueces
     * 
     * Verifica que se rechace la consolidación si no se cumple
     * el número mínimo de jueces requeridos.
     */
    public function test_fails_with_cardinality_when_min_3_and_only_2_judges(): void
    {
        // Configurar cardinalidad mínima a 3
        config()->set('grades.min_jueces.institucional', 3);

        // Seed básico
        $this->seedBasicData();

        // Obtener datos
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        // Crear proyecto
        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test Cardinalidad',
            'resumen' => 'Test con solo 2 jueces',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-CARD-002',
        ]);

        // Crear rúbrica escrita con criterios
        $rubricaCriterios = $this->seedRubricaEscritaConCriterios(2);
        $criterios = $rubricaCriterios['criterios'];

        // Crear 2 jueces (insuficiente para mínimo de 3)
        for ($i = 1; $i <= 2; $i++) {
            $juezUsuario = $this->makeJudgeUser([
                'proyectos.ver',
                'calificaciones.ver',
                'calificaciones.crear',
                'calificaciones.consolidar',
            ], "juez{$i}@test.com");

            $asignacion = $this->makeAsignacion(
                $proyecto, 
                $juezUsuario->juez, 
                $etapa->id, 
                'escrita'
            );

            // Calificar
            $this->calificar($asignacion, $criterios, [8, 9]);
        }

        // Crear un usuario coordinador con permiso de consolidar
        $coordinador = $this->makeCoordinatorUser();

        // Intentar consolidar
        $response = $this->actingAs($coordinador, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        // Assert 422
        $response->assertStatus(422);

        // Assert mensaje de error menciona "Se requieren al menos 3 jueces"
        $data = $response->json();
        $this->assertStringContainsString(
            'Se requieren al menos 3 jueces',
            $data['message'],
            'El mensaje debe indicar que se requieren al menos 3 jueces'
        );

        $response->assertJsonStructure([
            'message',
            'errors' => ['cardinalidad'],
        ]);
    }

    /**
     * Test 3: Promedio correcto entre múltiples jueces
     * 
     * Verifica que el cálculo del promedio entre varios jueces
     * sea correcto matemáticamente.
     */
    public function test_averages_multiple_judges_correctly(): void
    {
        // Configurar cardinalidad mínima a 1 para no bloquear
        config()->set('grades.min_jueces.institucional', 1);

        // Seed básico
        $this->seedBasicData();

        // Obtener datos
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        // Crear proyecto
        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test Promedio',
            'resumen' => 'Test con 3 jueces para verificar promedio',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-PROM-003',
        ]);

        $rubricaCriterios = $this->seedRubricaEscritaConCriterios(2);
        $criterios = $rubricaCriterios['criterios'];

        $puntajesPorJuez = [
            [7, 8],
            [8, 9],
            [9, 10],
        ];

        for ($i = 0; $i < 3; $i++) {
            $juezUsuario = $this->makeJudgeUser([
                'proyectos.ver',
                'calificaciones.ver',
                'calificaciones.crear',
            ], "juez_prom_{$i}@test.com");

            $asignacion = $this->makeAsignacion(
                $proyecto,
                $juezUsuario->juez,
                $etapa->id,
                'escrita'
            );

            $this->calificar($asignacion, $criterios, $puntajesPorJuez[$i]);
        }

        // Crear usuario coordinador con permiso de consolidar
        $coordinador = $this->makeCoordinatorUser();

        $response = $this->actingAs($coordinador, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        // Assert 200
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(3, $data['juecesPorTipo']['escrita']);

        $promedioEsperado = 0.85;
        $this->assertEquals(
            $promedioEsperado, 
            $data['notaEscrita'], 
            'El promedio de las 3 notas debe ser 0.85',
            0.001
        );

        $this->assertEquals(
            $promedioEsperado,
            $data['notaFinal'],
            'La nota final debe ser igual al promedio escrito',
            0.001
        );
    }

    public function test_store_is_blocked_when_stage_closed(): void
    {
        // Seed básico
        $this->seedBasicData();

        // Obtener datos
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        // Crear proyecto
        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test Etapa Cerrada',
            'resumen' => 'Test con etapa cerrada',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-CERR-004',
        ]);

        $rubricaCriterios = $this->seedRubricaEscritaConCriterios(2);
        $criterios = $rubricaCriterios['criterios'];

        // Crear juez
        $juezUsuario = $this->makeJudgeUser([
            'proyectos.ver',
            'calificaciones.ver',
            'calificaciones.crear',
        ]);

        $asignacion = $this->makeAsignacion(
            $proyecto,
            $juezUsuario->juez,
            $etapa->id,
            'escrita'
        );

        // Mockear el servicio para que isStageClosed retorne true
        $mockService = $this->partialMock(GradeConsolidationService::class, function ($mock) use ($proyecto, $etapa) {
            $mock->shouldReceive('isStageClosed')
                ->with($proyecto->id, $etapa->id)
                ->andReturn(true);
        });

        // Intentar calificar
        $response = $this->actingAs($juezUsuario, 'sanctum')
            ->postJson('/api/calificaciones', [
                'asignacion_juez_id' => $asignacion->id,
                'items' => [
                    [
                        'criterio_id' => $criterios[0]->id,
                        'puntaje' => 8,
                        'comentario' => 'Buen trabajo',
                    ],
                    [
                        'criterio_id' => $criterios[1]->id,
                        'puntaje' => 9,
                        'comentario' => 'Excelente',
                    ],
                ],
            ]);

        // Assert 422
        $response->assertStatus(422);

        $response->assertJson([
            'message' => 'Etapa cerrada; no se permiten cambios',
        ]);
    }

    public function test_endpoint_consolidar_responde_formato_correcto(): void
    {
        config()->set('grades.min_jueces.institucional', 1);

        $this->seedBasicData();

        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::first();
        $etapa = Etapa::where('nombre', 'institucional')->first();

        $coordinador = $this->makeCoordinatorUser();

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test Formato',
            'resumen' => 'Test formato respuesta',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-FMT-005',
        ]);

        $rubricaCriterios = $this->seedRubricaEscritaConCriterios(2);
        $criterios = $rubricaCriterios['criterios'];

        $juezUsuario = $this->makeJudgeUser(['proyectos.ver', 'calificaciones.crear']);
        $asignacion = $this->makeAsignacion($proyecto, $juezUsuario->juez, $etapa->id, 'escrita');

        $this->calificar($asignacion, $criterios, [8, 9]);

        $response = $this->actingAs($coordinador, 'sanctum')
            ->postJson('/api/calificaciones/consolidar', [
                'proyecto_id' => $proyecto->id,
                'etapa_id' => $etapa->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'proyectoId',
                'etapaId',
                'juecesPorTipo',
                'notaEscrita',
                'ponderaciones',
                'notaFinal',
            ]);

        $data = $response->json();
        $this->assertIsInt($data['proyectoId']);
        $this->assertIsInt($data['etapaId']);
        $this->assertIsArray($data['juecesPorTipo']);
        $this->assertIsArray($data['ponderaciones']);
        $this->assertIsFloat($data['notaFinal']);
    }

    private function seedBasicData(): void
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);
        Etapa::firstOrCreate(['id' => 1], ['nombre' => 'institucional']);
        Etapa::firstOrCreate(['id' => 2], ['nombre' => 'circuital']);
        Etapa::firstOrCreate(['id' => 3], ['nombre' => 'regional']);
        Modalidad::firstOrCreate(['nombre' => 'Individual']);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function makeJudgeUser(array $permisos = [], ?string $email = null): Usuario
    {
        static $counter = 0;
        $counter++;

        if ($email === null) {
            $email = "juez_test_{$counter}@test.com";
        }

        // Crear usuario
        $usuario = Usuario::create([
            'nombre' => "Juez Test {$counter}",
            'email' => $email,
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();

        $juez = Juez::create([
            'nombre' => "Juez Test {$counter}",
            'cedula' => "9-0000-{$counter}",
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => $email,
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $usuario->setRelation('juez', $juez);

        try {
            $role = Role::firstOrCreate(
                ['name' => 'juez', 'guard_name' => 'sanctum']
            );

            $usuario->assignRole($role);

            foreach ($permisos as $permiso) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permiso, 'guard_name' => 'sanctum']
                );
                $usuario->givePermissionTo($permission);
            }
        } catch (\Exception $e) {
            // Si Spatie no está disponible, continuar sin roles
        }

        return $usuario;
    }

    /**
     * Crea un usuario coordinador con permiso de consolidar
     * 
     * @return Usuario Usuario coordinador
     */
    private function makeCoordinatorUser(): Usuario
    {
        static $counter = 0;
        $counter++;

        $usuario = Usuario::create([
            'nombre' => "Coordinador Test {$counter}",
            'email' => "coord_test_{$counter}@test.com",
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        try {
            // Crear o obtener rol coordinador
            $role = Role::firstOrCreate(
                ['name' => 'coordinador_regional', 'guard_name' => 'sanctum']
            );

            $usuario->assignRole($role);

            // Dar permisos necesarios
            $permisos = [
                'proyectos.ver',
                'calificaciones.ver',
                'calificaciones.consolidar',
            ];

            foreach ($permisos as $permiso) {
                $permission = Permission::firstOrCreate(
                    ['name' => $permiso, 'guard_name' => 'sanctum']
                );
                $usuario->givePermissionTo($permission);
            }
        } catch (\Exception $e) {
            // Si Spatie no está disponible, continuar
        }

        return $usuario;
    }

    /**
     * Crea rúbrica escrita con N criterios
     * 
     * @param int $n Número de criterios
     * @return array ['rubrica' => Rubrica, 'criterios' => Collection]
     */
    private function seedRubricaEscritaConCriterios(int $n = 2): array
    {
        static $counter = 0;
        $counter++;

        $rubrica = Rubrica::create([
            'nombre' => "Evaluación Escrita Test {$counter}",
            'tipo_eval' => 'escrita',
            'ponderacion' => 0.60,
        ]);

        $criterios = collect();
        $peso = 1.0 / $n; // Distribuir peso equitativamente

        for ($i = 1; $i <= $n; $i++) {
            $criterio = Criterio::create([
                'rubrica_id' => $rubrica->id,
                'nombre' => "Criterio {$i}",
                'peso' => $peso,
                'max_puntos' => 10,
            ]);

            $criterios->push($criterio);
        }

        return [
            'rubrica' => $rubrica,
            'criterios' => $criterios,
        ];
    }

    /**
     * Crea una asignación de juez a proyecto
     * 
     * @param Proyecto $proyecto
     * @param Juez $juez
     * @param int $etapaId
     * @param string $tipo
     * @return AsignacionJuez
     */
    private function makeAsignacion(
        Proyecto $proyecto,
        Juez $juez,
        int $etapaId,
        string $tipo = 'escrita'
    ): AsignacionJuez {
        return AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapaId,
            'tipo_eval' => $tipo,
            'asignado_en' => now(),
        ]);
    }

    /**
     * Crea calificaciones para una asignación
     * 
     * @param AsignacionJuez $asignacion
     * @param \Illuminate\Support\Collection $criterios
     * @param array $puntajes Array de puntajes (uno por criterio)
     * @return void
     */
    private function calificar(
        AsignacionJuez $asignacion,
        $criterios,
        array $puntajes
    ): void {
        foreach ($criterios as $index => $criterio) {
            Calificacion::create([
                'asignacion_juez_id' => $asignacion->id,
                'criterio_id' => $criterio->id,
                'puntaje' => $puntajes[$index] ?? 0,
                'comentario' => 'Comentario de prueba',
                'creada_en' => now(),
            ]);
        }
    }
}
