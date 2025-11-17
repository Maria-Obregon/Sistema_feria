<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

use App\Models\Etapa;
use App\Models\Area;
use App\Models\Modalidad;
use App\Models\Categoria;
use App\Models\Institucion;
use App\Models\Proyecto;
use App\Models\Juez;
use App\Models\Usuario;

class DemoJuezAsignacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1) ETAPA "institucional" (idempotente)
            $etapa = Etapa::firstOrCreate(['nombre' => 'institucional']);
            $etapaId = (int) $etapa->id;

            // 2) MODALIDAD habilitada para esa etapa (pivot modalidad_etapa) o cualquiera
            $modalidad = Modalidad::whereHas('etapas', fn($q) => $q->where('etapas.id', $etapaId))->first()
                      ?? Modalidad::first();

            if (! $modalidad) {
                $this->command?->error('No hay modalidades. Corre NivelesYModalidadesSeeder.');
                DB::rollBack(); return;
            }

            // 3) CATEGORÍA vinculada a esa modalidad (pivot categoria_modalidad) o cualquiera
            $categoria = Categoria::whereHas('modalidades', fn($q) => $q->where('modalidades.id', $modalidad->id))->first()
                     ?? Categoria::first();

            if (! $categoria) {
                $this->command?->error('No hay categorías o ninguna vinculada a la modalidad. Corre CategoriasSeeder.');
                DB::rollBack(); return;
            }

            // 4) ÁREA existente (cualquiera)
            $area = Area::first();
            if (! $area) {
                $this->command?->error('No hay áreas. Corre CoreCatalogSeeder.');
                DB::rollBack(); return;
            }

            // 5) INSTITUCIÓN existente (o crea mínima si no hay)
            $inst = Institucion::first();
            if (! $inst) {
                $inst = Institucion::create([
                    'nombre'                => 'Institución Demo',
                    'codigo_presupuestario' => 'ID-'.Str::upper(Str::random(5)),
                    'tipo'                  => 'publica',
                    'modalidad'             => 'Académica',
                    'regional_id'           => 1,
                    'circuito_id'           => 1,
                    'activo'                => true,
                ]);
            }

            // 6) JUEZ: prioriza el demo; si no existe, toma el primero que haya
            $juez = Juez::whereHas('usuario', fn($q) => $q->where('email','juez.demo@prueba.local'))->first()
                 ?? Juez::first();

            if (! $juez) {
                $this->command?->error('No hay jueces. Corre DemoJuezLoginSeeder o crea uno.');
                DB::rollBack(); return;
            }

            // 7) (Opcional) FERIA si tu esquema la usa
            $feriaId = null;
            if (class_exists(Feria::class)) {
                $feria = Feria::firstOrCreate(
                    ['anio' => (int) date('Y'), 'institucion_id' => $inst->id],
                    [
                        'fecha'              => now()->toDateString(),
                        'hora_inicio'        => '08:00:00',
                        'sede'               => 'Gimnasio',
                        'proyectos_por_aula' => 0,
                        'tipo_feria'         => 'institucional',
                        'lugar_realizacion'  => 'Sede central',
                        'etapa_id'           => $etapaId,
                    ]
                );
                $feriaId = (int) $feria->id;
            }

            // 8) PROYECTO con columnas obligatorias (según tu migración)
            $codigo = 'PRJ-'.Str::upper(Str::random(6));

            $payload = [
                'titulo'         => 'Proyecto Demo Jueces',
                'resumen'        => 'Proyecto de prueba para flujo de jueces (listado admin y evaluación).',
                'area_id'        => $area->id,
                'categoria_id'   => $categoria->id,
                'institucion_id' => $inst->id,
                'estado'         => 'en_evaluacion', // enum válido
                'modalidad_id'   => $modalidad->id,
                'codigo'         => $codigo,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            if (Schema::hasColumn('proyectos','feria_id') && $feriaId) {
                $payload['feria_id'] = $feriaId;
            }
            if (Schema::hasColumn('proyectos','etapa_id')) {
                $payload['etapa_id'] = $etapaId;
            }

            DB::table('proyectos')->insert($payload);

            /** @var Proyecto $proyecto */
            $proyecto = Proyecto::where('codigo', $codigo)->first();
            if (! $proyecto) {
                $this->command?->error('No se pudo recuperar el proyecto creado.');
                DB::rollBack(); return;
            }

            // 9) ASIGNACIÓN del juez (tu tabla es singular: asignacion_juez)
            DB::table('asignacion_juez')->updateOrInsert(
                [
                    'proyecto_id' => $proyecto->id,
                    'juez_id'     => $juez->id,
                    'etapa_id'    => $etapaId,
                ],
                [
                    'tipo_eval'   => 'exposicion', // cambia a 'escrito' si querés probar
                    'asignado_en' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );

            // 10) Asegurar visibilidad
            DB::table('proyectos')->where('id', $proyecto->id)->update([
                'estado'     => 'en_evaluacion',
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->command?->info(
                "Proyecto demo listo: proyecto_id={$proyecto->id} · modalidad={$modalidad->nombre} · categoría={$categoria->nombre} · etapa=institucional"
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command?->error('Error en DemoJuezAsignacionSeeder: '.$e->getMessage());
            throw $e;
        }
    }
}
