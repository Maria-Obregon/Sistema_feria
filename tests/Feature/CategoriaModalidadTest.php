<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Modalidad;
use App\Models\Nivel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaModalidadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function categoria_es_elegible_para_modalidad_cuando_existe_en_pivot()
    {
        $nivel = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $mod   = Modalidad::create(['nombre' => 'Secundaria técnica', 'nivel_id' => $nivel->id, 'activo' => true]);
        $cat   = Categoria::create(['nombre' => 'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO', 'nivel' => null]);

        DB::table('categoria_modalidad')->insert([
            'categoria_id' => $cat->id,
            'modalidad_id' => $mod->id,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $exists = DB::table('categoria_modalidad')
            ->where('categoria_id', $cat->id)
            ->where('modalidad_id', $mod->id)
            ->exists();

        $this->assertTrue($exists, 'La categoría no quedó ligada a la modalidad en el pivot.');
    }

    #[Test]
    public function categoria_no_es_elegible_si_no_esta_en_pivot()
    {
        $nivel = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $mod   = Modalidad::create(['nombre' => 'Secundaria científica y colegios humanísticos', 'nivel_id' => $nivel->id, 'activo' => true]);
        $cat   = Categoria::create(['nombre' => 'INVESTIGACIÓN CIENTÍFICA', 'nivel' => null]);

        $exists = DB::table('categoria_modalidad')
            ->where('categoria_id', $cat->id)
            ->where('modalidad_id', $mod->id)
            ->exists();

        $this->assertFalse($exists, 'No debería existir relación en el pivot.');
    }
}
