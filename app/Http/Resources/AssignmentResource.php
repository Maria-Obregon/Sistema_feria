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
        // Determinar el estado de la asignación
        $estado = $this->determineEstado();

        return [
            'assignmentId' => $this->id,
            'projectId' => $this->proyecto_id,
            'projectName' => $this->project->titulo ?? null,
            'etapaId' => $this->etapa_id,
            'etapaNombre' => $this->stage->nombre ?? null,
            'tipoEval' => $this->tipo_eval,
            'estado' => $estado,
            'asignadoEn' => $this->asignado_en?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Determina si la asignación está pending o completed
     */
    private function determineEstado(): string
    {
        if (!$this->tipo_eval) {
            return 'pending';
        }

        // Obtener la rúbrica correspondiente
        $rubrica = \App\Models\Rubrica::where('tipo_eval', $this->tipo_eval)->first();
        
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
