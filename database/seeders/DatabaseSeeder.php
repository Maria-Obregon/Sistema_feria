<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreCatalogSeeder::class,
            RolesAndPermissionsSeeder::class,
            UsuariosSeeder::class,
        ]);

        // Seeder de demo para jueces (opcional, idempotente)
        try {
            $this->call(JuezDemoSeeder::class);
        } catch (\Exception $e) {
            $this->command->warn('⚠ JuezDemoSeeder no pudo ejecutarse: ' . $e->getMessage());
        }
    }
}
