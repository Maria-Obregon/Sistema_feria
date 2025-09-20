<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        $admin = Usuario::create([
            'nombre' => 'Administrador General',
            'email' => 'admin@feria.test',
            'password' => Hash::make('Admin123!'),
            'activo' => true,
        ]);
        $admin->assignRole('admin');

        // Comité Institucional
        $comite = Usuario::create([
            'nombre' => 'Comité Institucional',
            'email' => 'comite@feria.test',
            'password' => Hash::make('Comite123!'),
            'institucion_id' => 1, // cambia al ID real de la institución
            'activo' => true,
        ]);
        $comite->assignRole('comite_institucional');

        // Juez
        $juez = Usuario::create([
            'nombre' => 'Juez de Feria',
            'email' => 'juez@feria.test',
            'password' => Hash::make('Juez123!'),
            'activo' => true,
        ]);
        $juez->assignRole('juez');

        // Estudiante
        $estudiante = Usuario::create([
            'nombre' => 'Estudiante Feria',
            'email' => 'estudiante@feria.test',
            'password' => Hash::make('Estudiante123!'),
            'institucion_id' => 1, // cambia al ID real de la institución
            'activo' => true,
        ]);
        $estudiante->assignRole('estudiante');
    }
}
