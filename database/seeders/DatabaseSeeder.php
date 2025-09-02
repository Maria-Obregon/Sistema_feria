<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catálogos base
        $this->call(CoreCatalogSeeder::class);

        // Asegura rol admin
        $adminRol = Rol::firstOrCreate(['nombre' => 'admin']);

        // Usuario admin por defecto para pruebas manuales
        Usuario::firstOrCreate(
            ['email' => 'admin@feria.test'],
            [
                'password' => 'password123', // se hashea por el cast
                'rol_id'   => $adminRol->id,
                'activo'   => true,
            ]
        );
    }
}
