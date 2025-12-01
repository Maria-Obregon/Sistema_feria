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
            'DEMOSTRACIONES CIENTÍFICAS Y TECNOLÓGICAS' => [
                'exposicion' => [
                    'max' => 40,
                    'nombre_rubrica' => 'Demostraciones Científicas - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'SECCIÓN A: Propósito principal de la demostración e importancia del tema (4 pts)', 'nombre' => 'El propósito es explicado con claridad y coherencia, así como la importancia', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN A: Propósito principal de la demostración e importancia del tema (4 pts)', 'nombre' => 'Las preguntas generales están relacionadas con la demostración', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN A: Propósito principal de la demostración e importancia del tema (4 pts)', 'nombre' => 'La demostración corresponde a un proceso o principio científico o tecnológico', 'max' => 1.00],

                        // SECCIÓN B
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'Existe familiaridad y manejo de los contenidos de las fuentes consultadas', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'Existe claridad en los conceptos utilizados', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'La organización de la investigación demuestra una metodología de trabajo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'Selecciona los instrumentos adecuados para su demostración', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'Utiliza recursos materiales en forma ingeniosa y creativa', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico y metodología (10 pts)', 'nombre' => 'Los recursos y desechos generados son utilizados considerando la sostenibilidad', 'max' => 2.00],

                        // SECCIÓN C
                        ['seccion' => 'SECCIÓN C: Análisis y conclusiones (Logros obtenidos) (6 pts)', 'nombre' => 'Realiza la interpretación de los resultados obtenidos en la demostración', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Análisis y conclusiones (Logros obtenidos) (6 pts)', 'nombre' => 'Explica cómo la demostración ilustra el concepto o principio seleccionado', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Análisis y conclusiones (Logros obtenidos) (6 pts)', 'nombre' => 'Contrasta o compara los resultados obtenidos con la información consultada', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN C: Análisis y conclusiones (Logros obtenidos) (6 pts)', 'nombre' => 'Complementa la comparación con reflexiones personales', 'max' => 1.00],

                        // SECCIÓN D
                        ['seccion' => 'SECCIÓN D: Dominio del principio o proceso científico o tecnológico (8 pts)', 'nombre' => 'Explica el principio, proceso científico o tecnológico', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN D: Dominio del principio o proceso científico o tecnológico (8 pts)', 'nombre' => 'Evidencia comprensión de los conceptos que fundamentan la demostración', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN D: Dominio del principio o proceso científico o tecnológico (8 pts)', 'nombre' => 'Todas las personas estudiantes participan en la exposición y dominan el tema', 'max' => 3.00],

                        // SECCIÓN E
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica o tecnológica (8 pts)', 'nombre' => 'El cartel presentado apoya la comunicación en forma fluida', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica o tecnológica (8 pts)', 'nombre' => 'El material expuesto tiene relación con el trabajo de investigación', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica o tecnológica (8 pts)', 'nombre' => 'Existe claridad en la comunicación y se utiliza lenguaje científico acorde', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica o tecnológica (8 pts)', 'nombre' => 'Existe capacidad de síntesis para realizar la comunicación', 'max' => 2.00],

                        // SECCIÓN F
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'El cartel y material expuesto da muestras de realización propia', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'Existe originalidad en la elaboración del material', 'max' => 2.00],
                    ],
                ],
                'escrito' => [
                    'max' => 64,
                    'nombre_rubrica' => 'Demostraciones Científicas - Informe Escrito',
                    'criterios' => [
                        // SECCIÓN 1: I PARTE Y PÁGINA 1
                        ['seccion' => 'I. Autenticidad', 'nombre' => 'Estilo, estructura y vocabulario sugieren elaboración propia', 'max' => 3.00],
                        ['seccion' => 'I. Autenticidad', 'nombre' => 'No plagio: Da crédito a la producción intelectual ajena', 'max' => 3.00],

                        ['seccion' => 'II. Estructura (Página 1)', 'nombre' => 'Portada: Contiene los elementos oficiales', 'max' => 1.00],
                        ['seccion' => 'II. Estructura (Página 1)', 'nombre' => 'Título: Establece una idea general del trabajo', 'max' => 1.00],
                        ['seccion' => 'II. Estructura (Página 1)', 'nombre' => 'Índice: Indica las secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'Introducción', 'nombre' => 'Anota las ideas previas que motivan el proyecto', 'max' => 3.00],
                        ['seccion' => 'Introducción', 'nombre' => 'Señala la importancia del tema relacionado', 'max' => 3.00],
                        ['seccion' => 'Introducción', 'nombre' => 'Indica las preguntas generales relacionadas', 'max' => 3.00],
                        ['seccion' => 'Introducción', 'nombre' => 'Explica el propósito principal de la demostración', 'max' => 3.00],

                        ['seccion' => 'Marco Teórico', 'nombre' => 'Describe palabras claves y conceptos técnicos', 'max' => 3.00],
                        ['seccion' => 'Marco Teórico', 'nombre' => 'Registra información adicional de fuentes', 'max' => 3.00],
                        ['seccion' => 'Marco Teórico', 'nombre' => 'Cita o hace referencia a las fuentes (formato)', 'max' => 3.00],

                        ['seccion' => 'Metodología', 'nombre' => 'Explica pasos, procedimientos o técnicas', 'max' => 3.00],
                        ['seccion' => 'Metodología', 'nombre' => 'Narra los aportes propios que enriquecen', 'max' => 3.00],
                        ['seccion' => 'Metodología', 'nombre' => 'Indica si presenta cambios de la fuente original', 'max' => 1.00],
                        ['seccion' => 'Metodología', 'nombre' => 'Anota lista de recursos tecnológicos/materiales', 'max' => 1.00],
                        ['seccion' => 'Metodología', 'nombre' => 'Describe manejo de residuos y sostenibilidad', 'max' => 3.00],

                        ['seccion' => 'Resultados y Conclusiones', 'nombre' => 'Analiza o interpreta los resultados obtenidos', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones', 'nombre' => 'Contrasta resultados con información consultada', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones', 'nombre' => 'Cita fuentes en el análisis para evitar plagio', 'max' => 1.00],
                        ['seccion' => 'Resultados y Conclusiones', 'nombre' => 'Establece conclusiones obtenidas', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones', 'nombre' => 'Aporta evidencias de comunicación (fotos/listas)', 'max' => 3.00],

                        // SECCIÓN 3: PÁGINA 3 - FINAL
                        ['seccion' => 'Referencias y Resumen', 'nombre' => 'Referencias: Utiliza mínimo cuatro fuentes', 'max' => 1.00],
                        ['seccion' => 'Referencias y Resumen', 'nombre' => 'Referencias: Fuentes confiables (< 10 años)', 'max' => 3.00],
                        ['seccion' => 'Referencias y Resumen', 'nombre' => 'Referencias: Formato bibliográfico consistente', 'max' => 3.00],
                        ['seccion' => 'Referencias y Resumen', 'nombre' => 'Resumen: Síntesis de aspectos relevantes', 'max' => 3.00],
                    ],
                ],
            ],

            // ... (rest of the array remains unchanged, I'm only targeting the start of the array and the loop logic if possible, but replace_file_content works on chunks. I'll do the loop logic in a separate call or try to fit it if the file structure allows. The loop is at the bottom. I'll do two edits.)

            // F9: Investigación Científica
            'INVESTIGACIÓN CIENTÍFICA' => [
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
            'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO' => [
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
            'QUEHACER CIENTÍFICO Y TECNOLÓGICO' => [
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
            'SUMANDO EXPERIENCIAS CIENTÍFICAS' => [
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
            'MI EXPERIENCIA CIENTÍFICA' => [
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
                $nombreRubrica = $data['nombre_rubrica'] ?? "Rúbrica $tipo - $nombreCategoria";

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
                foreach ($data['criterios'] as $key => $val) {
                    $nombre = '';
                    $max = 0;
                    $seccion = null;

                    if (is_array($val)) {
                        // Nuevo formato con sección
                        $nombre = $val['nombre'];
                        $max = $val['max'];
                        $seccion = $val['seccion'] ?? null;
                    } else {
                        // Formato anterior: "Nombre" => Puntos
                        $nombre = $key;
                        $max = $val;
                    }

                    Criterio::create([
                        'rubrica_id' => $rubrica->id,
                        'nombre' => $nombre,
                        'peso' => $max, // En modo por_criterio, peso puede actuar como max visual o ponderación
                        'max_puntos' => $max,
                        'seccion' => $seccion,
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
