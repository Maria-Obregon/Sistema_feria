<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JudgeProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * 
     * Este recurso recibe una AsignacionJuez, no un Proyecto directamente
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $proyecto = $this->project;
        
        // Obtener la distribución de aula si existe
        $distribucionAula = \App\Models\DistribucionAula::where('proyecto_id', $proyecto->id)
            ->with('aula')
            ->first();

        return [
            'projectId' => $proyecto->id,
            'projectName' => $proyecto->titulo,
            'resumen' => $proyecto->resumen,
            'codigo' => $proyecto->codigo,
            'estado' => $proyecto->estado,
            'etapaActual' => $proyecto->etapa_actual,
            'detalles' => [
                'categoria' => [
                    'id' => $proyecto->categoria_id,
                    'nombre' => $proyecto->categoria->nombre ?? null,
                ],
                'modalidad' => [
                    'id' => $proyecto->modalidad_id ?? null,
                    'nombre' => $proyecto->modalidad->nombre ?? null,
                ],
                'area' => [
                    'id' => $proyecto->area_id,
                    'nombre' => $proyecto->area->nombre ?? null,
                ],
                'aula' => $distribucionAula ? [
                    'id' => $distribucionAula->aula->id ?? null,
                    'codigo' => $distribucionAula->aula->codigo ?? null,
                ] : null,
            ],
            'etapaId' => $this->etapa_id,
            'etapaNombre' => $this->stage->nombre ?? null,
            'tipoEval' => $this->tipo_eval,
            'asignadoEn' => $this->asignado_en?->format('Y-m-d H:i:s'),
        ];
    }
}
