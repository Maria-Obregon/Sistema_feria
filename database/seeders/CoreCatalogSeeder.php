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
            'nombre' => 'Regional de la Zona Norte-Norte',
            'activo' => true,
        ]);

        // Crear Circuitos
        $circuito1 = Circuito::create([
            'nombre' => 'Circuito Escolar 01 - Upala',
            'codigo' => 'C01-NN',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);

        $circuito2 = Circuito::create([
            'nombre' => 'Circuito Escolar 02 - Aguas Claras',
            'codigo' => 'C02-NN',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);

         $circuito3 = Circuito::create([
            'nombre' => 'Circuito Escolar 03 - San José de Upala',
            'codigo' => 'C03-NN',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);

        $circuito4 = Circuito::create([
            'nombre' => 'Circuito Escolar 04 - Canalete',
            'codigo' => 'C04-NN',
            'regional_id' => $regional1->id,
            'activo' => true,
        ]);


        // Crear Áreas científicas
        Area::create(['nombre' => 'Biología']);
        Area::create(['nombre' => 'Ciencias Ambientales']);
        Area::create(['nombre' => 'Ciencias de la Computación']);
        Area::create(['nombre' => 'Ciencias de la Tierra y del Espacio']);
        Area::create(['nombre' => 'Ciencias Sociales y Humanidades']);
        Area::create(['nombre' => 'Física y Matemática']);
        Area::create(['nombre' => 'Ingeniería y Tecnología']);
        Area::create(['nombre' => 'Química']);
        Area::create(['nombre' => 'Salud y Medicina']);

    }
}

