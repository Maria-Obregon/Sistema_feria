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
        $admin = Usuario::create([
            'nombre' => 'Administrador General',
            'email' => 'admin@feria.test',
            'password' => Hash::make('Admin123!'),
            'activo' => true,
        ]);
        $admin->assignRole('admin');

    }
}
