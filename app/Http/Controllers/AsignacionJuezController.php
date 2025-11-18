<?php

namespace App\Http\Controllers;

use App\Models\AsignacionJuez;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AsignacionJuezController extends Controller
{
    /**
     * POST /api/proyectos/{proyecto}/asignar-jueces
     */
    public function assign(Request $request, Proyecto $proyecto)
    {
        $data = $request->validate([
            'etapa_id' => ['required', 'integer', 'between:1,9'], // ajusta rango a tu catálogo
            'tipo_eval' => ['required', Rule::in(['escrito', 'exposicion', 'integral'])],
            'jueces' => ['required', 'array', 'min:1'],
            'jueces.*.id' => ['required', 'integer', 'exists:jueces,id'],
        ]);

        DB::transaction(function () use ($proyecto, $data) {
            foreach ($data['jueces'] as $j) {
                AsignacionJuez::updateOrCreate(
                    [
                        'proyecto_id' => $proyecto->id,
                        'juez_id' => (int) $j['id'],
                        'etapa_id' => (int) $data['etapa_id'],
                    ],
                    [
                        'tipo_eval' => $data['tipo_eval'],
                    ]
                );
            }
        });

        $asigs = $proyecto->asignacionesJuez()
            ->with(['juez:id,nombre,cedula,correo,telefono'])
            ->orderBy('etapa_id')
            ->get();

        return response()->json([
            'message' => 'Jueces asignados correctamente',
            'asignaciones' => $asigs,
        ]);
    }

    /**
     * GET /api/proyectos/{proyecto}/asignaciones
     */
    public function listByProyecto(Proyecto $proyecto)
    {
        return $proyecto->asignacionesJuez()
            ->with(['juez:id,nombre,cedula,correo,telefono'])
            ->orderBy('etapa_id')
            ->get();

    }

    /**
     * DELETE /api/asignaciones-jueces/{id}
     */
    public function unassign($id)
    {
        $asig = AsignacionJuez::findOrFail($id);
        $asig->delete();

        return response()->json(['message' => 'Asignación eliminada']);
    }

    /**
     * POST /api/asignaciones-jueces/{id}/finalizar
     */
    public function finalizar(Request $request, $id)
    {
        $user = Auth::user();
        $asig = AsignacionJuez::findOrFail($id);

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('juez')) {
            $juez = \App\Models\Juez::where('usuario_id', $user->id)->first();
            if (! $juez) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            if ((int) $asig->juez_id !== (int) $juez->id) {
                return response()->json(['message' => 'Asignación no pertenece al juez autenticado'], 403);
            }
        }

        if (! is_null($asig->finalizada_at)) {
            return response()->json([
                'message' => 'Asignación ya finalizada',
                'finalizada_at' => $asig->finalizada_at,
            ], 200);
        }

        $asig->finalizada_at = now();
        $asig->save();

        Log::info('asignaciones.finalizar', [
            'user_id' => $user?->id,
            'asignacion_id' => $asig->id,
            'ts' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Asignación finalizada',
            'finalizada_at' => $asig->finalizada_at,
        ]);
    }

    /**
     * POST /api/asignaciones-jueces/{id}/reabrir
     */
    public function reabrir(Request $request, $id)
    {
        $user = Auth::user();
        $asig = AsignacionJuez::findOrFail($id);

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('juez')) {
            $juez = \App\Models\Juez::where('usuario_id', $user->id)->first();
            if (! $juez) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            if ((int) $asig->juez_id !== (int) $juez->id) {
                return response()->json(['message' => 'Asignación no pertenece al juez autenticado'], 403);
            }
        }

        if (is_null($asig->finalizada_at)) {
            return response()->json([
                'message' => 'Asignación ya está abierta',
            ], 200);
        }

        $asig->finalizada_at = null;
        $asig->save();

        Log::info('asignaciones.reabrir', [
            'user_id' => $user?->id,
            'asignacion_id' => $asig->id,
            'ts' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'message' => 'Asignación reabierta para edición',
        ]);
    }
}
