<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AsignacionJuez;
use App\Models\Categoria;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestProyectosVariadosSeeder extends Seeder
{
    public function run(): void
    {
        // ... (código anterior del juez e institución)
        $juez = Juez::with('usuario')->first();
        if (! $juez) {
            $userJuez = User::role('juez')->first();
            if (! $userJuez) {
                return;
            }
            $juez = Juez::create(['usuario_id' => $userJuez->id, 'cargo' => 'Test', 'especialidad' => 'Gen']);
        }
        $userJuez = $juez->usuario;
        $this->command->info("✅ Juez: {$userJuez->email}");

        $institucion = Institucion::firstOrCreate(
            ['codigo_presupuestario' => 'TEST-001'],
            [
                'nombre' => 'Institución de Prueba',
                'regional_id' => 1, // Asumiendo que existe regional 1
                'circuito_id' => 1, // Asumiendo que existe circuito 1
                'modalidad' => 'Académica',
                'tipo' => 'Pública',
            ]
        );

        // Buscar dependencias obligatorias
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Científica']);
        $area = Area::firstOrCreate(['nombre' => 'Ciencias Generales']);

        $categoriasNombres = [
            'Demostraciones Científicas',
            'Investigación Científica',
            'Investigación y Desarrollo Tecnológico',
            'Quehacer Científico',
            'Sumando Experiencias',
            'Mi Experiencia Científica',
        ];

        $etapaId = 1;

        foreach ($categoriasNombres as $nombreCat) {
            $categoria = Categoria::where('nombre', $nombreCat)->first();
            if (! $categoria) {
                continue;
            }

            // Crear o buscar Proyecto para evitar duplicados
            $proyecto = Proyecto::firstOrCreate([
                'titulo' => "Proyecto Test - $nombreCat",
                'categoria_id' => $categoria->id,
                'institucion_id' => $institucion->id,
                'etapa_id' => $etapaId,
            ], [
                'resumen' => "Proyecto generado automáticamente para probar la categoría $nombreCat",
                'modalidad_id' => $modalidad->id,
                'area_id' => $area->id,
                'estado' => 'inscrito',
                'codigo' => 'TEST-'.rand(1000, 9999).'-'.substr(str_replace(' ', '', $nombreCat), 0, 3),
            ]);

            $esF13 = $nombreCat === 'Mi Experiencia Científica';

            // Limpiar asignaciones previas para asegurar estado limpio
            AsignacionJuez::where('proyecto_id', $proyecto->id)->delete();

            if ($esF13) {
                AsignacionJuez::create([
                    'juez_id' => $juez->id,
                    'proyecto_id' => $proyecto->id,
                    'etapa_id' => $etapaId,
                    'tipo_eval' => 'exposicion',
                    'finalizada_at' => null,
                ]);
            } else {
                AsignacionJuez::create([
                    'juez_id' => $juez->id,
                    'proyecto_id' => $proyecto->id,
                    'etapa_id' => $etapaId,
                    'tipo_eval' => 'escrito',
                    'finalizada_at' => null,
                ]);

                AsignacionJuez::create([
                    'juez_id' => $juez->id,
                    'proyecto_id' => $proyecto->id,
                    'etapa_id' => $etapaId,
                    'tipo_eval' => 'exposicion',
                    'finalizada_at' => null,
                ]);
            }
            $this->command->info("   -> Asignaciones regeneradas para: {$proyecto->titulo}");
        }
    }
}
