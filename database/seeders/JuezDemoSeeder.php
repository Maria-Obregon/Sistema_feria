<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AsignacionJuez;
use App\Models\Categoria;
use App\Models\Criterio;
use App\Models\Etapa;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\Rubrica;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder de demo para jueces
 * 
 * Crea usuario juez, proyecto demo, rúbricas y asignaciones.
 * Es idempotente.
 */
class JuezDemoSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = $this->createOrUpdateJuezUser();
        $area = $this->getOrCreateArea();
        $juez = $this->createOrUpdateJuez($usuario, $area);
        $this->createEtapas();
        $proyecto = $this->createOrUpdateProyecto($area);
        $rubricaEscrita = $this->createRubricaEscrita();
        $rubricaOral = $this->createRubricaOral();
        $this->createAsignaciones($juez, $proyecto);

        $this->command->info('Seeder completado');
        $this->command->info('Credenciales: juez@feria.test / Juez123!');
        $this->command->info('Proyecto: ' . $proyecto->titulo);
    }

    /**
     * Crea o actualiza el usuario juez
     */
    private function createOrUpdateJuezUser(): Usuario
    {
        $cols = Schema::getColumnListing('usuarios');

        $buildData = function(array $campos) use ($cols): array {
            $data = [];
            foreach ($campos as $key => $value) {
                if (in_array($key, $cols, true)) {
                    $data[$key] = $value;
                }
            }
            return $data;
        };

        $userData = $buildData([
            'nombre' => 'María Juez Rodríguez',
            'email' => 'juez@feria.test',
            'password' => Hash::make('Juez123!'),
            'activo' => true,
            'apellidos' => 'Juez Rodríguez',
            'tipo_identificacion' => 'cedula',
            'identificacion' => '1-2345-6789',
            'telefono' => '8888-9999',
            'direccion' => null,
        ]);
        $usuario = Usuario::updateOrCreate(
            ['email' => 'juez@feria.test'],
            $userData
        );

        try {
            if (method_exists($usuario, 'hasRole') && !$usuario->hasRole('juez')) {
                $usuario->assignRole('juez');
            }
        } catch (\Exception $e) {
            $this->command->warn('No se pudo asignar rol: ' . $e->getMessage());
        }

        return $usuario;
    }

    private function getOrCreateArea(): Area
    {
        $area = Area::first();

        if (!$area) {
            $area = Area::create([
                'nombre' => 'Biología',
                'codigo' => 'BIO',
                'descripcion' => 'Ciencias Biológicas',
                'activo' => true,
            ]);
        }

        return $area;
    }

    private function createOrUpdateJuez(Usuario $usuario, Area $area): Juez
    {
        $juez = Juez::where('usuario_id', $usuario->id)->first();

        $nombreCompleto = $usuario->nombre;
        if (isset($usuario->apellidos) && $usuario->apellidos) {
            $nombreCompleto .= ' ' . $usuario->apellidos;
        }

        $juezData = [
            'nombre' => $nombreCompleto,
            'cedula' => $usuario->identificacion ?? '1-2345-6789',
            'sexo' => 'F',
            'telefono' => $usuario->telefono ?? '8888-9999',
            'correo' => $usuario->email,
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
        ];

        if (!$juez) {
            $juezData['usuario_id'] = $usuario->id;
            $juez = Juez::create($juezData);
        } else {
            $juez->update($juezData);
        }

        return $juez;
    }

    private function createEtapas(): void
    {
        $etapas = [
            ['id' => 1, 'nombre' => 'institucional'],
            ['id' => 2, 'nombre' => 'circuital'],
            ['id' => 3, 'nombre' => 'regional'],
        ];

        foreach ($etapas as $etapaData) {
            Etapa::updateOrCreate(
                ['id' => $etapaData['id']],
                ['nombre' => $etapaData['nombre']]
            );
        }
    }

    private function createOrUpdateProyecto(Area $area): Proyecto
    {
        $institucion = Institucion::first();
        $categoria = Categoria::first();

        if (!$institucion || !$categoria) {
            throw new \Exception('Se requiere ejecutar CoreCatalogSeeder primero');
        }

        $modalidad = Modalidad::firstOrCreate(
            ['nombre' => 'Individual'],
            ['nombre' => 'Individual']
        );

        $proyecto = Proyecto::where('titulo', 'Proyecto Demo para Calificaciones')->first();

        if (!$proyecto) {
            $proyecto = Proyecto::create([
                'titulo' => 'Proyecto Demo para Calificaciones',
                'resumen' => 'Este es un proyecto de demostración creado para probar el sistema de calificaciones. ' .
                            'El proyecto aborda temas de investigación científica y permite evaluar el funcionamiento ' .
                            'del módulo de evaluación por criterios tanto en la fase escrita como oral.',
                'area_id' => $area->id,
                'categoria_id' => $categoria->id,
                'institucion_id' => $institucion->id,
                'modalidad_id' => $modalidad->id,
                'etapa_actual' => 'institucional',
                'estado' => 'en_evaluacion',
                'palabras_clave' => ['demo', 'calificaciones', 'test'],
                'codigo' => 'DEMO-' . str_pad($institucion->id, 4, '0', STR_PAD_LEFT) . '-' . date('Y'),
            ]);
        }

        return $proyecto;
    }

    private function createRubricaEscrita(): Rubrica
    {
        $rubrica = Rubrica::updateOrCreate(
            ['tipo_eval' => 'escrita'],
            [
                'nombre' => 'Rúbrica de Evaluación Escrita',
                'ponderacion' => 0.60,
            ]
        );

        $criterios = [
            [
                'nombre' => 'Planteamiento del problema',
                'peso' => 0.20,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Marco teórico y fundamentación',
                'peso' => 0.25,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Metodología y procedimiento',
                'peso' => 0.25,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Análisis de resultados',
                'peso' => 0.20,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Conclusiones y recomendaciones',
                'peso' => 0.10,
                'max_puntos' => 100,
            ],
        ];

        foreach ($criterios as $criterioData) {
            Criterio::updateOrCreate(
                [
                    'rubrica_id' => $rubrica->id,
                    'nombre' => $criterioData['nombre'],
                ],
                [
                    'peso' => $criterioData['peso'],
                    'max_puntos' => $criterioData['max_puntos'],
                ]
            );
        }

        return $rubrica->load('criterios');
    }

    private function createRubricaOral(): Rubrica
    {
        $rubrica = Rubrica::updateOrCreate(
            ['tipo_eval' => 'oral'],
            [
                'nombre' => 'Rúbrica de Evaluación Oral',
                'ponderacion' => 0.40,
            ]
        );

        $criterios = [
            [
                'nombre' => 'Dominio del tema',
                'peso' => 0.30,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Claridad en la exposición',
                'peso' => 0.25,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Respuestas a preguntas',
                'peso' => 0.25,
                'max_puntos' => 100,
            ],
            [
                'nombre' => 'Material de apoyo y presentación',
                'peso' => 0.20,
                'max_puntos' => 100,
            ],
        ];

        foreach ($criterios as $criterioData) {
            Criterio::updateOrCreate(
                [
                    'rubrica_id' => $rubrica->id,
                    'nombre' => $criterioData['nombre'],
                ],
                [
                    'peso' => $criterioData['peso'],
                    'max_puntos' => $criterioData['max_puntos'],
                ]
            );
        }

        return $rubrica->load('criterios');
    }

    private function createAsignaciones(Juez $juez, Proyecto $proyecto): void
    {
        $etapaInstitucional = Etapa::find(1);
        $etapaCircuital = Etapa::find(2);
        AsignacionJuez::updateOrCreate(
            [
                'proyecto_id' => $proyecto->id,
                'juez_id' => $juez->id,
                'etapa_id' => $etapaInstitucional->id,
            ],
            [
                'tipo_eval' => 'escrita',
                'asignado_en' => now(),
            ]
        );

        AsignacionJuez::updateOrCreate(
            [
                'proyecto_id' => $proyecto->id,
                'juez_id' => $juez->id,
                'etapa_id' => $etapaCircuital->id,
            ],
            [
                'tipo_eval' => 'oral',
                'asignado_en' => now(),
            ]
        );
    }
}
