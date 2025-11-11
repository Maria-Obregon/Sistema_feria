<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCalificacionesAsignaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-calificaciones-asignaciones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill de calificaciones.asignaciones_juez_id desde asignacion_juez_id, manteniendo compatibilidad.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando backfill de calificaciones.asignaciones_juez_id');

        $updated = 0;
        $omitted = 0;
        $unresolved = 0;
        $examples = [];

        // Procesar en bloques para evitar uso excesivo de memoria
        DB::table('calificaciones')
            ->whereNull('asignaciones_juez_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$updated, &$omitted, &$unresolved, &$examples) {
                foreach ($rows as $row) {
                    $asigNewId = null;

                    // 1) Mapeo directo: si existe un registro en asignaciones_jueces con el mismo id
                    if (! is_null($row->asignacion_juez_id)) {
                        $exists = DB::table('asignaciones_jueces')->where('id', $row->asignacion_juez_id)->exists();
                        if ($exists) {
                            $asigNewId = $row->asignacion_juez_id;
                        }
                    }

                    // 2) Si no existe directo, intentar obtener datos desde vista/tabla legacy para resolver equivalencia
                    if (is_null($asigNewId) && ! is_null($row->asignacion_juez_id)) {
                        $legacy = null;
                        try {
                            // Preferir VIEW de compatibilidad si fue creada por la migración
                            $legacy = DB::table('asignacion_juez')->where('id', $row->asignacion_juez_id)->first();
                        } catch (\Throwable $e) {
                            // Si no hay VIEW, intentar la tabla legacy directamente (si existe)
                            try {
                                $legacy = DB::table('asignacion_juez')->where('id', $row->asignacion_juez_id)->first();
                            } catch (\Throwable $e2) { /* noop */
                            }
                        }

                        if ($legacy) {
                            $candidate = DB::table('asignaciones_jueces')
                                ->where('proyecto_id', $legacy->proyecto_id ?? null)
                                ->where('juez_id', $legacy->juez_id ?? null)
                                ->where('etapa_id', $legacy->etapa_id ?? null)
                                ->value('id');
                            if ($candidate) {
                                $asigNewId = $candidate;
                            }
                        }
                    }

                    if (! is_null($asigNewId)) {
                        DB::table('calificaciones')->where('id', $row->id)->update(['asignaciones_juez_id' => $asigNewId]);
                        $updated++;
                    } else {
                        $unresolved++;
                        if (count($examples) < 10) {
                            $examples[] = [
                                'calificacion_id' => $row->id,
                                'asignacion_juez_id' => $row->asignacion_juez_id,
                            ];
                        }
                    }
                }
            });

        $totalNulls = DB::table('calificaciones')->whereNull('asignaciones_juez_id')->count();
        $omitted = $totalNulls - $unresolved; // aquellos no procesados en chunk por concurrencia, etc.

        $this->line("Actualizadas: {$updated}");
        $this->line("No resueltas: {$unresolved}");
        $this->line("Omitidas: {$omitted}");
        if ($examples) {
            $this->warn('Ejemplos no resueltos (máx 10):');
            foreach ($examples as $ex) {
                $this->line(json_encode($ex));
            }
        }

        $this->info('Backfill finalizado.');

        return 0;
    }
}
