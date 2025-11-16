<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para Criterios de Evaluación
 * 
 * Transforma los datos del modelo Criterio para la API
 */
class CriterionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'peso' => (float) $this->peso,
            'max_puntos' => $this->max_puntos,
            'rubrica_id' => $this->rubrica_id,
        ];
    }
}
