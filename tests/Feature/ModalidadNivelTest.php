<?php

namespace Tests\Feature;

use App\Models\Nivel;
use App\Models\Modalidad;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModalidadNivelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function permite_mismo_nombre_de_modalidad_en_distintos_niveles()
    {
        $n1 = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $n2 = Nivel::create(['nombre' => 'Secundaria III Ciclo de la Educación General', 'activo' => true]);

        $m1 = Modalidad::create(['nombre' => 'Secundaria académica nocturna', 'nivel_id' => $n1->id, 'activo' => true]);
        $m2 = Modalidad::create(['nombre' => 'Secundaria académica nocturna', 'nivel_id' => $n2->id, 'activo' => true]);

        $this->assertNotEquals($m1->id, $m2->id);
        $this->assertEquals($m1->nombre, $m2->nombre);
        $this->assertNotEquals($m1->nivel_id, $m2->nivel_id);
    }

    #[Test]
    public function rechaza_duplicado_de_modalidad_en_el_mismo_nivel()
    {
        $this->expectException(QueryException::class);

        $nivel = Nivel::create(['nombre' => 'Preescolar', 'activo' => true]);

        Modalidad::create(['nombre' => 'Preescolar regular', 'nivel_id' => $nivel->id, 'activo' => true]);
        // Debe fallar por unique(nivel_id, nombre)
        Modalidad::create(['nombre' => 'Preescolar regular', 'nivel_id' => $nivel->id, 'activo' => true]);
    }
}
