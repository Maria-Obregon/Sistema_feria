<?php

namespace App\Services;

use App\Models\Proyecto;
use Illuminate\Support\Facades\DB;

class RubricaResolver
{
    /**
     * Resuelve la rúbrica asignada para un proyecto/etapa/tipo_eval siguiendo orden de especificidad.
     * Devuelve objeto con: id, modo, max_total; o null si no encuentra.
     */
    public function resolveForProyecto(int $proyectoId, int $etapaId, string $tipoEval): ?object
    {
        /** @var Proyecto|null $proyecto */
        $proyecto = Proyecto::find($proyectoId);
        if (! $proyecto) {
            return null;
        }

        $keys = [
            'modalidad_id' => $proyecto->modalidad_id,
            'categoria_id' => $proyecto->categoria_id,
            'nivel_id' => $proyecto->nivel_id,
        ];

        $candidates = [
            ['modalidad_id', 'categoria_id', 'nivel_id'],
            ['modalidad_id', 'categoria_id'],
            ['modalidad_id', 'nivel_id'],
            ['categoria_id', 'nivel_id'],
            ['modalidad_id'],
            ['categoria_id'],
            ['nivel_id'],
            [],
        ];

        foreach ($candidates as $combo) {
            $q = DB::table('rubrica_asignacion as ra')
                ->join('rubricas as rb', 'rb.id', '=', 'ra.rubrica_id')
                ->where('ra.etapa_id', $etapaId)
                ->where('ra.tipo_eval', $tipoEval);

            foreach (['modalidad_id', 'categoria_id', 'nivel_id'] as $k) {
                if (in_array($k, $combo, true)) {
                    $q->where("ra.$k", $keys[$k]);
                } else {
                    $q->whereNull("ra.$k");
                }
            }

            $row = $q->select(['rb.id', 'rb.modo', 'rb.max_total'])->first();
            if ($row) {
                return (object) [
                    'id' => (int) $row->id,
                    'modo' => $row->modo,
                    'max_total' => $row->max_total !== null ? (float) $row->max_total : null,
                ];
            }
        }

        return null;
    }
}
