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
            'codigo' => 'RSJ',
            'activo' => true,
        ]);

        $regional2 = Regional::create([
            'nombre' => 'Regional de Alajuela',
            'codigo' => 'RAL',
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
            'nombre' => 'Liceo de Costa Rica',
            'codigo_presupuestario' => 'LCR-001',
            'circuito_id' => $circuito1->id,
            'tipo' => 'publica',
            'telefono' => '2222-3333',
            'email' => 'info@liceocr.ed.cr',
            'direccion' => 'San José Centro',
            'activo' => true,
        ]);

        Institucion::create([
            'nombre' => 'Colegio Superior de Señoritas',
            'codigo_presupuestario' => 'CSS-002',
            'circuito_id' => $circuito1->id,
            'tipo' => 'publica',
            'telefono' => '2222-4444',
            'email' => 'info@colegiosenoritas.ed.cr',
            'direccion' => 'San José Centro',
            'activo' => true,
        ]);

        // Crear Áreas científicas
        Area::create(['nombre' => 'Biología', 'codigo' => 'BIO', 'descripcion' => 'Ciencias de la vida']);
        Area::create(['nombre' => 'Física', 'codigo' => 'FIS', 'descripcion' => 'Ciencias físicas']);
        Area::create(['nombre' => 'Química', 'codigo' => 'QUI', 'descripcion' => 'Ciencias químicas']);
        Area::create(['nombre' => 'Matemática', 'codigo' => 'MAT', 'descripcion' => 'Ciencias matemáticas']);
        Area::create(['nombre' => 'Informática', 'codigo' => 'INF', 'descripcion' => 'Ciencias de la computación']);
        Area::create(['nombre' => 'Ciencias Sociales', 'codigo' => 'SOC', 'descripcion' => 'Estudios sociales']);
        Area::create(['nombre' => 'Ingeniería', 'codigo' => 'ING', 'descripcion' => 'Ingeniería y tecnología']);

        // Crear Categorías
        Categoria::create([
            'nombre' => 'Investigación Científica',
            'codigo' => 'IC',
            'nivel' => 'ambos',
            'descripcion' => 'Proyectos de investigación científica'
        ]);

        Categoria::create([
            'nombre' => 'Desarrollo Tecnológico',
            'codigo' => 'DT',
            'nivel' => 'secundaria',
            'descripcion' => 'Proyectos de desarrollo tecnológico e innovación'
        ]);

        Categoria::create([
            'nombre' => 'Demostración de Principios',
            'codigo' => 'DP',
            'nivel' => 'primaria',
            'descripcion' => 'Proyectos demostrativos para educación primaria'
        ]);

        $this->command->info('Catálogos base creados exitosamente');
    }
}

