<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoJuezAsignacionSeeder extends Seeder
{
    public function run(): void
    {
        // Etapa INSTITUCIONAL
        $etapaId = \App\Models\Etapa::idPorNombre(\App\Models\Etapa::INSTITUCIONAL);
        if (! $etapaId) {
            $this->command->info('DemoJuezAsignacionSeeder: Etapa INSTITUCIONAL no encontrada.');

            return;
        }

        // Área "Ciencias Generales"
        $area = \App\Models\Area::firstOrCreate(['nombre' => 'Ciencias Generales']);

        // Modalidad "Experimental" (nivel_id NULL)
        $modalidad = \App\Models\Modalidad::query()
            ->where('nombre', 'Experimental')
            ->whereNull('nivel_id')
            ->first();
        if (! $modalidad) {
            $modalidad = \App\Models\Modalidad::create(['nombre' => 'Experimental', 'nivel_id' => null]);
        }

        // Categoría preferida "Mi experiencia científica" si existe; si no, la primera disponible
        $categoria = \App\Models\Categoria::query()
            ->where('nombre', 'like', '%experiencia cient%')
            ->first();
        if (! $categoria) {
            $categoria = \App\Models\Categoria::query()->first();
            if (! $categoria) {
                $this->command->info('DemoJuezAsignacionSeeder: No hay categorías disponibles.');

                return;
            }
        }

        // Institución Demo
        $institucion = \App\Models\Institucion::firstOrCreate(
            ['nombre' => 'Institución Demo Jueces'],
            [
                'codigo_presupuestario' => 'IDJ-001',
                'tipo' => 'Colegio',
                'modalidad' => 'Académica',
                'regional_id' => 1,
                'circuito_id' => 1,
                'activo' => true,
            ]
        );

        // Usuario / Juez
        $usuario = \App\Models\Usuario::where('email', 'juez.demo@prueba.local')->first();
        if (! $usuario) {
            $this->command->info('DemoJuezAsignacionSeeder: Usuario juez.demo@prueba.local no existe. Ejecute DemoJuezLoginSeeder primero.');

            return;
        }
        $juez = \App\Models\Juez::where('usuario_id', $usuario->id)->first();
        if (! $juez) {
            $this->command->info('DemoJuezAsignacionSeeder: Juez asociado al usuario demo no existe. Ejecute DemoJuezLoginSeeder primero.');

            return;
        }

        // Proyecto Demo
        DB::table('proyectos')->updateOrInsert(
            ['titulo' => 'Proyecto Demo Jueces'],
            [
                'codigo' => 'PDJ-001',
                'resumen' => 'Proyecto de prueba para flujo de jueces',
                'estado' => 'en_evaluacion',
                'institucion_id' => $institucion->id,
                'etapa_id' => $etapaId,
                'area_id' => $area->id,
                'modalidad_id' => $modalidad->id,
                'categoria_id' => $categoria->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        $proyecto = \App\Models\Proyecto::where('titulo', 'Proyecto Demo Jueces')->first();
        if (! $proyecto) {
            $this->command->info('DemoJuezAsignacionSeeder: No se pudo recuperar el proyecto de demo.');

            return;
        }

        // Asignación en asignaciones_jueces (exposición)
        DB::table('asignaciones_jueces')->updateOrInsert(
            [
                'proyecto_id' => $proyecto->id,
                'juez_id' => $juez->id,
                'etapa_id' => $etapaId,
            ],
            [
                'tipo_eval' => 'exposicion',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
        $asignacion = DB::table('asignaciones_jueces')
            ->where('proyecto_id', $proyecto->id)
            ->where('juez_id', $juez->id)
            ->where('etapa_id', $etapaId)
            ->first();

        // Asegurar visibilidad en UI: proyecto en evaluación y asignación no finalizada
        DB::table('proyectos')
            ->where('id', $proyecto->id)
            ->update(['estado' => 'en_evaluacion', 'updated_at' => now()]);
        if ($asignacion) {
            DB::table('asignaciones_jueces')
                ->where('id', $asignacion->id)
                ->update(['finalizada_at' => null, 'updated_at' => now()]);
        }

        // Espejo legacy si existe
        if ($asignacion && Schema::hasTable('asignacion_juez')) {
            DB::table('asignacion_juez')->updateOrInsert(
                ['id' => $asignacion->id],
                [
                    'proyecto_id' => $proyecto->id,
                    'juez_id' => $juez->id,
                    'etapa_id' => $etapaId,
                    'tipo_eval' => 'exposicion',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Verificar rúbrica asignada para la categoría del proyecto (no crear)
        $rubAsig = DB::table('rubrica_asignacion')
            ->where('etapa_id', $etapaId)
            ->where('tipo_eval', 'exposicion')
            ->where('categoria_id', $categoria->id)
            ->first();
        if (! $rubAsig) {
            $this->command->info('DemoJuezAsignacionSeeder: No hay rubrica_asignacion para exposición en esta categoría/etapa.');
        }

        // Salida
        $modalidadNombre = DB::table('modalidades')->where('id', $modalidad->id)->value('nombre');
        $categoriaNombre = DB::table('categorias')->where('id', $categoria->id)->value('nombre');
        $this->command->info('Proyecto demo listo: proyecto_id='.$proyecto->id.' asignacion_id='.($asignacion->id ?? 'N/A').' etapa=Institucional categoria='.$categoriaNombre.' modalidad='.$modalidadNombre);
    }
}
