<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalcularPuntajes extends Command
{
    protected $signature = 'app:recalcular-puntajes';

    protected $description = 'Recalcula y actualiza el campo puntaje en asignacion_juez basado en las calificaciones existentes';

    public function handle()
    {
        $this->info('Iniciando recálculo de puntajes...');

        $asignaciones = DB::table('asignacion_juez')->get();
        $bar = $this->output->createProgressBar($asignaciones->count());

        foreach ($asignaciones as $asig) {
            // Detectar columna FK
            $fkColumn = 'asignacion_juez_id';
            // Verificar si existe calificaciones para esta asignación usando la columna correcta
            // Asumimos asignacion_juez_id por defecto ya que es lo que vimos en el controlador

            $total = DB::table('calificaciones')
                ->where('asignacion_juez_id', $asig->id)
                ->sum('puntaje');

            // Si da 0, intentamos con asignaciones_juez_id por si acaso (legacy)
            if ($total == 0) {
                $totalAlt = DB::table('calificaciones')
                    ->where('asignaciones_juez_id', $asig->id)
                    ->sum('puntaje');
                if ($totalAlt > 0) {
                    $total = $totalAlt;
                }
            }

            DB::table('asignacion_juez')
                ->where('id', $asig->id)
                ->update(['puntaje' => $total]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Puntajes recalculados exitosamente.');
    }
}
