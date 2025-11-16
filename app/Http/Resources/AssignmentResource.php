<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rubrica = $this->getRubrica();
        $estado = $this->determineEstado($rubrica);
        $lastGraded = $this->getLastGradedAt();
        $criteriaTotal = $rubrica ? $rubrica->criterios()->count() : 0;

        return [
            'assignmentId' => $this->id,
            'projectId' => $this->proyecto_id,
            'projectName' => $this->project->titulo ?? null,
            'etapaId' => $this->etapa_id,
            'etapaNombre' => $this->stage->nombre ?? null,
            'tipoEval' => $this->tipo_eval,
            'estado' => $estado,
            'gradesCount' => $this->grades()->count(),
            'lastGradedAt' => $lastGraded?->toIso8601String(),
            'criteriaTotal' => $criteriaTotal,
            'asignadoEn' => $this->asignado_en?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Obtiene la rúbrica correspondiente al tipo de evaluación
     */
    private function getRubrica(): ?\App\Models\Rubrica
    {
        if (!$this->tipo_eval) {
            return null;
        }

        return \App\Models\Rubrica::where('tipo_eval', $this->tipo_eval)->first();
    }

    /**
     * Obtiene la fecha de la última calificación
     */
    private function getLastGradedAt(): ?\Carbon\Carbon
    {
        $lastGrade = $this->grades()
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$lastGrade) {
            return null;
        }

        return $lastGrade->updated_at > $lastGrade->created_at 
            ? $lastGrade->updated_at 
            : $lastGrade->created_at;
    }

    /**
     * Determina si la asignación está pending o completed
     */
    private function determineEstado(?\App\Models\Rubrica $rubrica = null): string
    {
        if (!$this->tipo_eval) {
            return 'pending';
        }

        if (!$rubrica) {
            $rubrica = $this->getRubrica();
        }
        
        if (!$rubrica) {
            return 'pending';
        }

        // Contar criterios de la rúbrica
        $totalCriterios = $rubrica->criterios()->count();
        
        if ($totalCriterios === 0) {
            return 'pending';
        }

        // Contar calificaciones completadas
        $calificacionesCompletadas = $this->grades()
            ->whereHas('criterion', function ($query) use ($rubrica) {
                $query->where('rubrica_id', $rubrica->id);
            })
            ->count();

        return ($calificacionesCompletadas >= $totalCriterios) ? 'completed' : 'pending';
    }
}
