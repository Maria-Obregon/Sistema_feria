<?php

namespace Tests\Feature;

use App\Models\Etapa;
use App\Models\Modalidad;
use App\Models\Nivel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModalidadEtapaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function modalidad_aplica_a_etapas_definidas_en_el_pivot()
    {
        $etInst = Etapa::create(['nombre' => 'institucional']);
        $etCirc = Etapa::create(['nombre' => 'circuital']);
        $etReg  = Etapa::create(['nombre' => 'regional']);

        $nivel = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $mod   = Modalidad::create(['nombre' => 'Secundaria técnica', 'nivel_id' => $nivel->id, 'activo' => true]);

        // Solo institucional y regional
        DB::table('modalidad_etapa')->insert([
            ['modalidad_id' => $mod->id, 'etapa_id' => $etInst->id, 'created_at' => now(), 'updated_at' => now()],
            ['modalidad_id' => $mod->id, 'etapa_id' => $etReg->id,  'created_at' => now(), 'updated_at' => now()],
        ]);

        $aplicaInst = DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etInst->id)->exists();
        $aplicaCirc = DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etCirc->id)->exists();
        $aplicaReg  = DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etReg->id)->exists();

        $this->assertTrue($aplicaInst, 'Debería aplicar a institucional.');
        $this->assertFalse($aplicaCirc, 'No debería aplicar a circuital.');
        $this->assertTrue($aplicaReg, 'Debería aplicar a regional.');
    }

    #[Test]
    public function ejemplo_preescolar_solo_institucional()
    {
        $etInst = Etapa::create(['nombre' => 'institucional']);
        $etCirc = Etapa::create(['nombre' => 'circuital']);
        $etReg  = Etapa::create(['nombre' => 'regional']);

        $nivel = Nivel::create(['nombre' => 'Preescolar', 'activo' => true]);
        $mod   = Modalidad::create(['nombre' => 'Preescolar regular', 'nivel_id' => $nivel->id, 'activo' => true]);

        // Solo institucional
        DB::table('modalidad_etapa')->insert([
            'modalidad_id' => $mod->id,
            'etapa_id'     => $etInst->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->assertTrue(
            DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etInst->id)->exists()
        );
        $this->assertFalse(
            DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etCirc->id)->exists()
        );
        $this->assertFalse(
            DB::table('modalidad_etapa')->where('modalidad_id', $mod->id)->where('etapa_id', $etReg->id)->exists()
        );
    }
}
