<?php

namespace App\Http\Controllers;

use App\Http\Resources\CriterionResource;
use App\Models\Rubrica;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de Rúbricas
 * 
 * Gestiona la consulta de rúbricas y sus criterios de evaluación
 * 
 * @package App\Http\Controllers
 */
class RubricaController extends Controller
{
    /**
     * Obtiene los criterios de evaluación por tipo
     * 
     * GET /api/rubricas/{tipo_eval}/criterios
     * 
     * @param string $tipo_eval Tipo de evaluación: 'escrita' u 'oral'
     * @return JsonResponse
     */
    public function criteriosPorTipoEval(string $tipo_eval): JsonResponse
    {
        // Validar que tipo_eval sea válido
        if (!in_array($tipo_eval, ['escrita', 'oral'])) {
            return response()->json([
                'message' => 'El tipo de evaluación debe ser "escrita" u "oral"',
                'error' => 'Invalid tipo_eval'
            ], 422);
        }

        // Buscar la rúbrica con ese tipo_eval
        $rubrica = Rubrica::where('tipo_eval', $tipo_eval)->first();

        // Si no hay rúbrica, devolver array vacío
        if (!$rubrica) {
            return response()->json([
                'data' => []
            ], 200);
        }

        // Obtener criterios ordenados por id
        $criterios = $rubrica->criterios()
            ->orderBy('id')
            ->get();

        // Si no hay criterios, devolver array vacío
        if ($criterios->isEmpty()) {
            return response()->json([
                'data' => []
            ], 200);
        }

        // Devolver criterios con el resource
        return response()->json([
            'data' => CriterionResource::collection($criterios)
        ], 200);
    }
}
