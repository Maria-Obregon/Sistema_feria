<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MisAsignacionesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $juez = \App\Models\Juez::where('usuario_id', $user->id)->first();

        if (! $juez) {
            return response()->json(['data' => [], 'meta' => ['count' => 0]]);
        }

        $asignaciones = \App\Models\AsignacionJuez::with(['proyecto.categoria'])
            ->where('juez_id', $juez->id)
            ->where('fue_finalizada', false) // Solo mostrar pendientes reales que nunca se han terminado
            ->orderByDesc('finalizada_at')
            ->get();

        $data = $asignaciones->map(function ($asig) {
            return [
                'id' => $asig->id,
                'proyecto_id' => $asig->proyecto_id,
                'etapa_id' => $asig->etapa_id,
                'tipo_eval' => $asig->tipo_eval,
                'finalizada' => ! is_null($asig->finalizada_at),
                'proyecto' => [
                    'id' => $asig->proyecto->id,
                    'titulo' => $asig->proyecto->titulo,
                    'categoria' => $asig->proyecto->categoria ? $asig->proyecto->categoria->nombre : 'Sin Categoría',
                ],
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => ['count' => $data->count()],
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $juez = \App\Models\Juez::where('usuario_id', $user->id)->first();

        if (! $juez) {
            return response()->json([
                'pendientes' => 0,
                'calificadas' => 0,
                'total' => 0,
            ]);
        }

        $total = \App\Models\AsignacionJuez::where('juez_id', $juez->id)->count();
        $calificadas = \App\Models\AsignacionJuez::where('juez_id', $juez->id)
            ->whereNotNull('finalizada_at')
            ->count();

        return response()->json([
            'pendientes' => $total - $calificadas,
            'calificadas' => $calificadas,
            'total' => $total,
        ]);
    }
}
