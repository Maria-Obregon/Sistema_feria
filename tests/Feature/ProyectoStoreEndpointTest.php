<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Categoria;
use App\Models\Etapa;
use App\Models\Modalidad;
use App\Models\Nivel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProyectoStoreEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** Inserta en $table solo columnas existentes (evita fallos en SQLite/MySQL). */
    private function insertCompat(string $table, array $attrs): int
    {
        $cols = Schema::getColumnListing($table);
        $data = array_intersect_key($attrs, array_flip($cols));

        // Timestamps “compat” (inglés y español)
        $now = now();
        if (in_array('created_at', $cols) && !isset($data['created_at'])) $data['created_at'] = $now;
        if (in_array('updated_at', $cols) && !isset($data['updated_at'])) $data['updated_at'] = $now;
        if (in_array('creado_en', $cols)   && !isset($data['creado_en'])) $data['creado_en'] = $now;
        if (in_array('actualizado_en', $cols) && !isset($data['actualizado_en'])) $data['actualizado_en'] = $now;

        return (int) DB::table($table)->insertGetId($data);
    }

    /** Defaults para columnas NOT NULL, SOLO si esas columnas existen en la tabla. */
    private function defaultsFor(string $table): array
    {
        $cols = Schema::getColumnListing($table);
        $d = [];

        // Instituciones (comunes en tus migraciones)
        if (in_array('tipo', $cols))                 $d['tipo'] = 'Académica';
        if (in_array('modalidad', $cols))            $d['modalidad'] = 'Académica';
        if (in_array('codigo_presupuestario', $cols))$d['codigo_presupuestario'] = (string) random_int(1000000000, 9999999999);
        if (in_array('codigo', $cols))               $d['codigo'] = 'COLEG-'.str_pad((string) random_int(1,9999), 4, '0', STR_PAD_LEFT);
        if (in_array('telefono', $cols))             $d['telefono'] = '2222-2222';
        if (in_array('email', $cols))                $d['email'] = Str::lower(Str::random(6)).'@example.com';
        if (in_array('direccion', $cols))            $d['direccion'] = 'Dirección de prueba';

        return $d;
    }

    /** Crea una institución mínima (completando NOT NULL si existen). */
    private function crearInstitucionMinima(array $override = []): int
    {
        $regionalId = DB::table('regionales')->value('id') ?? $this->crearRegionalMinimo();
        $circuitoId = DB::table('circuitos')->value('id')  ?? $this->crearCircuitoMinimo();

        $base = [
            'nombre'      => 'Colegio X',
            'regional_id' => $regionalId,
            'circuito_id' => $circuitoId,
            'activo'      => 1,
        ];

        // Agrega defaults solo si esas columnas existen (ej. tipo NOT NULL)
        $base = array_merge($base, $this->defaultsFor('instituciones'));

        return $this->insertCompat('instituciones', array_merge($base, $override));
    }

    private function crearRegionalMinimo(): int
    {
        return $this->insertCompat('regionales', [
            'nombre' => 'Regional A',
            'activo' => 1,
        ]);
    }

    private function crearCircuitoMinimo(): int
    {
        $regionalId = DB::table('regionales')->value('id') ?? $this->crearRegionalMinimo();
        return $this->insertCompat('circuitos', [
            'nombre'      => 'Circuito 01',
            'codigo'      => 'C-01',
            'regional_id' => $regionalId,
            'activo'      => 1,
        ]);
    }

    /** Crea feria (institucional) usando tipo_feria/etapa_id si existen. */
    private function crearFeriaInstitucional(int $institucionId, int $etapaId): int
    {
        $base = [
            'anio'               => (int) date('Y'),
            'institucion_id'     => $institucionId,
            'fecha'              => now()->toDateString(),
            'hora_inicio'        => '08:00:00',
            'sede'               => 'Gimnasio',
            'proyectos_por_aula' => 0,
            'lugar_realizacion'  => 'Sede central',
            // Se insertan solo si existen dichas columnas
            'tipo_feria'         => 'institucional',
            'etapa_id'           => $etapaId,
        ];

        return $this->insertCompat('ferias', $base);
    }

    /** Prepara catálogos + vínculos (categoria_modalidad y modalidad_etapa). */
    private function prepararCatAreaNivelModalidadYVinculos(): array
    {
        $etInst = Etapa::create(['nombre' => 'institucional']);

        $nivel = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $area  = Area::create(['nombre' => 'Ciencias', 'activo' => true]);

        $mod = Modalidad::create(['nombre' => 'Secundaria técnica', 'nivel_id' => $nivel->id, 'activo' => true]);
        $cat = Categoria::create(['nombre' => 'INVESTIGACIÓN Y DESARROLLO TECNOLÓGICO', 'nivel' => null]);

        DB::table('categoria_modalidad')->insert([
            'categoria_id' => $cat->id,
            'modalidad_id' => $mod->id,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        DB::table('modalidad_etapa')->insert([
            'modalidad_id' => $mod->id,
            'etapa_id'     => $etInst->id,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        return compact('etInst','nivel','area','mod','cat');
    }

    #[Test]
    public function crea_proyecto_valido_cuando_todo_cuadra()
    {
        $this->withoutMiddleware();

        extract($this->prepararCatAreaNivelModalidadYVinculos()); // etInst, area, mod, cat
        $instId  = $this->crearInstitucionMinima();
        $feriaId = $this->crearFeriaInstitucional($instId, $etInst->id);

        $payload = [
            'titulo'         => 'Proyecto A',
            'resumen'        => 'Resumen',
            'area_id'        => $area->id,
            'categoria_id'   => $cat->id,
            'modalidad_id'   => $mod->id,
            'institucion_id' => $instId,
            'feria_id'       => $feriaId,
            'estudiantes'    => [],
        ];

        $resp = $this->postJson('/api/proyectos', $payload);
        $resp->assertCreated()->assertJsonFragment([
            'titulo'         => 'Proyecto A',
            'area_id'        => $area->id,
            'categoria_id'   => $cat->id,
            'modalidad_id'   => $mod->id,
            'institucion_id' => $instId,
            'feria_id'       => $feriaId,
        ]);

        $this->assertDatabaseHas('proyectos', ['titulo' => 'Proyecto A']);
    }

    #[Test]
    public function rechaza_si_categoria_no_es_elegible_para_modalidad()
    {
        $this->withoutMiddleware();

        $etInst = Etapa::create(['nombre' => 'institucional']);
        $nivel  = Nivel::create(['nombre' => 'Básica y Educación Diversificada', 'activo' => true]);
        $area   = Area::create(['nombre' => 'Ciencias', 'activo' => true]);

        $mod = Modalidad::create(['nombre' => 'Secundaria técnica', 'nivel_id' => $nivel->id, 'activo' => true]);

        // Aplica a institucional para que NO falle por etapa
        DB::table('modalidad_etapa')->insert([
            'modalidad_id' => $mod->id,
            'etapa_id'     => $etInst->id,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        // Categoría NO vinculada a la modalidad
        $cat = Categoria::create(['nombre' => 'INVESTIGACIÓN CIENTÍFICA', 'nivel' => null]);

        $instId  = $this->crearInstitucionMinima();
        $feriaId = $this->crearFeriaInstitucional($instId, $etInst->id);

        $payload = [
            'titulo'         => 'Proyecto B',
            'resumen'        => 'R',
            'area_id'        => $area->id,
            'categoria_id'   => $cat->id,     // sin vínculo en categoria_modalidad
            'modalidad_id'   => $mod->id,
            'institucion_id' => $instId,
            'feria_id'       => $feriaId,
        ];

        $this->postJson('/api/proyectos', $payload)->assertStatus(422);
    }

    #[Test]
    public function rechaza_si_modalidad_no_aplica_a_la_etapa_de_la_feria()
    {
        $this->withoutMiddleware();

        $etInst = Etapa::create(['nombre' => 'institucional']);
        $nivel  = Nivel::create(['nombre' => 'Preescolar', 'activo' => true]);
        $area   = Area::create(['nombre' => 'Ciencias', 'activo' => true]);

        $mod = Modalidad::create(['nombre' => 'Preescolar regular', 'nivel_id' => $nivel->id, 'activo' => true]);
        $cat = Categoria::create(['nombre' => 'INVESTIGACIÓN CIENTÍFICA', 'nivel' => null]);

        // Vínculo categoría-modalidad SÍ existe
        DB::table('categoria_modalidad')->insert([
            'categoria_id' => $cat->id,
            'modalidad_id' => $mod->id,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        // OJO: NO creamos modalidad_etapa para institucional → debe fallar por etapa
        $instId  = $this->crearInstitucionMinima();
        $feriaId = $this->crearFeriaInstitucional($instId, $etInst->id);

        $payload = [
            'titulo'         => 'Proyecto C',
            'resumen'        => 'R',
            'area_id'        => $area->id,
            'categoria_id'   => $cat->id,
            'modalidad_id'   => $mod->id,
            'institucion_id' => $instId,
            'feria_id'       => $feriaId,
        ];

        $this->postJson('/api/proyectos', $payload)->assertStatus(422);
    }
}
