<?php

namespace Tests\Unit\Services;

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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GradeConsolidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private GradeConsolidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradeConsolidationService();
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);
        Modalidad::firstOrCreate(['nombre' => 'Individual']);
    }

    private function crearJuez(): Juez
    {
        static $counter = 0;
        $counter++;

        $usuario = Usuario::create([
            'nombre' => "Juez {$counter}",
            'email' => "juez{$counter}@test.com",
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        return Juez::create([
            'nombre' => "Juez {$counter}",
            'cedula' => "1-0000-{$counter}",
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => "juez{$counter}@test.com",
            'grado_academico' => 'Licenciatura',
            'area__id' => Area::first()->id,
            'usuario_id' => $usuario->id,
        ]);
    }

    private function crearProyecto(): Proyecto
    {
        return Proyecto::create([
            'titulo' => 'Proyecto Test',
            'resumen' => 'Resumen de prueba',
            'area_id' => Area::first()->id,
            'categoria_id' => Categoria::first()->id,
            'institucion_id' => Institucion::first()->id,
            'modalidad_id' => Modalidad::first()->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-' . time(),
        ]);
    }

    private function seedRubricaConCriterios(string $tipoEval, int $numCriterios, float $ponderacion): array
    {
        $rubrica = Rubrica::create([
            'nombre' => "Rúbrica {$tipoEval}",
            'tipo_eval' => $tipoEval,
            'ponderacion' => $ponderacion,
        ]);

        $criterios = [];
        $peso = 1.0 / $numCriterios;
        for ($i = 1; $i <= $numCriterios; $i++) {
            $criterios[] = Criterio::create([
                'rubrica_id' => $rubrica->id,
                'nombre' => "Criterio {$i}",
                'peso' => $peso,
                'max_puntos' => 10,
            ]);
        }

        return $criterios;
    }

    public function test_consolidateProjectStage_con_solo_escrita(): void
    {
        config()->set('grades.min_jueces.institucional', 1);

        $criterios = $this->seedRubricaConCriterios('escrita', 2, 0.60);
        $proyecto = $this->crearProyecto();
        $juez = $this->crearJuez();

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => 1,
            'tipo_eval' => 'escrita',
        ]);

        // Calificar: 8/10 y 9/10 con peso 0.5 cada uno = 0.85
        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $criterios[0]->id,
            'puntaje' => 8,
        ]);
        Calificacion::create([
            'asignacion_juez_id' => $asignacion->id,
            'criterio_id' => $criterios[1]->id,
            'puntaje' => 9,
        ]);

        $resultado = $this->service->consolidateProjectStage($proyecto->id, 1);

        $this->assertEquals($proyecto->id, $resultado['proyectoId']);
        $this->assertEquals(1, $resultado['etapaId']);
        $this->assertEquals(1, $resultado['juecesPorTipo']['escrita']);
        $this->assertEquals(0.85, $resultado['notaEscrita'], '', 0.001);
        $this->assertEquals(0.85, $resultado['notaFinal'], '', 0.001);

        // Verificar en BD
        $resultadoEtapa = ResultadoEtapa::where('proyecto_id', $proyecto->id)
            ->where('etapa_id', 1)
            ->first();
        $this->assertNotNull($resultadoEtapa);
        $this->assertEquals(85.00, $resultadoEtapa->nota_final, '', 0.01);
    }

    public function test_consolidateProjectStage_con_escrita_y_oral_y_ponderaciones(): void
    {
        config()->set('grades.min_jueces.institucional', 1);

        $criteriosEscrita = $this->seedRubricaConCriterios('escrita', 2, 0.60);
        $criteriosOral = $this->seedRubricaConCriterios('oral', 2, 0.40);

        $proyecto = $this->crearProyecto();
        $juezEscrita = $this->crearJuez();
        $juezOral = $this->crearJuez();

        // Asignación escrita
        $asignacionEscrita = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juezEscrita->id,
            'etapa_id' => 1,
            'tipo_eval' => 'escrita',
        ]);

        Calificacion::create(['asignacion_juez_id' => $asignacionEscrita->id, 'criterio_id' => $criteriosEscrita[0]->id, 'puntaje' => 8]);
        Calificacion::create(['asignacion_juez_id' => $asignacionEscrita->id, 'criterio_id' => $criteriosEscrita[1]->id, 'puntaje' => 10]);

        // Asignación oral
        $asignacionOral = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juezOral->id,
            'etapa_id' => 1,
            'tipo_eval' => 'oral',
        ]);

        Calificacion::create(['asignacion_juez_id' => $asignacionOral->id, 'criterio_id' => $criteriosOral[0]->id, 'puntaje' => 6]);
        Calificacion::create(['asignacion_juez_id' => $asignacionOral->id, 'criterio_id' => $criteriosOral[1]->id, 'puntaje' => 8]);

        $resultado = $this->service->consolidateProjectStage($proyecto->id, 1);

        // Escrita: (8/10 * 0.5) + (10/10 * 0.5) = 0.9
        // Oral: (6/10 * 0.5) + (8/10 * 0.5) = 0.7
        // Final: (0.9 * 0.6) + (0.7 * 0.4) = 0.82
        $this->assertEquals(0.9, $resultado['notaEscrita'], '', 0.001);
        $this->assertEquals(0.7, $resultado['notaOral'], '', 0.001);
        $this->assertEquals(0.82, $resultado['notaFinal'], '', 0.001);
    }

    public function test_checkCardinality_lanza_exception_cuando_no_cumple(): void
    {
        config()->set('grades.min_jueces.institucional', 3);

        $proyecto = $this->crearProyecto();
        $juez = $this->crearJuez();

        // Solo 1 juez, pero se requieren 3
        AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => 1,
            'tipo_eval' => 'escrita',
        ]);

        $this->expectException(ValidationException::class);
        $this->service->checkCardinality($proyecto->id, 1);
    }

    public function test_isStageClosed_por_defecto_false(): void
    {
        $isClosed = $this->service->isStageClosed(1, 1);
        $this->assertFalse($isClosed);
    }
}
