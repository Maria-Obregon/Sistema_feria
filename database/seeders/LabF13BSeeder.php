<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AsignacionJuez;
use App\Models\Categoria;
use App\Models\Circuito;
use App\Models\Etapa;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\Rubrica;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class LabF13BSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Asegurar categoría "Mi experiencia científica"
            $categoria = Categoria::query()
                ->whereRaw('LOWER(nombre) LIKE LOWER(?)', ['%experiencia cient%'])
                ->first();
            if (! $categoria) {
                $categoria = Categoria::create(['nombre' => 'Mi experiencia científica']);
            }

            // Etapa institucional
            $etapaId = Etapa::idPorNombre('INSTITUCIONAL') ?? Etapa::idPorNombre(Etapa::INSTITUCIONAL);
            if (! $etapaId) {
                $etapa = Etapa::create(['nombre' => Etapa::INSTITUCIONAL]);
                $etapaId = $etapa->id;
            }

            // Usuario juez con rol
            $usuario = Usuario::query()->firstOrCreate(
                ['email' => 'juez.lab@example.com'],
                [
                    'nombre' => 'Juez',
                    'password' => Hash::make('secret1234'),
                    'activo' => true,
                ]
            );

            // Rol juez (Spatie) - solo asignación de rol, sin permisos explícitos (tests usan withoutMiddleware)
            $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
            if (! $usuario->hasRole('juez')) {
                $usuario->assignRole($role);
            }

            // Juez
            $juez = Juez::query()->firstOrCreate([
                'usuario_id' => $usuario->id,
            ], [
                'nombre' => $usuario->nombre,
                'cedula' => 'LAB-0001',
                'correo' => $usuario->email,
            ]);

            // Área requerida por proyectos
            $area = Area::query()->first() ?: Area::create(['nombre' => 'Ciencias']);

            // Institución requerida por proyecto
            $regional = \App\Models\Regional::query()->first() ?: \App\Models\Regional::create(['nombre' => 'Regional Lab']);
            $circuito = Circuito::query()->first() ?: Circuito::create(['nombre' => 'Circuito Lab', 'codigo' => 'CLAB', 'regional_id' => $regional->id]);
            $institucion = Institucion::query()->first() ?: Institucion::create([
                'nombre' => 'Institución Lab',
                'codigo_presupuestario' => 'ILAB-001',
                'modalidad' => 'Secundaria',
                'tipo' => 'publica',
                'regional_id' => $regional->id,
                'circuito_id' => $circuito->id,
            ]);

            // Proyecto
            $proyecto = Proyecto::create([
                'titulo' => 'Proyecto F13B de Laboratorio',
                'resumen' => 'Resumen de prueba',
                'area_id' => $area->id,
                'categoria_id' => $categoria->id,
                'institucion_id' => $institucion->id,
                'modalidad_id' => (Modalidad::query()->first() ?: Modalidad::create(['nombre' => 'Experimental']))->id,
                'etapa_id' => $etapaId,
                'estado' => 'en_evaluacion',
            ]);

            // Asegurar rúbrica F13B existente (sembrada por RubricaMiExperienciaSeeder)
            $rubrica = Rubrica::query()
                ->where('tipo_eval', 'exposicion')
                ->whereRaw('LOWER(nombre) LIKE LOWER(?)', ['%F13B%'])
                ->first();
            if (! $rubrica) {
                Log::info('LabF13BSeeder: Rúbrica F13B no encontrada, ejecute RubricaMiExperienciaSeeder antes.');
            }

            // Upsert de rubrica_asignacion para la categoría "Mi experiencia científica" en etapas reales
            if ($rubrica && $categoria) {
                $inst = Etapa::idPorNombre(Etapa::INSTITUCIONAL);
                $circ = Etapa::idPorNombre(Etapa::CIRCUITAL);
                $reg = Etapa::idPorNombre(Etapa::REGIONAL);
                $etapas = array_values(array_filter([$inst, $circ, $reg]));
                foreach ($etapas as $eId) {
                    DB::table('rubrica_asignacion')->updateOrInsert([
                        'modalidad_id' => null,
                        'categoria_id' => $categoria->id,
                        'nivel_id' => null,
                        'etapa_id' => $eId,
                        'tipo_eval' => 'exposicion',
                    ], [
                        'rubrica_id' => $rubrica->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]);
                }
            } elseif (! $categoria) {
                Log::info('LabF13BSeeder: categoría "Mi experiencia científica" no encontrada para upsert de rubrica_asignacion.');
            }

            // Asignación juez (exposición)
            $asignacion = AsignacionJuez::firstOrCreate([
                'proyecto_id' => $proyecto->id,
                'juez_id' => $juez->id,
                'etapa_id' => $etapaId,
                'tipo_eval' => 'exposicion',
            ]);

            // Espejo en tabla legacy con el mismo ID para compatibilidad SQLite (FK legacy)
            if (Schema::hasTable('asignacion_juez')) {
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
        });
    }
}
