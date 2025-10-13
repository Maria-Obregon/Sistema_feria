<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para Calificaciones
 * 
 * Transforma los datos del modelo Calificacion para la API
 */
class CalificacionResource extends JsonResource
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
            'asignacion_juez_id' => $this->asignacion_juez_id,
            'criterio_id' => $this->criterio_id,
            'puntaje' => (int) $this->puntaje,
            'comentario' => $this->comentario,
            'creada_en' => $this->creada_en?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
