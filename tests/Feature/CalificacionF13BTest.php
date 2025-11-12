<?php

namespace Tests\Feature;

use App\Models\AsignacionJuez;
use App\Models\Proyecto;
use App\Models\Rubrica;
use App\Models\Usuario;
use Database\Seeders\LabF13BSeeder;
use Database\Seeders\RubricaMiExperienciaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalificacionF13BTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    protected function seedLab(): array
    {
        $this->seed(RubricaMiExperienciaSeeder::class);
        $this->seed(LabF13BSeeder::class);

        $proyecto = Proyecto::where('titulo', 'Proyecto F13B de Laboratorio')->firstOrFail();
        $asignacion = AsignacionJuez::where('proyecto_id', $proyecto->id)->where('tipo_eval', 'exposicion')->firstOrFail();
        $rubrica = Rubrica::where('tipo_eval', 'exposicion')->where('nombre', 'like', '%F13B%')->firstOrFail();

        // Usuario juez vinculado a la asignación
        $usuario = $asignacion->juez->usuario;
        // Autenticación Sanctum
        $this->actingAs($usuario, 'sanctum');

        return [$proyecto, $asignacion, $rubrica];
    }

    public function test_consolidacion_f13b_sin_50_50(): void
    {
        [$proyecto, $asignacion, $rubrica] = $this->seedLab();

        // Resolver rúbrica de exposición con el mismo servicio del backend
        $resolved = app(\App\Services\RubricaResolver::class)
            ->resolveForProyecto($proyecto->id, $asignacion->etapa_id, 'exposicion');
        $this->assertNotNull($resolved, 'Rubrica de exposición no resuelta');

        // Cargar criterios reales de esa rúbrica
        $criterios = \App\Models\Criterio::where('rubrica_id', $resolved->id)->orderBy('id')->get();
        $this->assertSame(40, (int) $criterios->sum('max_puntos'));

        // Distribuir dinámicamente 28 puntos sobre los criterios resueltos (clamp a max_puntos)
        $objetivo = 28;
        $restante = $objetivo;
        foreach ($criterios as $k) {
            if ($restante <= 0) {
                break;
            }
            $val = min((int) $k->max_puntos, $restante);
            $payload = [
                'asignacion_id' => $asignacion->id,
                'criterio_id' => $k->id,
                'puntaje' => $val,
                'comentario' => null,
            ];
            $this->postJson('/api/calificaciones', $payload)->assertCreated();
            $restante -= $val;
        }

        $resp = $this->postJson('/api/calificaciones/consolidar', [
            'proyecto_id' => $proyecto->id,
            'etapa_id' => $asignacion->etapa_id,
        ]);
        $resp->assertOk();
        $data = $resp->json();
        // --- DEBUG: imprimir JSON de respuesta (no detiene el test) ---
        fwrite(STDOUT, "\n[DEBUG consolidar] ".json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        // --- FIN DEBUG ---
        $this->assertEquals(28.0, (float) $data['total']);
        $this->assertEquals(40.0, (float) $data['max_total']);
        $this->assertEqualsWithDelta(70.0, (float) $data['porcentaje'], 0.01);
    }

    public function test_rango_por_criterio_respetado(): void
    {
        [$proyecto, $asignacion, $rubrica] = $this->seedLab();
        $criterio = $rubrica->criterios()->orderByDesc('max_puntos')->firstOrFail();
        $mayor = (int) $criterio->max_puntos + 5;

        $resp = $this->postJson('/api/calificaciones', [
            'asignacion_id' => $asignacion->id,
            'criterio_id' => $criterio->id,
            'puntaje' => $mayor,
            'comentario' => null,
        ]);
        // Implementación actual: rechaza con 422 si puntaje > max_puntos
        $resp->assertStatus(422);
    }

    public function test_finalizacion_bloquea_edicion(): void
    {
        [$proyecto, $asignacion, $rubrica] = $this->seedLab();

        // Evitar middleware de permisos para este test de flujo
        $this->withoutMiddleware();

        // Finalizar
        $respFin = $this->postJson("/api/asignaciones-jueces/{$asignacion->id}/finalizar");
        $respFin->assertOk();

        // Intentar guardar calificación luego de finalizada
        $criterio = $rubrica->criterios()->firstOrFail();
        $resp = $this->postJson('/api/calificaciones', [
            'asignacion_id' => $asignacion->id,
            'criterio_id' => $criterio->id,
            'puntaje' => 1,
            'comentario' => null,
        ]);
        // Implementación actual retorna 409 CONFLICT
        $resp->assertStatus(409);
    }
}
