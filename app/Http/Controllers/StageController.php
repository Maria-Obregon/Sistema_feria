<?php

namespace App\Http\Controllers;

use App\Models\ResultadoEtapa;
use App\Services\GradeConsolidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StageController extends Controller
{
    public function __construct(
        private GradeConsolidationService $consolidationService
    ) {}

    public function close(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'proyecto_id' => 'required|integer|exists:proyectos,id',
            'etapa_id' => 'required|integer|exists:etapas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $resultado = $this->consolidationService->closeStage(
            $request->proyecto_id,
            $request->etapa_id,
            true
        );

        return response()->json([
            'message' => 'Etapa cerrada correctamente',
            'data' => [
                'proyecto_id' => $resultado->proyecto_id,
                'etapa_id' => $resultado->etapa_id,
                'cerrada' => $resultado->cerrada,
            ]
        ]);
    }

    public function open(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'proyecto_id' => 'required|integer|exists:proyectos,id',
            'etapa_id' => 'required|integer|exists:etapas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $resultado = $this->consolidationService->closeStage(
            $request->proyecto_id,
            $request->etapa_id,
            false
        );

        return response()->json([
            'message' => 'Etapa abierta correctamente',
            'data' => [
                'proyecto_id' => $resultado->proyecto_id,
                'etapa_id' => $resultado->etapa_id,
                'cerrada' => $resultado->cerrada,
            ]
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'proyecto_id' => 'required|integer|exists:proyectos,id',
            'etapa_id' => 'required|integer|exists:etapas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $resultado = ResultadoEtapa::where('proyecto_id', $request->proyecto_id)
            ->where('etapa_id', $request->etapa_id)
            ->first();

        if (!$resultado) {
            return response()->json([
                'message' => 'No existe resultado consolidado para este proyecto en esta etapa',
                'data' => null
            ], 404);
        }

        return response()->json([
            'data' => [
                'proyecto_id' => $resultado->proyecto_id,
                'etapa_id' => $resultado->etapa_id,
                'nota_escrito' => $resultado->nota_escrito,
                'nota_exposicion' => $resultado->nota_exposicion,
                'nota_final' => $resultado->nota_final,
                'cerrada' => $resultado->cerrada,
                'ganador' => $resultado->ganador,
                'observaciones' => $resultado->observaciones,
            ]
        ]);
    }
}
