<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Regional;
use App\Models\Circuito;
use App\Models\Institucion;
use App\Models\Area;
use App\Models\Categoria;

class CoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Regionales
        $regional1 = Regional::create([
            'nombre' => 'Regional de San José',
            'activo' => true,
        ]);

        $regional2 = Regional::create([
            'nombre' => 'Regional de Alajuela',
            'activo' => true,
        ]);

        // Crear Circuitos
        $circuito1 = Circuito::create([
            'nombre' => 'Circuito 01 - San José Centro',
            'codigo' => 'C01-SJ',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);

        $circuito2 = Circuito::create([
            'nombre' => 'Circuito 02 - Desamparados',
            'codigo' => 'C02-SJ',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);

        $circuito3 = Circuito::create([
            'nombre' => 'Circuito 01 - Alajuela Centro',
            'codigo' => 'C01-AL',
            'regional_id' => $regional2->id,
            'activo' => true,
        ]);

        // Crear Instituciones de ejemplo
         Institucion::create([
            'nombre'               => 'Liceo de Costa Rica',
            'modalidad'            => 'Académica',               // <- requerido por tu migración
            'codigo_presupuestario'=> 'LCR-001',
            'direccionreg_id'      => $regional1->id,           // FK a regionales
            'circuito_id'          => $circuito1->id,            // FK a circuitos
            'tipo'                 => 'publica',
            'telefono'             => '2222-3333',
            'email'                => 'info@liceocr.ed.cr',      // tu migración usa 'email' (no 'correo')
            'direccion'            => 'San José Centro',
            'activo'               => true,
            'limite_proyectos'     => 50,
            'limite_estudiantes'   => 200,
        ]);

        Institucion::create([
            'nombre'               => 'Colegio Superior de Señoritas',
            'modalidad'            => 'Académica',
            'codigo_presupuestario'=> 'CSS-002',
            'direccionreg_id'      => $regional2->id,
            'circuito_id'          => $circuito1->id,
            'tipo'                 => 'publica',
            'telefono'             => '2222-4444',
            'email'                => 'info@colegiosenoritas.ed.cr',
            'direccion'            => 'San José Centro',
            'activo'               => true,
            'limite_proyectos'     => 50,
            'limite_estudiantes'   => 200,
        ]);


        // Crear Áreas científicas
        Area::create(['nombre' => 'Biología']);
        Area::create(['nombre' => 'Física']);
        Area::create(['nombre' => 'Química']);
        Area::create(['nombre' => 'Matemática']);
        Area::create(['nombre' => 'Informática']);
        Area::create(['nombre' => 'Ciencias Sociales']);
        Area::create(['nombre' => 'Ingeniería']);

        // Crear Categorías
        Categoria::create([
            'nombre' => 'Investigación Científica',
            'nivel' => 'ambos',
        ]);

        Categoria::create([
            'nombre' => 'Desarrollo Tecnológico',
            'nivel' => 'secundaria',
        ]);

        Categoria::create([
            'nombre' => 'Demostración de Principios',
            'nivel' => 'primaria',
        ]);

        $this->command->info('Catálogos base creados exitosamente');
    }
}

