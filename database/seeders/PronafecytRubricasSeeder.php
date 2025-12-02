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
                        ['seccion' => 'I. Autenticidad (6 pts)', 'nombre' => 'Estilo, estructura y vocabulario sugieren elaboración propia', 'max' => 3.00],
                        ['seccion' => 'I. Autenticidad (6 pts)', 'nombre' => 'No plagio: Da crédito a la producción intelectual ajena', 'max' => 3.00],

                        ['seccion' => 'II. Estructura (Página 1) (3 pts)', 'nombre' => 'Portada: Contiene los elementos oficiales', 'max' => 1.00],
                        ['seccion' => 'II. Estructura (Página 1) (3 pts)', 'nombre' => 'Título: Establece una idea general del trabajo', 'max' => 1.00],
                        ['seccion' => 'II. Estructura (Página 1) (3 pts)', 'nombre' => 'Índice: Indica las secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'Introducción (12 pts)', 'nombre' => 'Anota las ideas previas que motivan el proyecto', 'max' => 3.00],
                        ['seccion' => 'Introducción (12 pts)', 'nombre' => 'Señala la importancia del tema relacionado', 'max' => 3.00],
                        ['seccion' => 'Introducción (12 pts)', 'nombre' => 'Indica las preguntas generales relacionadas', 'max' => 3.00],
                        ['seccion' => 'Introducción (12 pts)', 'nombre' => 'Explica el propósito principal de la demostración', 'max' => 3.00],

                        ['seccion' => 'Marco Teórico (9 pts)', 'nombre' => 'Describe palabras claves y conceptos técnicos', 'max' => 3.00],
                        ['seccion' => 'Marco Teórico (9 pts)', 'nombre' => 'Registra información adicional de fuentes', 'max' => 3.00],
                        ['seccion' => 'Marco Teórico (9 pts)', 'nombre' => 'Cita o hace referencia a las fuentes (formato)', 'max' => 3.00],

                        ['seccion' => 'Metodología (11 pts)', 'nombre' => 'Explica pasos, procedimientos o técnicas', 'max' => 3.00],
                        ['seccion' => 'Metodología (11 pts)', 'nombre' => 'Narra los aportes propios que enriquecen', 'max' => 3.00],
                        ['seccion' => 'Metodología (11 pts)', 'nombre' => 'Indica si presenta cambios de la fuente original', 'max' => 1.00],
                        ['seccion' => 'Metodología (11 pts)', 'nombre' => 'Anota lista de recursos tecnológicos/materiales', 'max' => 1.00],
                        ['seccion' => 'Metodología (11 pts)', 'nombre' => 'Describe manejo de residuos y sostenibilidad', 'max' => 3.00],

                        ['seccion' => 'Resultados y Conclusiones (13 pts)', 'nombre' => 'Analiza o interpreta los resultados obtenidos', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones (13 pts)', 'nombre' => 'Contrasta resultados con información consultada', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones (13 pts)', 'nombre' => 'Cita fuentes en el análisis para evitar plagio', 'max' => 1.00],
                        ['seccion' => 'Resultados y Conclusiones (13 pts)', 'nombre' => 'Establece conclusiones obtenidas', 'max' => 3.00],
                        ['seccion' => 'Resultados y Conclusiones (13 pts)', 'nombre' => 'Aporta evidencias de comunicación (fotos/listas)', 'max' => 3.00],

                        // SECCIÓN 3: PÁGINA 3 - FINAL
                        ['seccion' => 'Referencias y Resumen (10 pts)', 'nombre' => 'Referencias: Utiliza mínimo cuatro fuentes', 'max' => 1.00],
                        ['seccion' => 'Referencias y Resumen (10 pts)', 'nombre' => 'Referencias: Fuentes confiables (< 10 años)', 'max' => 3.00],
                        ['seccion' => 'Referencias y Resumen (10 pts)', 'nombre' => 'Referencias: Formato bibliográfico consistente', 'max' => 3.00],
                        ['seccion' => 'Referencias y Resumen (10 pts)', 'nombre' => 'Resumen: Síntesis de aspectos relevantes', 'max' => 3.00],
                    ],
                ],
            ],

            // ... (rest of the array remains unchanged, I'm only targeting the start of the array and the loop logic if possible, but replace_file_content works on chunks. I'll do the loop logic in a separate call or try to fit it if the file structure allows. The loop is at the bottom. I'll do two edits.)

            // F9: Investigación Científica
            'INVESTIGACIÓN CIENTÍFICA' => [
                'exposicion' => [
                    'max' => 40,
                    'nombre_rubrica' => 'Investigación Científica - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'A. Planteamiento de objetivos y justificación', 'nombre' => 'Creatividad y originalidad del problema', 'max' => 1.00],
                        ['seccion' => 'A. Planteamiento de objetivos y justificación', 'nombre' => 'Objetivos tienen relación con el problema', 'max' => 1.00],
                        ['seccion' => 'A. Planteamiento de objetivos y justificación', 'nombre' => 'Objetivos explicados con claridad y coherencia', 'max' => 1.00],
                        ['seccion' => 'A. Planteamiento de objetivos y justificación', 'nombre' => 'Definición de la pregunta incluye las variables', 'max' => 1.00],
                        ['seccion' => 'A. Planteamiento de objetivos y justificación', 'nombre' => 'Identificación de variables en la hipótesis', 'max' => 1.00],

                        // SECCIÓN B
                        ['seccion' => 'B. Marco Teórico', 'nombre' => 'Familiaridad y manejo de contenidos de fuentes', 'max' => 2.00],
                        ['seccion' => 'B. Marco Teórico', 'nombre' => 'Comprensión de conceptos, variables o términos', 'max' => 3.00],

                        // SECCIÓN C
                        ['seccion' => 'C. Metodología Aplicada', 'nombre' => 'Planificación y cumplimiento por etapas', 'max' => 2.00],
                        ['seccion' => 'C. Metodología Aplicada', 'nombre' => 'Selección de recursos e instrumentos adecuados', 'max' => 2.00],
                        ['seccion' => 'C. Metodología Aplicada', 'nombre' => 'Descripción de recursos tecnológicos/materiales', 'max' => 2.00],
                        ['seccion' => 'C. Metodología Aplicada', 'nombre' => 'Descripción adecuada de metodologías utilizadas', 'max' => 2.00],
                        ['seccion' => 'C. Metodología Aplicada', 'nombre' => 'Manejo de residuos y sostenibilidad ambiental', 'max' => 2.00],

                        // SECCIÓN D
                        ['seccion' => 'D. Discusión e Interpretación', 'nombre' => 'Coherencia entre objetivos y conclusiones', 'max' => 1.00],
                        ['seccion' => 'D. Discusión e Interpretación', 'nombre' => 'Análisis, discusión y correlación de variables', 'max' => 2.00],
                        ['seccion' => 'D. Discusión e Interpretación', 'nombre' => 'Logra comprobación o negación de hipótesis', 'max' => 1.00],
                        ['seccion' => 'D. Discusión e Interpretación', 'nombre' => 'Congruencia de datos/tablas con el tema', 'max' => 1.00],
                        ['seccion' => 'D. Discusión e Interpretación', 'nombre' => 'Sugiere aplicaciones o mejoras a actividades', 'max' => 1.00],

                        // SECCIÓN E
                        ['seccion' => 'E. Presentación y Comunicación', 'nombre' => 'Cartel apoya la comunicación fluida', 'max' => 1.00],
                        ['seccion' => 'E. Presentación y Comunicación', 'nombre' => 'Material expuesto tiene relación con trabajo', 'max' => 2.00],
                        ['seccion' => 'E. Presentación y Comunicación', 'nombre' => 'Capacidad de síntesis en la comunicación', 'max' => 2.00],
                        ['seccion' => 'E. Presentación y Comunicación', 'nombre' => 'Claridad/coherencia al explicar propósito/proceso', 'max' => 3.00],
                        ['seccion' => 'E. Presentación y Comunicación', 'nombre' => 'Participación y dominio de todos los miembros', 'max' => 2.00],

                        // SECCIÓN F
                        ['seccion' => 'F. Autenticidad', 'nombre' => 'Muestras de realización propia (Cartel/Material)', 'max' => 2.00],
                        ['seccion' => 'F. Autenticidad', 'nombre' => 'Originalidad en la elaboración del material', 'max' => 2.00],
                    ],
                ],
                'escrito' => [
                    'max' => 78,
                    'nombre_rubrica' => 'Investigación Científica - Informe Escrito',
                    'criterios' => [
                        // SECCIÓN 1: I PARTE Y PÁGINA 1
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Autenticidad: Estilo, estructura y vocabulario', 'max' => 3.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Autenticidad: No plagio (Créditos)', 'max' => 3.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Portada: Contiene elementos oficiales', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Título: Establece idea general (Breve/Conciso)', 'max' => 3.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Índice: Indica secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Intro: Ideas previas que motivan', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Intro: Importancia del tema investigado', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Intro: Preguntas que orientan la investigación', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Intro: Hipótesis (Variables Indep/Dep)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Intro: Objetivo general y específicos', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Marco: Conceptos/Variables/Términos técnicos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Marco: Información adicional de fuentes', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Marco: Citas y referencias (Formato)', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Metodología: Explicación de pasos/procedimientos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Metodología: Lista de recursos tecnológicos', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Metodología: Selección de instrumentos adecuados', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Metodología: Explicación de variables (Indep/Dep)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (37 pts)', 'nombre' => 'Metodología: Manejo de residuos/sostenibilidad', 'max' => 3.00],

                        // SECCIÓN 3: PÁGINA 3 - ANÁLISIS Y FINAL
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Análisis estadístico de datos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Indica cumplimiento de hipótesis', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Contraste con información consultada', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Citas/Referencias en análisis', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Conclusiones por objetivo específico', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Sugerencias de mejora', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resultados: Evidencias de comunicación', 'max' => 1.00],

                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Referencias: Cantidad suficiente (7)', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Referencias: Calidad (<10 años/Confiables)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Referencias: Formato consistente', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Resumen: Síntesis completa', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: ANÁLISIS Y FINAL (30 pts)', 'nombre' => 'Bitácora: Completa (Fechas/Actividades)', 'max' => 3.00],
                    ],
                ],
            ],

            // F10: Investigación y Desarrollo Tecnológico
            'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO' => [
                'exposicion' => [
                    'max' => 40,
                    'nombre_rubrica' => 'Investigación y Desarrollo Tecnológico - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'SECCIÓN A: Planteamiento de los objetivos y justificación del problema (5 pts)', 'nombre' => 'La escogencia del problema/pregunta responde a una necesidad concreta', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN A: Planteamiento de los objetivos y justificación del problema (5 pts)', 'nombre' => 'Justifica, de forma cualitativa o cuantitativa, la relevancia del problema', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN A: Planteamiento de los objetivos y justificación del problema (5 pts)', 'nombre' => 'Los objetivos tienen relación con el problema de investigación', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN A: Planteamiento de los objetivos y justificación del problema (5 pts)', 'nombre' => 'Los objetivos son explicados con claridad y coherencia', 'max' => 1.00],

                        // SECCIÓN B
                        ['seccion' => 'SECCIÓN B: Marco teórico (5 pts)', 'nombre' => 'Existe familiaridad y manejo de los contenidos de las fuentes', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico (5 pts)', 'nombre' => 'Existe claridad y precisión en los conceptos utilizados', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN B: Marco teórico (5 pts)', 'nombre' => 'Utiliza correctamente el lenguaje científico y tecnológico acorde', 'max' => 3.00],

                        // SECCIÓN C
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Selección de instrumentos y métodos adecuados', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Describe las metodologías utilizadas para la obtención de soluciones', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Cumplimiento de las etapas planificadas en el diseño', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Utiliza recursos materiales de bajo costo', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Los recursos están orientados hacia la sostenibilidad ambiental', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Metodología aplicada (10 pts)', 'nombre' => 'Describe las metodologías de evaluación y perfeccionamiento', 'max' => 2.00],

                        // SECCIÓN D
                        ['seccion' => 'SECCIÓN D: Discusión, interpretación y aplicación de los resultados (8 pts)', 'nombre' => 'Coherencia de los objetivos con los resultados obtenidos', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN D: Discusión, interpretación y aplicación de los resultados (8 pts)', 'nombre' => 'Explica cómo los resultados tienen un impacto positivo sobre el problema', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN D: Discusión, interpretación y aplicación de los resultados (8 pts)', 'nombre' => 'Presentación y congruencia de datos, tablas y gráficos', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN D: Discusión, interpretación y aplicación de los resultados (8 pts)', 'nombre' => 'Analiza posibles aplicaciones del desarrollo tecnológico en la sociedad', 'max' => 2.00],

                        // SECCIÓN E
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica (8 pts)', 'nombre' => 'El cartel presentado apoya la comunicación en forma fluida', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica (8 pts)', 'nombre' => 'El material expuesto tiene relación con el trabajo de investigación', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica (8 pts)', 'nombre' => 'Existe capacidad de síntesis para llevar a cabo la comunicación', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica (8 pts)', 'nombre' => 'Claridad al explicar el propósito, proceso y relevancia', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación científica (8 pts)', 'nombre' => 'Participación y dominio del tema (todos los miembros)', 'max' => 2.00],

                        // SECCIÓN F
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'El cartel y material expuesto da muestras de realización propia', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'Existe originalidad en la elaboración del material', 'max' => 2.00],
                    ],
                ],
                'escrito' => [
                    'max' => 98,
                    'nombre_rubrica' => 'Investigación y Desarrollo Tecnológico - Informe Escrito',
                    'criterios' => [
                        // SECCIÓN 1: I PARTE Y PÁGINA 1
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Autenticidad: Estilo, estructura y vocabulario propios', 'max' => 3.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Autenticidad: No plagio: Créditos a producción ajena', 'max' => 3.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Portada: Contiene elementos oficiales', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Portada: Informa contenido de la investigación', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Título: Breve, conciso y específico', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (10 pts)', 'nombre' => 'Índice: Indica secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Anota ideas previas que motivan el proyecto', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Destaca el problema/necesidad a resolver', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Presenta la pregunta/problema orientador', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Describe dimensiones del problema (social/amb/etc)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Presenta objetivo general y 3 específicos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Claridad en la redacción de objetivos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Concordancia entre objetivos y pregunta', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Describe razones y propósito (Justificación)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Describe conveniencia para la comunidad', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Intro: Presenta análisis de viabilidad', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Marco: Describe conceptos, variables o términos técnicos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Marco: Registra información adicional fuentes', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Marco: Cita o hace referencia a fuentes (Formato)', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Metodología: Explica pasos, procedimientos o técnicas', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Metodología: Presenta lista de recursos tecnológicos/materiales', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Metodología: Selecciona y describe instrumentos de investigación', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Metodología: Describe método para análisis de datos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (54 pts)', 'nombre' => 'Metodología: Describe manejo de residuos y sostenibilidad', 'max' => 3.00],

                        // SECCIÓN 3: PÁGINA 3 - RESULTADOS Y FINAL
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Analiza estadísticamente datos obtenidos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Explica impacto positivo sobre el problema', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Contrasta resultados con teoría', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Conclusiones asociadas a objetivos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Conclusiones demuestran comprensión del tema', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Brinda sugerencias para mejorar/futuro', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resultados: Aporta evidencias de comunicación', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Referencias: Presenta suficientes (mínimo 7)', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Referencias: Calidad (<10 años/Confiables)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Referencias: Formato consistente', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Resumen: Síntesis aspectos relevantes', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 3: RESULTADOS Y FINAL (34 pts)', 'nombre' => 'Bitácora: Completa (Fechas/Actividades)', 'max' => 3.00],
                    ],
                ],
            ],

            // F11: Quehacer Científico
            'QUEHACER CIENTÍFICO Y TECNOLÓGICO' => [
                'exposicion' => [
                    'max' => 40,
                    'nombre_rubrica' => 'Quehacer Científico y Tecnológico - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (5 pts)', 'nombre' => 'Las ideas previas evidencian toma de decisiones', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (5 pts)', 'nombre' => 'Expresa sus ideas al presentar la pregunta y suposiciones', 'max' => 2.00],

                        // SECCIÓN B
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (6 pts)', 'nombre' => 'Las acciones o pasos son comunicados con frases sencillas', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (6 pts)', 'nombre' => 'Evidencia familiaridad y comprensión de los pasos realizados', 'max' => 3.00],

                        // SECCIÓN C
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Comunica los logros de la investigación', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Comunica las fuentes de información consultadas', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Expresa ideas propias relacionadas con la temática', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Evidencia el disfrute y apropiación de la investigación', 'max' => 2.00],

                        // SECCIÓN D
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Comunica el proceso de forma lógica y secuencial', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Demuestra dominio al comunicar los logros obtenidos', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Todas las personas integrantes participan en la comunicación', 'max' => 2.00],

                        // SECCIÓN E
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (7 pts)', 'nombre' => 'El cartel presentado apoya la comunicación en forma fluida', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (7 pts)', 'nombre' => 'El material expuesto tiene relación con el trabajo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (7 pts)', 'nombre' => 'Menciona todos los elementos que apoyan el trabajo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (7 pts)', 'nombre' => 'Todas las personas participan en la exposición y dominan el tema', 'max' => 2.00],

                        // SECCIÓN F
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'El cartel y material da muestras de realización propia', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'Existe originalidad en la elaboración del material', 'max' => 2.00],
                    ],
                ],
                'escrito' => [
                    'max' => 57,
                    'nombre_rubrica' => 'Quehacer Científico y Tecnológico - Informe Escrito',
                    'criterios' => [
                        // SECCIÓN 1: I PARTE Y PÁGINA 1
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Autenticidad: El estilo, la estructura y el vocabulario sugieren elaboración propia', 'max' => 4.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Autenticidad: No se cometió plagio / Da crédito a autores', 'max' => 4.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Portada: Contiene los elementos básicos', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Título: Establece una idea general del trabajo', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Índice: Indica las secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Establece ideas previas que motivan la investigación', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Presenta la(s) pregunta(s) que orienta la investigación', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Señala suposiciones o predicciones (Hipótesis)', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Narra o describe las acciones o pasos necesarios', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Refiere a recursos tecnológicos o materiales requeridos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Describe el manejo de residuos y sostenibilidad', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Registra los hallazgos encontrados (imágenes/datos)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Cita o hace referencia a las fuentes utilizadas', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Compara hallazgos con información consultada', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Verifica si se cumplieron las suposiciones/predicciones', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Establece los logros obtenidos y sugiere ideas', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Aporta evidencias de comunicación (fotos/listas)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Demuestra apropiación del proceso de investigación', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Utiliza mínimo tres referencias', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Calidad (Recientes/Confiables)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Formato consistente (APA u otro)', 'max' => 3.00],
                    ],
                ],
            ],

            // F12: Sumando Experiencias
            'SUMANDO EXPERIENCIAS CIENTÍFICAS' => [
                'exposicion' => [
                    'max' => 40,
                    'nombre_rubrica' => 'Sumando Experiencias Científicas - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (5 pts)', 'nombre' => 'Las ideas previas evidencian toma de decisiones', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (5 pts)', 'nombre' => 'Expresa sus ideas al presentar la pregunta y suposiciones', 'max' => 2.00],

                        // SECCIÓN B
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (5 pts)', 'nombre' => 'Las acciones o pasos son comunicados con frases sencillas', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (5 pts)', 'nombre' => 'Evidencia familiaridad y comprensión de los pasos realizados', 'max' => 3.00],

                        // SECCIÓN C
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Comunica los hallazgos con la información consultada', 'max' => 1.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Comunica los logros de la investigación', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Comunica las fuentes de información consultadas', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Expresa ideas propias relacionadas con la temática', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (10 pts)', 'nombre' => 'Evidencia el disfrute y apropiación de la investigación', 'max' => 2.00],

                        // SECCIÓN D
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Comunica el proceso de forma lógica y secuencial', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Demuestra dominio al comunicar los logros obtenidos', 'max' => 3.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (8 pts)', 'nombre' => 'Todas las personas integrantes participan en la comunicación', 'max' => 2.00],

                        // SECCIÓN E
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (8 pts)', 'nombre' => 'El cartel presentado apoya la comunicación en forma fluida', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (8 pts)', 'nombre' => 'El material expuesto tiene relación con el trabajo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (8 pts)', 'nombre' => 'Señala o menciona todos los elementos que apoyan el trabajo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN E: Presentación y comunicación de la información (8 pts)', 'nombre' => 'Todas las personas participan en la exposición y dominan el tema', 'max' => 2.00],

                        // SECCIÓN F
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'El cartel y recursos visuales corresponden al desarrollo cognitivo', 'max' => 2.00],
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (4 pts)', 'nombre' => 'Existe originalidad en la elaboración del material', 'max' => 2.00],
                    ],
                ],
                'escrito' => [
                    'max' => 57,
                    'nombre_rubrica' => 'Sumando Experiencias Científicas - Informe Escrito',
                    'criterios' => [
                        // SECCIÓN 1: I PARTE Y PÁGINA 1
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Autenticidad: Estilo y vocabulario propios', 'max' => 4.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'No plagio: Créditos a fuentes', 'max' => 4.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Portada: Contiene elementos básicos', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Título: Establece idea general', 'max' => 1.00],
                        ['seccion' => 'I PARTE: AUTENTICIDAD Y PÁGINA 1 (11 pts)', 'nombre' => 'Índice: Indica secciones y páginas', 'max' => 1.00],

                        // SECCIÓN 2: PÁGINA 2 - CUERPO DEL INFORME
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Establece ideas previas que motivan', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Presenta la(s) pregunta(s) orientadora(s)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Aspectos Iniciales: Señala suposiciones o predicciones', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Narra/Describe acciones o pasos necesarios', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Refiere a recursos tecnológicos o materiales', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Pasos por Seguir: Describe manejo de residuos y sostenibilidad', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Registra hallazgos (imágenes/datos/textos)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Contrasta hallazgos con información consultada', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Cita fuentes utilizadas para complementar', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Verifica si se cumplieron suposiciones', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Establece logros obtenidos', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Aporta evidencias de comunicación', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Logros Obtenidos: Demuestra apropiación del proceso', 'max' => 3.00],

                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Utiliza mínimo tres referencias', 'max' => 1.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Calidad (Recientes/Confiables)', 'max' => 3.00],
                        ['seccion' => 'PÁGINA 2: CUERPO DEL INFORME (46 pts)', 'nombre' => 'Referencias: Formato consistente', 'max' => 3.00],
                    ],
                ],
            ],

            // F13: Mi Experiencia Científica (SOLO EXPOSICIÓN)
            'MI EXPERIENCIA CIENTÍFICA' => [
                'exposicion' => [
                    'max' => 100,
                    'nombre_rubrica' => 'Mi Experiencia Científica - Exposición',
                    'criterios' => [
                        // SECCIÓN A
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (15 pts)', 'nombre' => 'Se evidencia el planteamiento de la hipótesis o problema', 'max' => 5.00],
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (15 pts)', 'nombre' => 'Se demuestra que fue un tema desarrollado en el aula', 'max' => 5.00],
                        ['seccion' => 'SECCIÓN A: Aspectos iniciales (15 pts)', 'nombre' => 'El tema corresponde al currículo del nivel de los estudiantes', 'max' => 5.00],

                        // SECCIÓN B
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (15 pts)', 'nombre' => 'Expresa acciones/pasos realizados (material/fotos/señas/oral)', 'max' => 7.00],
                        ['seccion' => 'SECCIÓN B: Pasos por seguir (15 pts)', 'nombre' => 'Evidencia familiaridad y comprensión de los pasos realizados', 'max' => 8.00],

                        // SECCIÓN C
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (25 pts)', 'nombre' => 'Expresa hallazgos contrastados con información consultada', 'max' => 5.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (25 pts)', 'nombre' => 'Expresa los logros de la investigación', 'max' => 10.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (25 pts)', 'nombre' => 'Expresa las fuentes de información consultada', 'max' => 5.00],
                        ['seccion' => 'SECCIÓN C: Logros obtenidos (25 pts)', 'nombre' => 'Evidencia el disfrute y la apropiación de la investigación', 'max' => 5.00],

                        // SECCIÓN D
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (23 pts)', 'nombre' => 'Expresa el proceso de forma lógica y secuencial', 'max' => 8.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (23 pts)', 'nombre' => 'Demuestra dominio al comunicar los logros obtenidos', 'max' => 8.00],
                        ['seccion' => 'SECCIÓN D: Dominio de la temática (23 pts)', 'nombre' => 'Todas las personas integrantes participan en la comunicación', 'max' => 7.00],

                        // SECCIÓN E
                        ['seccion' => 'SECCIÓN E: Comunicación de la información (15 pts)', 'nombre' => 'El cartel presentado apoya la comunicación fluida', 'max' => 4.00],
                        ['seccion' => 'SECCIÓN E: Comunicación de la información (15 pts)', 'nombre' => 'El material expuesto tiene relación con el trabajo', 'max' => 4.00],
                        ['seccion' => 'SECCIÓN E: Comunicación de la información (15 pts)', 'nombre' => 'Señala o menciona elementos que apoyan la investigación', 'max' => 4.00],
                        ['seccion' => 'SECCIÓN E: Comunicación de la información (15 pts)', 'nombre' => 'Manifiesta normas de cortesía al comunicar lo investigado', 'max' => 3.00],

                        // SECCIÓN F
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (7 pts)', 'nombre' => 'El cartel y recursos corresponden al desarrollo cognitivo', 'max' => 4.00],
                        ['seccion' => 'SECCIÓN F: Autenticidad del trabajo realizado (7 pts)', 'nombre' => 'Evidencia originalidad en la elaboración de material', 'max' => 3.00],
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
