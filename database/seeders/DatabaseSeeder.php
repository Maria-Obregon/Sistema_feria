<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a seeders en orden correcto
        $this->call([
            CoreCatalogSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        // Crear usuario admin por defecto
        $admin = Usuario::create([
            'nombre' => 'Administrador',
            'email' => 'admin@feria.test',
            'password' => Hash::make('password123'),
            'activo' => true,
        ]);

        // Asignar rol de admin
        $admin->assignRole('admin');

        $this->command->info('Usuario admin creado: admin@feria.test / password123');
    }
}
