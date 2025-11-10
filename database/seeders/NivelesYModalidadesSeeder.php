<?php
// database/seeders/NivelesYModalidadesSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Models\Nivel;
use App\Models\Modalidad;

class NivelesYModalidadesSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'Preescolar' => [
                'Preescolar regular',
                'Preescolar Centros de educación especial',
            ],
            'Primaria I Ciclo de la Educación General Básica' => [
                'Primaria académica regular',
                'Primaria académica indígena',
                'Primaria académica unidocente',
                'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)',
            ],
            'Primaria I Ciclo de la Educación de Personas Jóvenes y Adultas (EPJA)' => [
                'Primaria académica Nocturna',
                'I Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
            ],
            'Primaria II Ciclo de la Educación General Básica' => [
                'Primaria académica regular',
                'Primaria académica indígena',
                'Primaria académica unidocente',
                'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)',
            ],
            'Primaria II Ciclo  de la Educación de Personas Jóvenes y Adultas (EPJA)' => [
                'Primaria académica nocturna',
            ],
            'Secundaria III Ciclo de la Educación General' => [
                'Secundaria académica regular',
                'Secundaria académica indígena',
            ],
            'Básica y Educación Diversificada' => [
                'Secundaria científica y colegios humanísticos',
                'Secundaria técnica',
                'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)',
            ],
            'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)' => [
                'Secundaria académica nocturna',
                'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
                'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
            ],
        ];

        foreach ($map as $nivelNombre => $modalidades) {
            $nivel = Nivel::firstOrCreate(['nombre' => $nivelNombre], ['activo' => true]);

            foreach (Arr::wrap($modalidades) as $modNombre) {
                // ¡Ojo! usar par (nivel_id, nombre)
                Modalidad::firstOrCreate(
                    ['nivel_id' => $nivel->id, 'nombre' => $modNombre],
                    ['activo'   => true]
                );
            }
        }
    }
}
