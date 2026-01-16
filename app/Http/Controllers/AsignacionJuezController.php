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
   public function assign(Request $request, Proyecto $proyecto)
{
    $data = $request->validate([
        'etapa_id'   => ['required', 'integer', 'between:1,9'],
        'tipo_eval'  => ['required', Rule::in(['escrito', 'exposicion', 'integral'])],
        'jueces'     => ['required', 'array', 'min:1'],
        'jueces.*.id'=> ['required', 'integer', 'exists:jueces,id'],
    ]);

    $etapaId = (int) $data['etapa_id'];

    $permitidos = DB::table('rubrica_asignacion')
        ->where('categoria_id', (int) $proyecto->categoria_id)
        ->where('etapa_id', $etapaId)
        ->pluck('tipo_eval')
        ->map(fn($t) => (string) $t)
        ->unique()
        ->values()
        ->all();

    if (empty($permitidos)) {
        return response()->json([
            'message' => 'Este proyecto no tiene rúbricas configuradas para esta etapa.'
        ], 422);
    }

    $tipo = (string) $data['tipo_eval'];

    if ($tipo === 'integral') {
        $tiposAGuardar = array_values(array_intersect(['escrito', 'exposicion'], $permitidos));
        if (empty($tiposAGuardar)) {
            return response()->json([
                'message' => 'Este proyecto no admite evaluación integral en esta etapa.'
            ], 422);
        }
    } else {
        if (!in_array($tipo, $permitidos, true)) {
            return response()->json([
                'message' => "Este proyecto no admite evaluación tipo '{$tipo}' en esta etapa."
            ], 422);
        }
        $tiposAGuardar = [$tipo];
    }

    DB::transaction(function () use ($proyecto, $data, $tiposAGuardar, $etapaId) {

        foreach ($data['jueces'] as $j) {
            $juezId = (int) $j['id'];

            foreach ($tiposAGuardar as $t) {
                AsignacionJuez::updateOrCreate(
                    [
                        'proyecto_id' => (int) $proyecto->id,
                        'juez_id'     => $juezId,
                        'etapa_id'    => $etapaId,
                        'tipo_eval'   => $t,
                    ],
                    [
                        'asignado_en' => now(),
                    ]
                );
            }
        }
    });

    $asigs = $proyecto->asignacionesJuez()
        ->with(['juez:id,nombre,cedula,correo,telefono'])
        ->orderBy('etapa_id')
        ->orderBy('tipo_eval')
        ->get();

    return response()->json([
        'message'      => 'Jueces asignados correctamente',
        'asignaciones' => $asigs,
    ]);
}


    public function listByProyecto(Proyecto $proyecto)
    {
        return $proyecto->asignacionesJuez()
            ->with(['juez:id,nombre,cedula,correo,telefono'])
            ->orderBy('etapa_id')
            ->get();

    }

    public function unassign($id)
    {
        $asig = AsignacionJuez::findOrFail($id);
        $asig->delete();

        return response()->json(['message' => 'Asignación eliminada']);
    }

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
        $asig->fue_finalizada = true;
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
