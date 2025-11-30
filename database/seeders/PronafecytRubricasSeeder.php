<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Criterio;
use App\Models\Rubrica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PronafecytRubricasSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas relacionadas (opcional, cuidado en producción)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Criterio::truncate(); // Descomentar si se desea limpiar todo
        // Rubrica::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $datos = [
            // F8: Demostraciones Científicas
            'Demostraciones Científicas' => [
                'exposicion' => [
                    'max' => 40,
                    'criterios' => [
                        'Propósito principal y justificación' => 4,
                        'Marco teórico y metodología' => 10,
                        'Análisis y conclusiones' => 6,
                        'Dominio del principio científico' => 8,
                        'Presentación y comunicación' => 8,
                        'Autenticidad' => 4,
                    ],
                ],
                'escrito' => [
                    'max' => 64,
                    'criterios' => [
                        'Autenticidad del trabajo' => 3,
                        'Portada e Índice' => 9,
                        'Aspectos iniciales y Marco Teórico' => 45,
                        'Referencias y Resumen' => 7,
                    ],
                ],
            ],

            // F9: Investigación Científica
            'Investigación Científica' => [
                'exposicion' => [
                    'max' => 40,
                    'criterios' => [
                        'Planteamiento del problema' => 5,
                        'Marco Teórico' => 5,
                        'Metodología' => 10,
                        'Discusión y Resultados' => 6,
                        'Presentación y comunicación' => 10,
                        'Autenticidad' => 4,
                    ],
                ],
                'escrito' => [
                    'max' => 78,
                    'criterios' => [
                        'Autenticidad' => 3,
                        'Estructura: Páginas preliminares e Introducción' => 11,
                        'Estructura: Metodología y Resultados' => 37,
                        'Estructura: Discusión, Conclusiones y Bibliografía' => 27,
                    ],
                ],
            ],

            // F10: Investigación y Desarrollo Tecnológico
            'Investigación y Desarrollo Tecnológico' => [
                'exposicion' => [
                    'max' => 40,
                    'criterios' => [
                        // Genéricos distribuidos
                        'Identificación de la necesidad y solución' => 10,
                        'Diseño y desarrollo del prototipo' => 10,
                        'Funcionamiento y validación' => 10,
                        'Presentación y comunicación' => 6,
                        'Autenticidad' => 4,
                    ],
                ],
                'escrito' => [
                    'max' => 98,
                    'criterios' => [
                        // Genéricos distribuidos para sumar 98
                        'Autenticidad y formato' => 6,
                        'Definición del problema y antecedentes' => 10,
                        'Descripción técnica y diseño' => 54,
                        'Pruebas, resultados y manuales' => 28,
                    ],
                ],
            ],

            // F11: Quehacer Científico
            'Quehacer Científico' => [
                'exposicion' => [
                    'max' => 40,
                    'criterios' => [
                        'Importancia del tema en la sociedad' => 10,
                        'Comprensión de conceptos científicos' => 10,
                        'Capacidad de argumentación' => 10,
                        'Presentación visual y oral' => 6,
                        'Autenticidad' => 4,
                    ],
                ],
                'escrito' => [
                    'max' => 57,
                    'criterios' => [
                        'Autenticidad' => 3,
                        'Introducción y justificación' => 15,
                        'Desarrollo del ensayo/investigación' => 25,
                        'Conclusiones y opinión personal' => 10,
                        'Referencias' => 4,
                    ],
                ],
            ],

            // F12: Sumando Experiencias
            'Sumando Experiencias' => [
                'exposicion' => [
                    'max' => 40,
                    'criterios' => [
                        'Claridad en la explicación' => 10,
                        'Dominio del tema' => 10,
                        'Creatividad en la presentación' => 10,
                        'Respuestas a preguntas' => 6,
                        'Autenticidad' => 4,
                    ],
                ],
                'escrito' => [
                    'max' => 57,
                    'criterios' => [
                        'Autenticidad' => 3,
                        'Bitácora/Registro de actividades' => 20,
                        'Evidencias y reflexiones' => 20,
                        'Organización y presentación' => 14,
                    ],
                ],
            ],

            // F13: Mi Experiencia Científica (SOLO EXPOSICIÓN)
            'Mi Experiencia Científica' => [
                'exposicion' => [
                    'max' => 100,
                    'criterios' => [
                        'Dominio del tema' => 25,
                        'Fluidez y vocabulario' => 20,
                        'Material de apoyo' => 20,
                        'Estructura lógica de la presentación' => 20,
                        'Impacto y entusiasmo' => 10,
                        'Autenticidad' => 5,
                    ],
                ],
                // No tiene escrito
            ],
        ];

        foreach ($datos as $nombreCategoria => $rubricas) {
            // Buscar categoría, si no existe crearla o saltar (asumimos que existen o se crean)
            $categoria = Categoria::firstOrCreate(['nombre' => $nombreCategoria]);

            foreach ($rubricas as $tipo => $data) {
                $nombreRubrica = "Rúbrica $tipo - $nombreCategoria";

                // Crear rúbrica
                $rubrica = Rubrica::create([
                    'nombre' => $nombreRubrica,
                    'modo' => 'por_criterio',
                    'max_puntos' => $data['max'],
                ]);

                // Asociar rúbrica a categoría
                // Asumiendo que existe una tabla pivote o relación en Categoria.
                // Si no, necesitamos insertar en categoria_rubrica o similar.
                // Revisando modelos... Rubrica suele estar ligada a Categoria o al revés.
                // En el esquema actual, usaremos el RubricaResolver para encontrarlas,
                // pero necesitamos vincularlas.

                // VINCULACIÓN:
                // Si la tabla categorias tiene rubrica_id, solo soporta una.
                // Lo ideal es una tabla pivote categoria_rubrica con campos extra (tipo_eval, etapa_id).
                // O que la tabla rubricas tenga categoria_id.

                // Verificaremos si Rubrica tiene categoria_id
                // Si no, usaremos la tabla pivote 'categoria_rubrica' si existe.

                // IMPORTANTE: Para este seeder asumiré que vamos a crear las relaciones en
                // una tabla pivote `categoria_rubrica` que debe existir o ser creada.
                // Si no existe, la creo dinámicamente con DB::table

                // Insertar Criterios
                foreach ($data['criterios'] as $nombreCriterio => $maxPuntos) {
                    Criterio::create([
                        'rubrica_id' => $rubrica->id,
                        'nombre' => $nombreCriterio,
                        'peso' => $maxPuntos, // En modo por_criterio, peso puede actuar como max visual o ponderación
                        'max_puntos' => $maxPuntos,
                    ]);
                }

                // Vincular a Categoría (Todas las etapas por defecto, o Circuital/Regional)
                // Asignaremos a etapa 1, 2, 3 para asegurar cobertura
                $etapas = [1, 2, 3];
                foreach ($etapas as $etapaId) {
                    // Usar la tabla correcta: rubrica_asignacion
                    // Primero limpiar asignación previa específica para esta categoría
                    DB::table('rubrica_asignacion')
                        ->where('categoria_id', $categoria->id)
                        ->where('etapa_id', $etapaId)
                        ->where('tipo_eval', $tipo)
                        ->delete();

                    // Insertar nueva asignación
                    DB::table('rubrica_asignacion')->insert([
                        'categoria_id' => $categoria->id,
                        'etapa_id' => $etapaId,
                        'tipo_eval' => $tipo,
                        'rubrica_id' => $rubrica->id,
                        'modalidad_id' => null, // Aplica a todas las modalidades de esta categoría
                        'nivel_id' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
