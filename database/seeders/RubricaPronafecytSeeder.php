<?php

namespace Database\Seeders;

use App\Models\Criterio;
use App\Models\Rubrica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricaPronafecytSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $definicion = [
                // Ejemplo simplificado (extensible): F1/F2 por tipo_eval
                [
                    'nombre' => 'PRONAFECYT - Escrito Base',
                    'tipo_eval' => 'escrito',
                    'criterios' => [
                        ['nombre' => 'Redacción y ortografía', 'peso' => 0.2, 'max_puntos' => 10],
                        ['nombre' => 'Marco teórico',          'peso' => 0.3, 'max_puntos' => 10],
                        ['nombre' => 'Metodología',             'peso' => 0.5, 'max_puntos' => 10],
                    ],
                    'asignaciones' => [
                        // Asignación genérica (cualquier modalidad/categoría/nivel) por etapa
                        ['etapa_id' => 1, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                        ['etapa_id' => 2, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                        ['etapa_id' => 3, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                    ],
                ],
                [
                    'nombre' => 'PRONAFECYT - Exposición Base',
                    'tipo_eval' => 'exposicion',
                    'criterios' => [
                        ['nombre' => 'Claridad de la exposición', 'peso' => 0.4, 'max_puntos' => 10],
                        ['nombre' => 'Dominio del tema',          'peso' => 0.4, 'max_puntos' => 10],
                        ['nombre' => 'Recursos y materiales',     'peso' => 0.2, 'max_puntos' => 10],
                    ],
                    'asignaciones' => [
                        ['etapa_id' => 1, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                        ['etapa_id' => 2, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                        ['etapa_id' => 3, 'modalidad_id' => null, 'categoria_id' => null, 'nivel_id' => null],
                    ],
                ],
            ];

            foreach ($definicion as $def) {
                /** @var Rubrica $rubrica */
                $rubrica = Rubrica::query()->firstOrCreate(
                    ['nombre' => $def['nombre'], 'tipo_eval' => $def['tipo_eval']],
                    ['ponderacion' => 0.5]
                );

                foreach ($def['criterios'] as $c) {
                    Criterio::query()->firstOrCreate(
                        ['rubrica_id' => $rubrica->id, 'nombre' => $c['nombre']],
                        ['peso' => $c['peso'], 'max_puntos' => $c['max_puntos']]
                    );
                }

                foreach ($def['asignaciones'] as $asig) {
                    $q = DB::table('rubrica_asignacion')
                        ->where('etapa_id', $asig['etapa_id'])
                        ->where('tipo_eval', $def['tipo_eval']);
                    foreach (['modalidad_id', 'categoria_id', 'nivel_id'] as $k) {
                        if (is_null($asig[$k])) {
                            $q->whereNull($k);
                        } else {
                            $q->where($k, $asig[$k]);
                        }
                    }
                    $exists = $q->exists();

                    if (! $exists) {
                        DB::table('rubrica_asignacion')->insert([
                            'modalidad_id' => $asig['modalidad_id'],
                            'categoria_id' => $asig['categoria_id'],
                            'nivel_id' => $asig['nivel_id'],
                            'etapa_id' => $asig['etapa_id'],
                            'tipo_eval' => $def['tipo_eval'],
                            'rubrica_id' => $rubrica->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
