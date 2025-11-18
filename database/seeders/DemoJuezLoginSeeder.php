<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoJuezLoginSeeder extends Seeder
{
    public function run(): void
    {
        // Rol "juez" (idempotente)
        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);

        // Área mínima
        $area = Area::firstOrCreate(['nombre' => 'Ciencias Generales']);

        // Institución mínima (campos mínimos según tu esquema)
        $institucion = Institucion::firstOrCreate(
            ['nombre' => 'Institución Demo Jueces'],
            [
                'codigo_presupuestario' => 'IDJ-001',
                'tipo' => 'Colegio',
                'modalidad' => 'Académica',
                'regional_id' => 1,
                'circuito_id' => 1,
                'activo' => true,
            ]
        );

        // Usuario de juez (idempotente por email)
        $email = 'juez.demo@prueba.local';
        $passwordPlano = 'JuezDemo#2024';
        $usuario = Usuario::firstOrCreate(
            ['email' => $email],
            [
                'nombre' => 'Juez Demo',
                'password' => Hash::make($passwordPlano),
                'identificacion' => 'JD-'.Str::upper(Str::random(6)),
                'institucion_id' => $institucion->id,
                'activo' => true,
            ]
        );

        if (! $usuario->hasRole('juez')) {
            $usuario->assignRole($role);
        }

        // Juez vinculado a Usuario (1–1) - usar esquema actual (columna area__id)
        DB::table('jueces')->updateOrInsert(
    ['usuario_id' => $usuario->id],
    [
        'nombre' => $usuario->nombre,
        'cedula' => 'DEM-'.Str::upper(Str::random(6)),
        'sexo' => 'N/D',
        'telefono' => '0000-0000',
        'correo' => $email,
        'grado_academico' => 'Licenciatura',
        'area_id' => $area->id,   // <-- aquí estaba 'area__id'
        'updated_at' => now(),
        'created_at' => now(),
    ]
);

        // Salida visible en consola para facilitar pruebas
        $this->command->info('Juez demo listo para login: '.$email.' / '.$passwordPlano);
    }
}
