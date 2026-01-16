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
            NivelesYModalidadesSeeder::class,
            CategoriasSeeder::class,
            ModalidadEtapaSeeder::class,
            PronafecytRubricasSeeder::class,
        ]);
    }
}
