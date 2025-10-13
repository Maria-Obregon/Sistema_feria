<?php

namespace App\Services;

use App\Models\AsignacionJuez;
use App\Models\Calificacion;
use App\Models\Etapa;
use App\Models\ResultadoEtapa;
use App\Models\Rubrica;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Servicio de Consolidación de Calificaciones
 */
class GradeConsolidationService
{
    /**
     * Consolida las calificaciones de un proyecto en una etapa
     * 
     * @param int $proyectoId
     * @param int $etapaId
     * @return array
     * @throws ValidationException
     */
    public function consolidateProjectStage(int $proyectoId, int $etapaId): array
    {
        $asignaciones = AsignacionJuez::where('proyecto_id', $proyectoId)
            ->where('etapa_id', $etapaId)
            ->with(['grades.criterion'])
            ->get();

        if ($asignaciones->isEmpty()) {
            throw ValidationException::withMessages([
                'asignaciones' => 'No hay asignaciones para este proyecto en esta etapa'
            ]);
        }

        $agrupado = $asignaciones->groupBy('tipo_eval');
        
        $juecesPorTipo = [];
        $notasPorTipo = [];

        foreach ($agrupado as $tipoEval => $asignacionesDelTipo) {
            $juecesPorTipo[$tipoEval] = $asignacionesDelTipo->count();
            $notasParciales = [];

            foreach ($asignacionesDelTipo as $asignacion) {
                $notaParcial = $this->calculatePartialGrade($asignacion);
                
                if ($notaParcial !== null) {
                    $notasParciales[] = $notaParcial;
                }
            }

            if (count($notasParciales) > 0) {
                $notasPorTipo[$tipoEval] = array_sum($notasParciales) / count($notasParciales);
            }
        }

        $ponderaciones = $this->getPonderaciones();

        $notaFinal = $this->calculateFinalGrade($notasPorTipo, $ponderaciones);

        ResultadoEtapa::updateOrCreate(
            [
                'proyecto_id' => $proyectoId,
                'etapa_id' => $etapaId,
            ],
            [
                'nota_escrito' => isset($notasPorTipo['escrita']) ? round($notasPorTipo['escrita'] * 100, 2) : null,
                'nota_exposicion' => isset($notasPorTipo['oral']) ? round($notasPorTipo['oral'] * 100, 2) : null,
                'nota_final' => round($notaFinal * 100, 2),
            ]
        );

        return [
            'proyectoId' => $proyectoId,
            'etapaId' => $etapaId,
            'juecesPorTipo' => $juecesPorTipo,
            'notaEscrita' => isset($notasPorTipo['escrita']) ? round($notasPorTipo['escrita'], 2) : null,
            'notaOral' => isset($notasPorTipo['oral']) ? round($notasPorTipo['oral'], 2) : null,
            'ponderaciones' => $ponderaciones,
            'notaFinal' => round($notaFinal, 2),
        ];
    }

    /**
     * @param AsignacionJuez $asignacion
     * @return float|null
     */
    private function calculatePartialGrade(AsignacionJuez $asignacion): ?float
    {
        $calificaciones = $asignacion->grades;

        if ($calificaciones->isEmpty()) {
            return null;
        }

        $notaParcial = 0;

        foreach ($calificaciones as $calificacion) {
            $criterio = $calificacion->criterion;
            
            if ($criterio && $criterio->max_puntos > 0) {
                $notaParcial += ($calificacion->puntaje / $criterio->max_puntos) * $criterio->peso;
            }
        }

        return $notaParcial;
    }

    /**
     * @return array
     */
    private function getPonderaciones(): array
    {
        $rubricas = Rubrica::whereIn('tipo_eval', ['escrita', 'oral'])->get();

        if ($rubricas->isEmpty()) {
            return config('grades.default_ponderaciones', [
                'escrita' => 0.60,
                'oral' => 0.40,
            ]);
        }

        $ponderaciones = [];
        $total = 0;

        foreach ($rubricas as $rubrica) {
            $pond = $rubrica->ponderacion ?? 1.0;
            $ponderaciones[$rubrica->tipo_eval] = (float) $pond;
            $total += $pond;
        }

        if ($total > 0 && $total != 1.0) {
            foreach ($ponderaciones as $tipo => $valor) {
                $ponderaciones[$tipo] = $valor / $total;
            }
        }

        $ponderaciones['escrita'] = $ponderaciones['escrita'] ?? 0.60;
        $ponderaciones['oral'] = $ponderaciones['oral'] ?? 0.40;

        return $ponderaciones;
    }

    /**
     * @param array $notasPorTipo
     * @param array $ponderaciones
     * @return float
     */
    private function calculateFinalGrade(array $notasPorTipo, array $ponderaciones): float
    {
        $notaFinal = 0;

        if (count($notasPorTipo) === 1) {
            return array_values($notasPorTipo)[0];
        }

        if (isset($notasPorTipo['escrita'])) {
            $notaFinal += $notasPorTipo['escrita'] * $ponderaciones['escrita'];
        }

        if (isset($notasPorTipo['oral'])) {
            $notaFinal += $notasPorTipo['oral'] * $ponderaciones['oral'];
        }

        return $notaFinal;
    }

    /**
     * Valida el número mínimo de jueces por etapa
     * 
     * @param int $proyectoId
     * @param int $etapaId
     * @throws ValidationException
     */
    public function checkCardinality(int $proyectoId, int $etapaId): void
    {
        $etapa = Etapa::find($etapaId);

        if (!$etapa) {
            throw ValidationException::withMessages([
                'etapa_id' => 'Etapa no encontrada'
            ]);
        }

        $nombreEtapa = strtolower($etapa->nombre);
        
        $minJueces = config("grades.min_jueces.{$nombreEtapa}");

        if (!$minJueces) {
            return;
        }

        $numJueces = AsignacionJuez::where('proyecto_id', $proyectoId)
            ->where('etapa_id', $etapaId)
            ->distinct('juez_id')
            ->count('juez_id');

        if ($numJueces < $minJueces) {
            throw ValidationException::withMessages([
                'cardinalidad' => "Se requieren al menos {$minJueces} jueces en etapa " . ucfirst($etapa->nombre)
            ]);
        }
    }

    /**
     * @param int $proyectoId
     * @param int $etapaId
     * @return bool
     */
    public function isStageClosed(int $proyectoId, int $etapaId): bool
    {
        return false;
    }
}
