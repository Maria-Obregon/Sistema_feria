<?php

namespace Tests\Feature\Judge;

use App\Models\Area;
use App\Models\AsignacionJuez;
use App\Models\Categoria;
use App\Models\Etapa;
use App\Models\Institucion;
use App\Models\Juez;
use App\Models\Modalidad;
use App\Models\Proyecto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignmentsApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioJuezConPermisos(array $permisos = []): array
    {
        $this->seed(\Database\Seeders\CoreCatalogSeeder::class);

        $usuario = Usuario::create([
            'nombre' => 'Juez Test',
            'email' => 'juez.test@feria.test',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $area = Area::first();
        $juez = Juez::create([
            'nombre' => 'Juez Test',
            'cedula' => '1-0000-0001',
            'sexo' => 'M',
            'telefono' => '8888-8888',
            'correo' => 'juez.test@feria.test',
            'grado_academico' => 'Licenciatura',
            'area__id' => $area->id,
            'usuario_id' => $usuario->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'juez', 'guard_name' => 'sanctum']);
        $usuario->assignRole($role);

        foreach ($permisos as $permiso) {
            $permission = Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'sanctum']);
            $usuario->givePermissionTo($permission);
        }

        return ['usuario' => $usuario, 'juez' => $juez];
    }

    private function crearProyectoConAsignacion(Juez $juez, int $etapaId, string $tipoEval): array
    {
        $area = Area::first();
        $categoria = Categoria::first();
        $institucion = Institucion::first();
        $modalidad = Modalidad::firstOrCreate(['nombre' => 'Individual']);

        $proyecto = Proyecto::create([
            'titulo' => 'Proyecto Test',
            'resumen' => 'Resumen de prueba',
            'area_id' => $area->id,
            'categoria_id' => $categoria->id,
            'institucion_id' => $institucion->id,
            'modalidad_id' => $modalidad->id,
            'etapa_actual' => 'institucional',
            'estado' => 'en_evaluacion',
            'codigo' => 'TEST-' . time(),
        ]);

        $asignacion = AsignacionJuez::create([
            'proyecto_id' => $proyecto->id,
            'juez_id' => $juez->id,
            'etapa_id' => $etapaId,
            'tipo_eval' => $tipoEval,
        ]);

        return ['proyecto' => $proyecto, 'asignacion' => $asignacion];
    }

    public function test_listado_de_asignaciones_del_juez_autenticado(): void
    {
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);

        $data = $this->crearUsuarioJuezConPermisos(['proyectos.ver']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearProyectoConAsignacion($juez, 1, 'escrita');

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    public function test_filtros_por_etapa_tipo_eval_y_estado(): void
    {
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);

        $data = $this->crearUsuarioJuezConPermisos(['proyectos.ver']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $this->crearProyectoConAsignacion($juez, 1, 'escrita');

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/juez/asignaciones?etapa_id=1&tipo_eval=escrita');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_proyecto_show_403_si_no_pertenece_al_juez(): void
    {
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);

        $data = $this->crearUsuarioJuezConPermisos(['proyectos.ver']);
        $usuario = $data['usuario'];

        // Crear otro juez y su proyecto
        $otroJuez = Juez::create([
            'nombre' => 'Otro Juez',
            'cedula' => '2-0000-0002',
            'sexo' => 'F',
            'telefono' => '8888-9999',
            'correo' => 'otro@test.com',
            'grado_academico' => 'Maestría',
            'area__id' => Area::first()->id,
            'usuario_id' => null,
        ]);

        $proyectoData = $this->crearProyectoConAsignacion($otroJuez, 1, 'escrita');
        $proyecto = $proyectoData['proyecto'];

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson("/api/juez/proyectos/{$proyecto->id}");

        $response->assertStatus(403);
    }

    public function test_proyecto_show_200_si_pertenece(): void
    {
        Etapa::create(['id' => 1, 'nombre' => 'institucional']);

        $data = $this->crearUsuarioJuezConPermisos(['proyectos.ver']);
        $usuario = $data['usuario'];
        $juez = $data['juez'];

        $proyectoData = $this->crearProyectoConAsignacion($juez, 1, 'escrita');
        $proyecto = $proyectoData['proyecto'];

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson("/api/juez/proyectos/{$proyecto->id}");

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json());
    }
}
