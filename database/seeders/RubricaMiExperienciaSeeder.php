<?php

namespace Database\Seeders;

use App\Models\Criterio;
use App\Models\Rubrica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RubricaMiExperienciaSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $rubrica = Rubrica::query()->firstOrCreate(
                [
                    'nombre' => 'F13B — Mi Experiencia Científica (Exposición 100%)',
                    'tipo_eval' => 'exposicion',
                ],
                [
                    'modo' => 'por_criterio',
                    'ponderacion' => 1.00,
                    'max_total' => 40,
                ]
            );

            // Alinear configuración en caso de existir previamente con otros valores
            $needsUpdate = false;
            if ($rubrica->modo !== 'por_criterio') {
                $rubrica->modo = 'por_criterio';
                $needsUpdate = true;
            }
            if ((int) $rubrica->max_total !== 40) {
                $rubrica->max_total = 40;
                $needsUpdate = true;
            }
            if ((float) $rubrica->ponderacion !== 1.0) {
                $rubrica->ponderacion = 1.0;
                $needsUpdate = true;
            }
            if ($needsUpdate) {
                $rubrica->save();
            }

            // Sembrar criterios oficiales (peso 1.0)
            $criterios = [
                // A. Propósito/justificación — total 4
                'El propósito es explicado con claridad y coherencia, así como la importancia de la investigación y sus posibles consecuencias.' => 2,
                'Las preguntas generales están relacionadas con la demostración.' => 1,
                'La demostración corresponde a un proceso o principio científico o tecnológico.' => 1,
                // B. Marco teórico y metodología — total 10
                'Existe familiaridad y manejo de los contenidos de las fuentes consultadas.' => 1,
                'Existe claridad en los conceptos utilizados.' => 1,
                'La organización de la investigación demuestra una metodología de trabajo.' => 2,
                'Selecciona instrumentos adecuados para su demostración.' => 2,
                'Utiliza recursos materiales en forma ingeniosa y creativa.' => 2,
                'Los recursos y desechos generados son utilizados considerando la sostenibilidad ambiental.' => 2,
                // C. Análisis y conclusiones — total 6
                'Interpretación de resultados obtenidos en la demostración.' => 2,
                'Explica cómo la demostración ilustra el concepto o principio seleccionado.' => 2,
                'Contrasta o compara los resultados con la información consultada.' => 1,
                'Complementa con reflexiones personales.' => 1,
                // D. Dominio del principio/proceso — total 8
                'Explica el principio o proceso científico/tecnológico.' => 2,
                'Evidencia comprensión de los conceptos de la demostración.' => 3,
                'Todas las personas estudiantes participan y dominan el tema.' => 3,
                // E. Presentación y comunicación — total 8
                'El cartel apoya la comunicación en forma fluida.' => 2,
                'El material expuesto se relaciona con el trabajo.' => 2,
                'Claridad en la comunicación y uso de lenguaje científico acorde.' => 2,
                'Capacidad de síntesis.' => 2,
                // F. Autenticidad — total 4
                'El cartel/material evidencia autoría de estudiantes.' => 2,
                'Originalidad en la elaboración del material.' => 2,
            ];

            // Eliminar criterio temporal si existiera
            DB::table('criterios')->where('rubrica_id', $rubrica->id)->where('nombre', 'Indicador TEMP')->delete();

            foreach ($criterios as $nombre => $max) {
                Criterio::query()->firstOrCreate(
                    ['rubrica_id' => $rubrica->id, 'nombre' => $nombre],
                    ['peso' => 1.0, 'max_puntos' => (int) $max]
                );
            }

            // Intentar asignaciones por categoría "Mi experiencia científica" en cada etapa
            $categoria = DB::table('categorias')->where('nombre', 'like', '%experiencia cient%')->first();
            if (! $categoria) {
                Log::info('RubricaMiExperienciaSeeder: categoría "Mi experiencia científica" no encontrada; no se crean asignaciones.');

                return;
            }

            // Resolver etapas por nombre (evitar IDs mágicos)
            $inst = \App\Models\Etapa::idPorNombre(\App\Models\Etapa::INSTITUCIONAL);
            $circ = \App\Models\Etapa::idPorNombre(\App\Models\Etapa::CIRCUITAL);
            $reg = \App\Models\Etapa::idPorNombre(\App\Models\Etapa::REGIONAL);

            foreach ([$inst, $circ, $reg] as $etapaId) {
                if (! $etapaId) {
                    continue;
                }

                DB::table('rubrica_asignacion')->updateOrInsert(
                    [
                        'modalidad_id' => null,
                        'categoria_id' => $categoria->id,
                        'nivel_id' => null,
                        'etapa_id' => $etapaId,
                        'tipo_eval' => 'exposicion',
                    ],
                    [
                        'rubrica_id' => $rubrica->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });
    }
}
