<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rol')->upsert([
            ['nombre'=>'admin'], ['nombre'=>'comite'], ['nombre'=>'juez'], ['nombre'=>'estudiante']
        ], ['nombre']);

        DB::table('etapa')->upsert([
            ['id'=>1,'nombre'=>'Institucional'],
            ['id'=>2,'nombre'=>'Circuito'],
            ['id'=>3,'nombre'=>'Regional'],
        ], ['id'], ['nombre']);

        DB::table('categoria')->upsert([
            ['nombre'=>'Ciencias Naturales'], ['nombre'=>'Ingeniería y Tecnología']
        ], ['nombre']);

        DB::table('modalidad')->upsert([
            ['nombre'=>'Experimental'], ['nombre'=>'Demostrativa']
        ], ['nombre']);

        DB::table('area_cientifica')->upsert([
            ['nombre'=>'Biología'], ['nombre'=>'Física'], ['nombre'=>'Química'], ['nombre'=>'Informática']
        ], ['nombre']);
    }
}

