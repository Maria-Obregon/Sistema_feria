<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Categoria;
use App\Models\Nivel;
use App\Models\Modalidad;

class CategoriasSeeder extends Seeder
{
    private function norm(?string $s): string
    {
        $s = (string) $s;
        $s = Str::of($s)->ascii();               
        $s = preg_replace('/\s+/', ' ', trim($s)); // colapsa espacios
        return Str::lower($s);                     
    }

  
    private array $cats = [
        'DEMOSTRACIONES CIENTÍFICAS Y TECNOLÓGICAS' => [
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica regular'],
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica indígena'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'Secundaria académica nocturna'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
        ],

        'INVESTIGACIÓN CIENTÍFICA' => [
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica regular'],
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica indígena'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria científica y colegios humanísticos'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria técnica'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'Secundaria académica nocturna'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
        ],

        'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO' => [
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica regular'],
            ['nivel' => 'Secundaria III Ciclo de la Educación General', 'modalidad' => 'Secundaria académica indígena'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria científica y colegios humanísticos'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria técnica'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'Secundaria académica nocturna'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
            ['nivel' => 'Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],
        ],

        'QUEHACER CIENTÍFICO Y TECNOLÓGICO' => [
            ['nivel' => 'Preescolar', 'modalidad' => 'Preescolar regular'],
            ['nivel' => 'Preescolar', 'modalidad' => 'Preescolar Centros de educación especial'],

            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica regular'],
            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica indígena'],
            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica unidocente'],
            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],

            ['nivel' => 'Primaria I Ciclo de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'Primaria académica Nocturna'],
            ['nivel' => 'Primaria I Ciclo de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'I Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)'],

            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica regular'],
            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica indígena'],
            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria académica unidocente'],
            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],

            ['nivel' => 'Primaria II Ciclo  de la Educación de Personas Jóvenes y Adultas (EPJA)', 'modalidad' => 'Primaria académica nocturna'],
        ],

        'SUMANDO EXPERIENCIAS CIENTÍFICAS' => [
            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],
            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)'],
        ],
        'MI EXPERIENCIA CIENTÍFICA' => [
            ['nivel' => 'Primaria I Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],
            ['nivel' => 'Primaria II Ciclo de la Educación General Básica', 'modalidad' => 'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)'],
            ['nivel' => 'Básica y Educación Diversificada', 'modalidad' => 'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)'],
        ],
    ];

    private array $nivelesPorCategoria = [
        'DEMOSTRACIONES CIENTÍFICAS Y TECNOLÓGICAS' => null,
        'INVESTIGACIÓN CIENTÍFICA'                  => null,
        'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO'    => null,
        'QUEHACER CIENTÍFICO Y TECNOLÓGICO'         => null,
        'SUMANDO EXPERIENCIAS CIENTÍFICAS'          => null,
        'MI EXPERIENCIA CIENTÍFICA'                 => null,
    ];

    public function run(): void
    {
        $cachePorNivel = [];
        $niveles = Nivel::all(['id','nombre']);
        foreach ($niveles as $nivel) {
            $nivelKey = $this->norm($nivel->nombre);
            $cachePorNivel[$nivelKey] = [];

            $mods = Modalidad::where('nivel_id', $nivel->id)->get(['id','nombre']);
            foreach ($mods as $m) {
                $cachePorNivel[$nivelKey][$this->norm($m->nombre)] = $m->id;
            }
        }

        foreach ($this->nivelesPorCategoria as $catNombre => $nivelVal) {
            /** @var \App\Models\Categoria $cat */
            $cat = Categoria::firstOrCreate(
                ['nombre' => $catNombre],
                ['nivel'  => $nivelVal]
            );

            $items = $this->cats[$catNombre] ?? [];
            if (empty($items)) continue;

            $ids = [];

            foreach ($items as $item) {
                if (is_string($item)) {
                    $targetNorm = $this->norm($item);
                    foreach ($cachePorNivel as $nivelKey => $mapa) {
                        if (isset($mapa[$targetNorm])) {
                            $ids[] = $mapa[$targetNorm];
                        }
                    }
                } elseif (is_array($item) && isset($item['nivel'], $item['modalidad'])) {
                    $nivelKey = $this->norm($item['nivel']);
                    $modKey   = $this->norm($item['modalidad']);

                    if (!isset($cachePorNivel[$nivelKey])) {
                        $disponibles = isset($cachePorNivel[$nivelKey]) ? implode(', ', array_keys($cachePorNivel[$nivelKey])) : '(sin modalidades para ese nivel)';
                        throw new \RuntimeException("Nivel no encontrado (o sin modalidades): {$item['nivel']} (cat: {$catNombre}). Disponibles: {$disponibles}");
                    }

                    $mapa = $cachePorNivel[$nivelKey];
                    if (!isset($mapa[$modKey])) {
                        $disponibles = implode(', ', array_keys($mapa));
                        throw new \RuntimeException("Modalidad no encontrada: {$item['modalidad']} en nivel {$item['nivel']} (cat: {$catNombre}). Modalidades en ese nivel: {$disponibles}");
                    }
                    $ids[] = $mapa[$modKey];
                }
            }

            $ids = array_values(array_unique($ids));
            if ($ids) {
                $cat->modalidades()->syncWithoutDetaching($ids);
            }
        }
    }
}
